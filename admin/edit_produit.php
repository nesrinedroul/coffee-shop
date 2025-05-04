<?php
include('../includes/db.php');
session_start();

// Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check product ID
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    die("ID produit manquant");
}

// Fetch product data
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produit non trouvé");
}

// Determine current image type
$currentImage = $product['image'];
$isCurrentImageUrl = filter_var($currentImage, FILTER_VALIDATE_URL);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = trim($_POST['category']);
    $imageUrl = trim($_POST['image_url'] ?? '');

    // Initialize with current image
    $newImagePath = $currentImage;

    // Handle image upload
    if (!empty($_FILES['image_upload']['name'])) {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image_upload']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            die("Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP");
        }

        // Delete old file if it's a local upload
        if (!$isCurrentImageUrl && file_exists("../".$currentImage)) {
            unlink("../".$currentImage);
        }

        // Upload new file
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image_upload']['name']);
        $newImagePath = 'uploads/' . $fileName;
        
        if (!move_uploaded_file($_FILES['image_upload']['tmp_name'], "../".$newImagePath)) {
            die("Erreur lors du téléchargement de l'image");
        }
    } 
    // Handle image URL
    elseif (!empty($imageUrl)) {
        // Validate URL format
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            die("URL d'image invalide");
        }

        // Validate image extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $urlExtension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        if (!in_array($urlExtension, $allowedExtensions)) {
            die("L'URL doit pointer vers une image valide (JPG, PNG, GIF, WebP)");
        }

        // Delete old local file if switching from upload to URL
        if (!$isCurrentImageUrl && file_exists("../".$currentImage)) {
            unlink("../".$currentImage);
        }

        $newImagePath = $imageUrl;
    }

    // Update product in database
    try {
        $sql = "UPDATE produit SET 
                nom = ?, 
                description = ?, 
                prix = ?, 
                stock = ?, 
                image = ?, 
                categorie = ? 
                WHERE id_produit = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $price, $stock, $newImagePath, $category, $product_id]);
        
        header("Location: admin_produit.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Erreur de mise à jour : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary: #6f4e37;
            --primary-light: #a67c52;
            --secondary: #f8f5f2;
            --text: #333333;
            --light-text: #777777;
            --border: #e0e0e0;
            --error: #e74c3c;
            --success: #2ecc71;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f9f9f9;
            color: var(--text);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 250px;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-header h2 {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--light-text);
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
            font-size: 0.95rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(111, 78, 55, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .image-uploader {
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .image-uploader:hover {
            border-color: var(--primary-light);
            background: rgba(111, 78, 55, 0.02);
        }

        .image-uploader i {
            font-size: 2rem;
            color: var(--primary-light);
            margin-bottom: 0.5rem;
        }

        .image-uploader p {
            color: var(--light-text);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        #image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 1rem;
            border-radius: 4px;
        }

        .tabs {
            display: flex;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .tab {
            padding: 0.7rem 1.5rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: var(--light-text);
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            font-weight: 500;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        .submit-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(111, 78, 55, 0.2);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }
.current-image-preview {
    margin-bottom: 1.5rem;
    padding: 10px;
  
    background: #fff;
}

.current-image-preview h4 {
    color: var(--primary);
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.current-image-thumbnail {
    max-width: 250px;
    height: auto;
    border-radius: 6px;
    border: 2px solid var(--border);
    padding: 3px;
}
    </style>
</head>
<body>
    <?php include('admin_header.php'); ?>
    
    <div class="main-content main admin-container">
        <div class="form-card">
            <div class="form-header">
                <h2>Modifier le Produit</h2>
                <p>Mettez à jour les détails du produit</p>
            </div>

            <form action="edit_produit.php?id=<?= htmlspecialchars($product_id) ?>" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="form-left">
                        <div class="form-group">
                            <label for="name">Nom du produit</label>
                            <input type="text" name="name" id="name" 
                                   value="<?= htmlspecialchars($product['nom']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" required><?= 
                                htmlspecialchars($product['description']) ?></textarea>
                        </div>
                    
                   
                        <div class="form-group current-image-preview">
                            <h4>Image Actuelle:</h4>
                            <?php if (!empty($currentImage)): ?>
                                <?php if ($isCurrentImageUrl): ?>
                                    <img src="<?= htmlspecialchars($currentImage) ?>" 
                                        class="current-image-thumbnail" 
                                        alt="Image actuelle du produit">
                                <?php else: ?>
                                    <img src="../<?= htmlspecialchars($currentImage) ?>" 
                                        class="current-image-thumbnail" 
                                        alt="Image actuelle du produit">
                                <?php endif; ?>
                            <?php else: ?>
                                <p>Aucune image disponible</p>
                            <?php endif; ?></div>     
                   </div>
                    <!-- Right Column -->
                    <div class="form-right">
                        <div class="form-group">
                            <label for="price">Prix (DZD)</label>
                            <input type="number" name="price" id="price" step="0.01" 
                                   value="<?= htmlspecialchars($product['prix']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" name="stock" id="stock" 
                                   value="<?= htmlspecialchars($product['stock']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Catégorie</label>
                            <input type="text" name="category" id="category" 
                                   value="<?= htmlspecialchars($product['categorie']) ?>" required>
                        </div>
                             <!-- Image Section -->
                     <!-- Image Section -->
                        <div class="form-group full-width">
                            <label>Image du produit</label>
                            <div class="tabs">
                                <div class="tab <?= !$isCurrentImageUrl ? 'active' : '' ?>" data-tab="upload">
                                    <i class='bx bx-upload'></i> Uploader une image
                                </div>
                                <div class="tab <?= $isCurrentImageUrl ? 'active' : '' ?>" data-tab="url">
                                    <i class='bx bx-link'></i> Utiliser une URL
                                </div>
                            </div>

                            <!-- Upload Tab -->
                            <div class="tab-content <?= !$isCurrentImageUrl ? 'active' : '' ?>" id="upload-tab">
                                <label class="image-uploader" for="image_upload">
                                    <i class='bx bx-image-add'></i>
                                    <p>Cliquez pour changer l'image</p>
                                    <small>Formats acceptés: JPG, PNG, GIF, WebP (max 2MB)</small>
                                    <input type="file" name="image_upload" id="image_upload" 
                                           accept="image/*" style="display: none;">
                                    <?php if (!$isCurrentImageUrl && !empty($currentImage)): ?>
                                        <img id="image-preview" src="../<?= htmlspecialchars($currentImage) ?>" 
                                             alt="Image actuelle">
                                    <?php endif; ?>
                                </label>
                            </div>

                            <!-- URL Tab -->
                            <div class="tab-content <?= $isCurrentImageUrl ? 'active' : '' ?>" id="url-tab">
                                <input type="url" name="image_url" id="image_url" 
                                       placeholder="https://example.com/image.jpg" 
                                       value="<?= $isCurrentImageUrl ? htmlspecialchars($currentImage) : '' ?>">
                                <small class="hint">Laissez vide pour garder l'image actuelle</small>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class='bx bx-save'></i> Mettre à Jour
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/edit.js">
    </script>
</body>
</html>