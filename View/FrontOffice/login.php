<?php
require_once __DIR__ . '/../../Controller/userController.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $ctrl = new userController();
    $users = $ctrl->getAllUsers();
    $loggedIn = false;
    foreach ($users as $user) {
        if ($user->getEmail() == $email && password_verify($password, $user->getPassword())) {
            session_start();
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['role'] = $user->getRole();
            $loggedIn = true;
            if ($user->getRole() == 'admin') {
                header("Location: ../BackOffice/users.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    }
    if (!$loggedIn) {
        $message = "Email ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SUPPORTINI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontoffice.css">
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
                <a href="login.php" class="nav-link active">Connexion</a>
                <a href="signup.php" class="nav-link">Inscription</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-sign-in-alt"></i> Connexion</h1>
            <p class="page-subtitle">Connectez-vous à votre compte SUPPORTINI</p>
        </div>

        <div class="search-container" style="max-width: 500px; margin: 0 auto;">
            <?php if ($message): ?>
                <div style="color: #ff6659; margin-bottom: 20px; padding: 12px; background: rgba(211, 47, 47, 0.1); border-radius: 4px; border-left: 4px solid var(--primary-color);">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" class="search-form">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </div>
            </form>
            <p style="text-align: center; margin-top: 20px; color: var(--text-muted);">
                Pas de compte ? <a href="signup.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Inscrivez-vous</a>
            </p>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2025 SUPPORTINI - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>
    </main>
</body>
</html>