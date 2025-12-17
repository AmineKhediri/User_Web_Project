<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../Controller/userController.php';
$ctrl = new userController();
$user = $ctrl->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - SUPPORTINI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontoffice.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="logo_supportini.jpg" alt="SUPPORTINI Logo" class="logo">
                <div class="site-title">SUPPORTINI.TN</div>
            </div>
            <nav class="nav-links">
                <a href="index.html" class="nav-link">Accueil</a>
                <span style="color: white; padding: 10px 20px;">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user->getUsername()); ?>
                </span>
                <a href="../../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</h1>
            <p class="page-subtitle">Bienvenue <?php echo htmlspecialchars($user->getUsername()); ?> !</p>
        </div>

        <div class="search-container">
            <h2 class="search-title"><i class="fas fa-info-circle"></i> Vos Informations</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="padding: 20px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 12px; font-weight: 600;">Nom d'utilisateur</p>
                    <p style="font-size: 16px; font-weight: 600; word-break: break-word;"><?php echo htmlspecialchars($user->getUsername()); ?></p>
                </div>
                <div style="padding: 20px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 12px; font-weight: 600;">Email</p>
                    <p style="font-size: 14px; font-weight: 600; word-break: break-word; overflow-wrap: break-word;"><?php echo htmlspecialchars($user->getEmail()); ?></p>
                </div>
                <div style="padding: 20px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 12px; font-weight: 600;">Rôle</p>
                    <p style="font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($user->getRole()); ?></p>
                </div>
                <div style="padding: 20px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <p style="color: var(--text-muted); margin-bottom: 8px; font-size: 12px; font-weight: 600;">Localisation</p>
                    <p style="font-size: 16px; font-weight: 600;"><?php echo $user->getLocation() ? htmlspecialchars($user->getLocation()) : 'Non défini'; ?></p>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2024 SUPPORTINI - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>
