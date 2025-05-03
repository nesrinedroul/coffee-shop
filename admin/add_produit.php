<?php
include('../includes/db.php');
session_start();
// Vérifier si l'utilisateur est un admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category = trim($_POST['category']);
    $imageUrl = trim($_POST['image_url'] ?? ''); // Nouveau champ pour l'URL

    $imagePath = null;

    // Vérifier si un fichier image a été uploadé
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image_upload']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            die("Type de fichier non autorisé. Veuillez télécharger une image JPG, PNG, GIF ou WebP.");
        }

        // Créer le dossier uploads s'il n'existe pas
        if (!file_exists('../uploads')) {
            mkdir('../uploads', 0777, true);
        }

        // Déplacer l'image dans le dossier uploads/
        $imageName = uniqid() . '_' . basename($_FILES['image_upload']['name']);
        $imagePath = 'uploads/' . $imageName;
        if (!move_uploaded_file($_FILES['image_upload']['tmp_name'], '../' . $imagePath)) {
            die("Erreur lors du téléchargement de l'image.");
        }
    } 
    // Sinon, vérifier si une URL a été fournie
    elseif (!empty($imageUrl)) {
        // Valider que c'est une URL d'image valide
        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            // Vérifier l'extension de l'image
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $urlExtension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            
            if (in_array($urlExtension, $allowedExtensions)) {
                $imagePath = $imageUrl;
            } else {
                die("L'URL doit pointer vers une image (JPG, PNG, GIF ou WebP)");
            }
        } else {
            die("URL d'image invalide");
        }
    } else {
        die("Veuillez fournir soit une image à uploader, soit une URL d'image valide.");
    }

    // Insertion du produit en base de données
    try {
        $sql = "INSERT INTO produit (nom, description, prix, stock, image, categorie, date_ajout) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $price, $stock, $imagePath, $category]);
        
        header("Location: admin_products.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'ajout du produit : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
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
        
        /* Main content area */
        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 250px; /* Adjust based on your sidebar width */
        }
        
        /* Form styling */
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
        
        /* Image upload styles */
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
        
        .image-uploader small {
            color: var(--light-text);
            font-size: 0.8rem;
        }
        
        #image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 1rem;
            display: none;
            border-radius: 4px;
        }
        
        /* Tabs styling */
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
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Button styling */
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
        
        /* Responsive adjustments */
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
    </style>
</head>
<body>
    <?php include('admin_header.php'); ?>
    
    <div class="admin-container">
        <div class="main-content">
            <div class="form-card">
                <div class="form-header">
                    <h2>Ajouter un Nouveau Produit</h2>
                    <p>Remplissez les détails du produit ci-dessous</p>
                </div>
                
                <form action="add_produit.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nom du produit</label>
                            <input type="text" name="name" id="name" placeholder="Ex: Café Colombien" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Prix (€)</label>
                            <input type="number" name="price" id="price" step="0.01" min="0" placeholder="Ex: 9.99" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Quantité en stock</label>
                            <input type="number" name="stock" id="stock" min="0" placeholder="Ex: 50" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Catégorie</label>
                            <input type="text" name="category" id="category" placeholder="Ex: Café en grains" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" placeholder="Décrivez le produit en détail..." required></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Image du produit</label>
                            <div class="tabs">
                                <div class="tab active" data-tab="upload">
                                    <i class='bx bx-upload'></i> Uploader une image
                                </div>
                                <div class="tab" data-tab="url">
                                    <i class='bx bx-link'></i> Utiliser une URL
                                </div>
                            </div>
                            
                            <div class="tab-content active" id="upload-tab">
                                <label class="image-uploader" for="image_upload">
                                    <i class='bx bx-image-add'></i>
                                    <p>Cliquez pour sélectionner une image</p>
                                    <small>Formats acceptés: JPG, PNG, GIF, WebP (max 2MB)</small>
                                    <input type="file" name="image_upload" id="image_upload" accept="image/*" style="display: none;">
                                    <img id="image-preview" src="#" alt="Aperçu de l'image">
                                </label>
                            </div>
                            
                            <div class="tab-content" id="url-tab">
                                <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg">
                                <small class="hint">L'URL doit pointer directement vers une image valide</small>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class='bx bx-plus'></i> Ajouter le Produit
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });

        // Image preview for file upload
        const imageUpload = document.getElementById('image_upload');
        const imagePreview = document.getElementById('image-preview');
        
        imageUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.style.display = 'block';
                    imagePreview.src = this.result;
                });
                
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop for image upload
        const uploader = document.querySelector('.image-uploader');
        
        uploader.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploader.style.borderColor = 'var(--primary)';
            uploader.style.backgroundColor = 'rgba(111, 78, 55, 0.05)';
        });
        
        uploader.addEventListener('dragleave', () => {
            uploader.style.borderColor = 'var(--border)';
            uploader.style.backgroundColor = 'transparent';
        });
        
        uploader.addEventListener('drop', (e) => {
            e.preventDefault();
            uploader.style.borderColor = 'var(--border)';
            uploader.style.backgroundColor = 'transparent';
            
            if (e.dataTransfer.files.length) {
                imageUpload.files = e.dataTransfer.files;
                const event = new Event('change');
                imageUpload.dispatchEvent(event);
            }
        });
    </script>
</body>
</html>