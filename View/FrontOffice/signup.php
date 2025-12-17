<?php
require_once __DIR__ . '/../../Controller/userController.php';

$message = "";
$message_type = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $location = $_POST['location'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $demande_psy = isset($_POST['demande_psy']) ? 1 : 0;
    
    // Les nouveaux utilisateurs sont TOUJOURS "utilisateur"
    // Si quelqu'un veut être psychologue, il demande à l'admin
    $role = 'utilisateur';

    $user = new User($username, $email, $password, $location, $phone_number, $bio, $role);
    $ctrl = new userController();
    $result = $ctrl->addUser($user, $demande_psy);
    
    if (strpos($result, "successfully") !== false) {
        $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        $message_type = "success";
    } else {
        $message = $result;
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SUPPORTINI.TN</title>
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
                <a href="login.php" class="nav-link">Connexion</a>
                <a href="signup.php" class="nav-link active">Inscription</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-user-plus"></i> Inscription</h1>
            <p class="page-subtitle">Créez votre compte SUPPORTINI.TN</p>
        </div>

        <div class="search-container" style="max-width: 600px; margin: 0 auto;">
            <?php if ($message): ?>
                <div style="color: <?php echo $message_type === 'success' ? '#4caf50' : '#ff6659'; ?>; margin-bottom: 20px; padding: 12px; background: <?php echo $message_type === 'success' ? 'rgba(76, 175, 80, 0.1)' : 'rgba(211, 47, 47, 0.1)'; ?>; border-radius: 4px; border-left: 4px solid <?php echo $message_type === 'success' ? '#4caf50' : 'var(--primary-color)'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" class="search-form">
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-control" placeholder="Votre nom d'utilisateur" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="location" class="form-control" placeholder="Votre localisation (optionnel)">
                </div>
                <div class="form-group">
                    <label class="form-label">Numéro de téléphone</label>
                    <input type="text" name="phone_number" class="form-control" placeholder="Votre numéro de téléphone (optionnel)">
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" placeholder="Votre bio (optionnel)"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label" style="display: flex; align-items: center; cursor: pointer; gap: 10px;">
                        <input type="checkbox" name="demande_psy" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                        <span style="font-weight: 500;">Je suis un psychologue/praticien</span>
                    </label>
                    <small style="color: #999; margin-top: 8px; display: block;">
                        ✓ Si vous cochez cette case, l'administrateur recevra votre demande pour devenir psychologue<br>
                        ✓ Après vérification, votre rôle sera changé de "Utilisateur" à "Psychologue"
                    </small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> S'inscrire
                </button>
            </form>
            <p style="text-align: center; margin-top: 20px; color: var(--text-muted);">
                Déjà un compte ? <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Connectez-vous</a>
            </p>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2025 SUPPORTINI.TN - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>