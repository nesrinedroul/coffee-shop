<?php
session_start();
include('includes/db.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
    $confirm = isset($_POST['confirm_mot_de_passe']) ? $_POST['confirm_mot_de_passe'] : ''; 

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Cette adresse e-mail est déjà utilisée.";
        header("Location: register.php");
        exit();
    }

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
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (:nom, :prenom, :email, :mot_de_passe, 'client')");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $hash
        ]);

        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'inscription.";
            header("Location: register.php");
            exit();
        }
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
</head>
<body>
    <div class="split-container register">
        <div class="illustration-section">
            <div class="welcome-content">
                <h2>Bienvenue !</h2>
                <p>Déjà membre de notre communauté ?</p>
                <a href="login.php" class="cta-button">Se connecter</a>
            </div>
        </div>
        <div class="form-section">
            <div class="form-header">
                <h2>Créer un compte</h2>
                <p>Rejoignez notre coffee community en quelques étapes simples</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php elseif (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="registerForm">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                    <div class="error-text" id="error-prenom"></div>
                </div>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                    <div class="error-text" id="error-nom"></div>
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required>
                    <div class="error-text" id="error-email"></div>
                </div>

                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                    <div class="error-text" id="error-password"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_mot_de_passe">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
                    <div class="error-text" id="error-confirm"></div>
                </div>

                <button type="submit" class="primary-btn">S'inscrire</button>
            </form>

            <div class="legal-text">
                <p>En cliquant sur S'inscrire, vous acceptez nos <a href="#">Conditions d'utilisation</a> et notre <a href="#">Politique de confidentialité</a>.</p>
            </div>
        </div>
    </div>
<script>
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        let valid = true;

        document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
        document.querySelectorAll('input').forEach(el => el.classList.remove('error-field'));

        const nom = document.getElementById('nom');
        const prenom = document.getElementById('prenom');
        const email = document.getElementById('email');
        const mdp = document.getElementById('mot_de_passe');
        const confirm = document.getElementById('confirm_mot_de_passe');

        if (nom.value.length < 2 || nom.value.length > 30 || !/^[a-zA-ZÀ-ÿ\- ]+$/.test(nom.value)) {
            document.getElementById('error-nom').textContent = "Le nom doit contenir entre 2 et 30 lettres.";
            nom.classList.add('error-field');
            valid = false;
        }
        if (prenom.value.length < 2 || prenom.value.length > 30 || !/^[a-zA-ZÀ-ÿ\- ]+$/.test(prenom.value)) {
            document.getElementById('error-prenom').textContent = "Le prénom doit contenir entre 2 et 30 lettres.";
            prenom.classList.add('error-field');
            valid = false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            document.getElementById('error-email').textContent = "L'email est invalide.";
            email.classList.add('error-field');
            valid = false;
        }
        if (mdp.value.length < 8 || mdp.value.length > 20 ||
            !/[A-Z]/.test(mdp.value) || !/[a-z]/.test(mdp.value) || !/[0-9]/.test(mdp.value) || /\s/.test(mdp.value)) {
            document.getElementById('error-password').textContent = "Le mot de passe doit contenir entre 8-20 caractères, avec majuscule, minuscule, chiffre, sans espace.";
            mdp.classList.add('error-field');
            valid = false;
        }
        if (mdp.value !== confirm.value) {
            document.getElementById('error-confirm').textContent = "Les mots de passe ne correspondent pas.";
            confirm.classList.add('error-field');
            valid = false;
        }
        if (!valid) {
            e.preventDefault();
        }
    });
</script>

</body>
</html>
