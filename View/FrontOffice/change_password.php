<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/userController.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$controller = new userController();
$user = $controller->getUserById($_SESSION['user_id']);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$oldPassword) {
        $error = "Veuillez entrer votre ancien mot de passe";
    } elseif (!$newPassword) {
        $error = "Veuillez entrer un nouveau mot de passe";
    } elseif (strlen($newPassword) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif ($oldPassword === $newPassword) {
        $error = "Le nouveau mot de passe doit être différent de l'ancien";
    } else {
        if ($controller->changePassword($_SESSION['user_id'], $oldPassword, $newPassword)) {
            $message = "✓ Mot de passe modifié avec succès!";
        } else {
            $error = "L'ancien mot de passe est incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer mon mot de passe - SUPPORTINI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontoffice.css">
    <style>
        .password-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .password-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .password-header h1 {
            font-size: 28px;
            color: #333;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .password-wrapper {
            position: relative;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            padding-right: 40px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.1);
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 18px;
            background: none;
            border: none;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }
        .strength-bar {
            width: 100%;
            height: 4px;
            background: #eee;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            background: #28a745;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #e9ecef;
            color: #333;
            text-decoration: none;
            text-align: center;
        }
        .btn-secondary:hover {
            background: #dfe4e9;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="../../logo_supportini.jpg" alt="SUPPORTINI Logo" class="logo">
                <div class="site-title">SUPPORTINI.TN</div>
            </div>
            <nav class="nav-links">
                <a href="index.html" class="nav-link">Accueil</a>
                <span style="color: white; padding: 10px 20px;">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                </span>
                <a href="../../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

<div class="password-container">
    <div class="password-header">
        <h1>🔑 Changer mon mot de passe</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="old_password">Ancien mot de passe *</label>
            <div class="password-wrapper">
                <input type="password" id="old_password" name="old_password" required placeholder="Entrez votre mot de passe actuel">
                <button type="button" class="toggle-password" onclick="togglePassword('old_password')">👁️</button>
            </div>
        </div>

        <div class="form-group">
            <label for="new_password">Nouveau mot de passe *</label>
            <div class="password-wrapper">
                <input type="password" id="new_password" name="new_password" required placeholder="Min. 6 caractères" oninput="checkPasswordStrength()">
                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">👁️</button>
            </div>
            <div class="password-strength">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <span id="strengthText"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe *</label>
            <div class="password-wrapper">
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirmez le nouveau mot de passe">
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">💾 Modifier le mot de passe</button>
            <a href="dashboard.php" class="btn btn-secondary">← Retour</a>
        </div>
    </form>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        field.type = field.type === 'password' ? 'text' : 'password';
    }

    function checkPasswordStrength() {
        const password = document.getElementById('new_password').value;
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        let text = '';
        
        if (password.length >= 6) strength += 25;
        if (password.length >= 12) strength += 25;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        if (strength < 25) {
            text = 'Très faible';
            strengthFill.style.background = '#dc3545';
        } else if (strength < 50) {
            text = 'Faible';
            strengthFill.style.background = '#ffc107';
        } else if (strength < 75) {
            text = 'Moyen';
            strengthFill.style.background = '#ffc107';
        } else {
            text = 'Fort';
            strengthFill.style.background = '#28a745';
        }
        
        strengthFill.style.width = strength + '%';
        strengthText.textContent = text;
    }
</script>

    <footer style="background: rgba(0,0,0,0.9); color: white; text-align: center; padding: 20px; margin-top: 40px;">
        <p>&copy; 2025 SUPPORTINI. Tous droits réservés.</p>
    </footer>
</body>
</html>
