<?php
session_start();
include('includes/db.php'); // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm = $_POST['confirm_mot_de_passe'];

    // Vérification si l'email existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Cette adresse e-mail est déjà utilisée.";
        header("Location: register.php");
        exit();
    }

    // Validation des champs côté serveur
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirm)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\- ]{2,30}$/", $nom) || !preg_match("/^[a-zA-ZÀ-ÿ\- ]{2,30}$/", $prenom)) {
        $_SESSION['error'] = "Le nom et le prénom doivent contenir entre 2 et 30 lettres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Adresse e-mail invalide.";
    } elseif (strlen($mot_de_passe) < 8 || strlen($mot_de_passe) > 20) {
        $_SESSION['error'] = "Le mot de passe doit contenir entre 8 et 20 caractères.";
    } elseif (!preg_match('/[A-Z]/', $mot_de_passe) || !preg_match('/[a-z]/', $mot_de_passe) || !preg_match('/\d/', $mot_de_passe)) {
        $_SESSION['error'] = "Le mot de passe doit contenir une majuscule, une minuscule et un chiffre.";
    } elseif ($mot_de_passe !== $confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        // Hachage du mot de passe
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Insertion en base
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (:nom, :prenom, :email, :mot_de_passe, 'client')");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $hash
        ]);

        $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        header("Location: user/client_dashboard.php");
        exit();
    }

    header("Location: register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <style>
        .error-text {
            color: red;
            font-size: 0.9em;
        }

        .error-field {
            border: 1px solid red;
        }

        .success-message {
            color: green;
        }

        .error-message {
            color: red;
        }

        .container {
            width: 400px;
            margin: auto;
            padding: 30px;
            border: 1px solid #ddd;
            margin-top: 50px;
            border-radius: 10px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Créer un compte</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php elseif (isset($_SESSION['success'])): ?>
        <p class="success-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registerForm">
        <label for="nom">Nom</label><br>
        <input type="text" id="nom" name="nom" required>
        <div class="error-text" id="error-nom"></div><br>

        <label for="prenom">Prénom</label><br>
        <input type="text" id="prenom" name="prenom" required>
        <div class="error-text" id="error-prenom"></div><br>

        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required>
        <div class="error-text" id="error-email"></div><br>

        <label for="mot_de_passe">Mot de passe</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        <div class="error-text" id="error-password"></div><br>

        <label for="confirm_mot_de_passe">Confirmer le mot de passe</label><br>
        <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
        <div class="error-text" id="error-confirm"></div><br>

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        let valid = true;

        // Réinitialiser les messages d'erreur
        document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
        document.querySelectorAll('input').forEach(el => el.classList.remove('error-field'));

        const nom = document.getElementById('nom');
        const prenom = document.getElementById('prenom');
        const email = document.getElementById('email');
        const mdp = document.getElementById('mot_de_passe');
        const confirm = document.getElementById('confirm_mot_de_passe');

        // Validation du nom
        if (nom.value.length < 2 || nom.value.length > 30 || !/^[a-zA-ZÀ-ÿ\- ]+$/.test(nom.value)) {
            document.getElementById('error-nom').textContent = "Le nom doit contenir entre 2 et 30 lettres.";
            nom.classList.add('error-field');
            valid = false;
        }

        // Validation du prénom
        if (prenom.value.length < 2 || prenom.value.length > 30 || !/^[a-zA-ZÀ-ÿ\- ]+$/.test(prenom.value)) {
            document.getElementById('error-prenom').textContent = "Le prénom doit contenir entre 2 et 30 lettres.";
            prenom.classList.add('error-field');
            valid = false;
        }

        // Validation de l'email
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            document.getElementById('error-email').textContent = "L'email est invalide.";
            email.classList.add('error-field');
            valid = false;
        }

        // Validation du mot de passe
        if (mdp.value.length < 8 || mdp.value.length > 20 ||
            !/[A-Z]/.test(mdp.value) || !/[a-z]/.test(mdp.value) || !/[0-9]/.test(mdp.value) || /\s/.test(mdp.value)) {
            document.getElementById('error-password').textContent = "Le mot de passe doit contenir entre 8-20 caractères, avec majuscule, minuscule, chiffre, sans espace.";
            mdp.classList.add('error-field');
            valid = false;
        }

        // Vérification de la confirmation
        if (mdp.value !== confirm.value) {
            document.getElementById('error-confirm').textContent = "Les mots de passe ne correspondent pas.";
            confirm.classList.add('error-field');
            valid = false;
        }

        // Empêche l'envoi si des erreurs sont présentes
        if (!valid) {
            e.preventDefault();
        }
    });
</script>

</body>
</html>
