<?php
ob_start();
session_start();
require_once __DIR__ . '/../../Controller/userController.php';

$message = "";
$resetSuccess = "";

// Vérifier le paramètre reset
if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $resetSuccess = "✓ Mot de passe réinitialisé avec succès! Veuillez vous connecter.";
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['code'])) {
    $ctrl = new userController();
    $result = $ctrl->handleGoogleCallback($_GET['code']);
    
    // Log errors for debugging
    if (!is_array($result)) {
        error_log("[GOOGLE_OAUTH_ERROR] Code: " . $_GET['code'] . " | Error: " . $result);
    }
    
    if (is_array($result)) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_role'] = $result['role'];
        $_SESSION['username'] = $result['username'];
        
        // Clear any output buffering and set headers with status code
        
        if ($result['role'] == 'admin') {
            header("Location: ../BackOffice/users.php", true, 302);
        } else {
            header("Location: dashboard.php", true, 302);
        }
        exit;
    } else {
        $message = "❌ Erreur Google Auth: " . $result;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. VERIFICATION CODE 2FA
    if (isset($_POST['otp_code'])) {
        $ctrl = new userController();
        $user = $ctrl->verifyAdmin2FA($_POST['otp_code']);
        
        if ($user) {
            header("Location: ../BackOffice/users.php");
            exit;
        } else {
            $message = "❌ Code incorrect ou expiré.";
            $show2FA = true; // Stay on 2FA screen
        }
    } 
    // 2. TENTATIVE DE CONNEXION INITIALE
    else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $message = "Veuillez entrer email et mot de passe";
        } else {
            $ctrl = new userController();
            
            // Checking account status logic... (kept same)
            $user = $ctrl->getUserByEmail($email);
            if ($user && $ctrl->isAccountLocked($user['id'])) {
                $message = "⏳ Compte temporairement verrouillé. Réessayez dans 30 minutes.";
            } else if ($user && $user['is_blocked']) {
                $message = "🚫 Compte bloqué. Raison: " . ($user['blocked_reason'] ?: 'Décision administrative');
            } else if ($user && $user['is_banned']) {
                $message = "🔴 Compte banni. Raison: " . ($user['banned_reason'] ?: 'Violation des conditions');
            } else {
                $result = $ctrl->validateLogin($email, $password);
                
                // CAS 1: 2FA REQUIS (ADMIN OLD)
                if ($result === '2FA_REQUIRED') {
                    $show2FA = true;
                    $message = "🔒 Code de sécurité envoyé à votre email.";
                } 
                // CAS 1b: 2FA TOTP REQUIS (NOUVEAU)
                else if (is_array($result) && isset($result['status']) && $result['status'] === '2FA_TOTP_REQUIRED') {
                     $_SESSION['pending_2fa_user'] = $result['user_id'];
                     $_SESSION['pending_2fa_status'] = true;
                     header("Location: enter_2fa.php", true, 302);
                     exit;
                }
                // CAS 2: CONNEXION REUSSIE
                else if (is_array($result)) {
                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['user_role'] = $result['role'];
                    $_SESSION['username'] = $result['username'];
                    
                    if ($result['role'] == 'admin') {
                        header("Location: ../BackOffice/users.php", true, 302);
                    } else {
                        header("Location: dashboard.php", true, 302);
                    }
                    exit;
                } 
                // CAS 3: ECHEC
                else if ($result === 'LOCKED') {
                    $message = "⏳ Trop de tentatives. Compte verrouillé.";
                } else if ($result === 'BANNED') {
                    $message = "🚫 Accès refusé.";
                } else {
                    $message = "❌ Email ou mot de passe incorrect";
                }
            }
        }
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
            <?php if ($resetSuccess): ?>
                <div style="color: #155724; margin-bottom: 20px; padding: 12px; background: #d4edda; border-radius: 4px; border-left: 4px solid #28a745;">
                    <?php echo htmlspecialchars($resetSuccess); ?>
                </div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div style="color: #721c24; margin-bottom: 20px; padding: 12px; background: #f8d7da; border-radius: 4px; border-left: 4px solid var(--primary-color);">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($show2FA) && $show2FA): ?>
                <!-- FORMULAIRE 2FA -->
                <form method="POST" action="" class="search-form">
                    <div style="text-align:center; margin-bottom:20px;">
                        <i class="fas fa-shield-alt" style="font-size:40px; color:var(--primary-color);"></i>
                        <h3 style="margin-top:10px; color:white;">Vérification Admin</h3>
                        <p style="color:#aaa;">Entrez le code envoyé par email</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code OTP</label>
                        <input type="text" name="otp_code" class="form-control" placeholder="6 chiffres" required style="text-align:center; letter-spacing:5px; font-size:20px;">
                    </div>
                    
                    <!-- DEV HINT FOR USER -->
                    <?php if (file_exists('../../logs/email_log.txt')): ?>
                        <div style="background:#222; padding:10px; border-radius:4px; margin:10px 0; font-family:monospace; color:#0f0; font-size:12px; max-height:100px; overflow:auto;">
                            <strong>Dev Mode (Code):</strong><br>
                            <?php 
                                $logs = file_get_contents('../../logs/email_log.txt');
                                preg_match_all('/([0-9]{6})/', $logs, $matches);
                                if (!empty($matches[0])) {
                                    echo end($matches[0]);
                                } else {
                                    echo "Pas de code trouvé";
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions" style="flex-direction: column; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-check-circle"></i> Vérifier
                        </button>
                    </div>
                    <p style="text-align: center; margin-top: 15px;">
                        <a href="login.php" style="color: #aaa; font-size: 14px;">Retour</a>
                    </p>
                </form>
            <?php else: ?>
                <!-- FORMULAIRE LOGIN STANDARD -->
                <form method="POST" action="" class="search-form">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                    </div>
                    <div class="form-actions" style="flex-direction: column; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                        <!-- Google Login -->
                        <!-- Google Login (REAL) -->
                        <a href="https://accounts.google.com/o/oauth2/v2/auth?response_type=code&client_id=<?php echo GOOGLE_CLIENT_ID; ?>&scope=openid%20email%20profile&redirect_uri=<?php echo urlencode(GOOGLE_REDIRECT_URI); ?>&access_type=online" 
                           class="btn btn-outline" style="width: 100%; border-color: #ddd; color: #fff; display:flex; align-items:center; justify-content:center; gap:10px;">
                            <img src="https://www.google.com/favicon.ico" alt="Google" style="width:16px;"> Se connecter avec Google
                        </a>
                    </div>
                </form>
            <?php endif; ?>
            <div style="margin-top: 20px; text-align: center;">
                <p style="color: var(--text-muted); margin-bottom: 10px;">
                    <a href="forgot_password.php" style="color: var(--primary-color); text-decoration: none;">🔐 Mot de passe oublié ?</a>
                </p>
                <p style="color: var(--text-muted);">
                    Pas de compte ? <a href="signup.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Inscrivez-vous</a>
                </p>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2025 SUPPORTINI - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>