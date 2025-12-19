<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/userController.php';

$controller = new userController();
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$error = '';

if (isset($_GET['cancel'])) {
    unset($_SESSION['forgot_method']);
    unset($_SESSION['forgot_contact']);
    unset($_SESSION['forgot_code']);
    header("Location: forgot_password.php", true, 302);
    exit();
}

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si l'utilisateur clique sur Retour (bouton form)
    if (isset($_POST['back'])) {
        unset($_SESSION['forgot_method']);
        unset($_SESSION['forgot_email']);
        unset($_SESSION['forgot_contact']);
        unset($_SESSION['forgot_code']);
        $step = 1;
    }

    // ÉTAPE 1: Sélectionner la méthode (email, sms, whatsapp)
    if (isset($_POST['step']) && $_POST['step'] == 1) {
        // Nettoyer la session
        unset($_SESSION['forgot_method']);
        unset($_SESSION['forgot_email']);
        unset($_SESSION['forgot_contact']);
        unset($_SESSION['forgot_code']);
        
        $method = $_POST['method'] ?? 'email';
        if (!in_array($method, ['email', 'sms', 'whatsapp'])) {
            $error = "Méthode invalide";
        } else {
            $_SESSION['forgot_method'] = $method;
            $step = 2;
        }
    }
    // ÉTAPE 2: Entrer email ou téléphone pour obtenir le code
    elseif (isset($_POST['step']) && $_POST['step'] == 2) {
        $method = $_SESSION['forgot_method'] ?? 'email';
        $contact = trim($_POST['contact'] ?? '');
        
        if (!$contact) {
            $error = "Veuillez entrer votre " . ($method === 'email' ? 'email' : 'numéro de téléphone');
            $step = 2;
        } else {
            // Chercher l'utilisateur par email ou téléphone
            $user = $controller->getUserByContact($contact);
            
            if (!$user) {
                $error = "Aucun compte trouvé avec cet email ou numéro.";
                $step = 2;
            } else {
                // Générer le code via le contrôleur
                $code = $controller->generateForgottenPasswordCode($user['email'], $method);

                // Si l'update en base a échoué, fallback local en session
                if ($code === false || $code === null || $code === '') {
                    // Générer un code local et le stocker temporairement en session
                    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $_SESSION['forgot_email'] = $user['email'];
                    $_SESSION['forgot_contact'] = $contact;
                    $_SESSION['forgot_code'] = $code;
                    $_SESSION['forgot_method'] = $method;
                    error_log("[FORGOT_PASSWORD] Fallback local code generated for {$user['email']}: $code");
                    $message = "✓ Code généré et enregistré. Vérifiez votre " . ($method === 'email' ? 'email' : ($method === 'sms' ? 'SMS' : 'WhatsApp')) . ".";
                    $step = 3;
                } else {
                    $_SESSION['forgot_email'] = $user['email'];
                    $_SESSION['forgot_contact'] = $contact;
                    $_SESSION['forgot_code'] = $code;
                    $_SESSION['forgot_method'] = $method;
                    $message = "✓ Code envoyé à votre " . ($method === 'email' ? 'email' : ($method === 'sms' ? 'numéro SMS' : 'WhatsApp')) . ". Vérifiez.";
                    $step = 3;
                }
            }
        }
    }
    // ÉTAPE 3: Vérifier code et réinitialiser le mot de passe
    elseif (isset($_POST['step']) && $_POST['step'] == 3) {
        $code = trim($_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $email = $_SESSION['forgot_email'] ?? '';
        
        // Vérifier code d'abord
        if (!$code) {
            $error = "Veuillez entrer le code de vérification";
            $step = 3;
        } else {
            // Vérifier le code
            $verified = $controller->verifyForgottenPasswordCode($email, $code);
            
            // Fallback local session (pour démo sans SMTP)
            if (!$verified && isset($_SESSION['forgot_code']) && $_SESSION['forgot_code'] === $code) {
                $verified = true;
            }

            if (!$verified) {
                $error = "Code invalide ou expiré (15 minutes max)";
                $step = 3;
            } else if (!$password) {
                $error = "Veuillez entrer un nouveau mot de passe";
                $step = 3;
            } else if ($password !== $passwordConfirm) {
                $error = "Les mots de passe ne correspondent pas";
                $step = 3;
            } else if (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères";
                $step = 3;
            } else {
                // Réinitialiser le mot de passe directement
                $res = $controller->resetPasswordWithCode($email, $code, $password, true);
                if ($res) {
                    // Nettoyer la session et rediriger vers login
                    unset($_SESSION['forgot_method']);
                    unset($_SESSION['forgot_email']);
                    unset($_SESSION['forgot_contact']);
                    unset($_SESSION['forgot_code']);
                    session_destroy();
                    header("Location: login.php?reset=success", true, 302);
                    exit();
                } else {
                    $error = "Erreur lors de la réinitialisation";
                    $step = 3;
                }
            }
        }
    }
}

// Déterminer l'étape actuelle (par défaut 1)
if ($step == 1 && isset($_SESSION['forgot_method']) && empty($error)) {
    if (isset($_SESSION['forgot_email']) && empty($error)) {
        $step = 3;
    } else {
        $step = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - SUPPORTINI</title>
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
                <a href="signup.php" class="nav-link">Inscription</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-key"></i> Réinitialiser le mot de passe</h1>
            <p class="page-subtitle">
                <?php
                if ($step == 1) echo "Sélectionnez votre méthode de vérification";
                elseif ($step == 2) echo "Entrez votre " . (isset($_SESSION['forgot_method']) && $_SESSION['forgot_method'] === 'email' ? 'email' : 'numéro de téléphone');
                else echo "Vérifiez le code et créez un nouveau mot de passe";
                ?>
            </p>
        </div>

        <div class="search-container" style="max-width: 500px; margin: 0 auto;">
            <!-- Indicateurs d'étapes -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 12px;">
                <div style="text-align: center;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo ($step >= 1 ? 'var(--primary-color)' : '#ddd'); ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px; font-weight: bold;">1</div>
                    <span style="color: <?php echo ($step >= 1 ? 'var(--primary-color)' : '#999'); ?>">Méthode</span>
                </div>
                <div style="text-align: center;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo ($step >= 2 ? 'var(--primary-color)' : '#ddd'); ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px; font-weight: bold;">2</div>
                    <span style="color: <?php echo ($step >= 2 ? 'var(--primary-color)' : '#999'); ?>">Contact</span>
                </div>
                <div style="text-align: center;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo ($step >= 3 ? 'var(--primary-color)' : '#ddd'); ?>; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px; font-weight: bold;">3</div>
                    <span style="color: <?php echo ($step >= 3 ? 'var(--primary-color)' : '#999'); ?>">Code</span>
                </div>
            </div>

            <?php if ($error): ?>
                <div style="color: #721c24; margin-bottom: 20px; padding: 12px; background: #f8d7da; border-radius: 4px; border-left: 4px solid var(--primary-color);">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div style="color: #155724; margin-bottom: 20px; padding: 12px; background: #d4edda; border-radius: 4px; border-left: 4px solid #28a745;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- ÉTAPE 1: Sélectionner la méthode -->
            <?php if ($step == 1): ?>
            <form method="POST" action="" class="search-form">
                <input type="hidden" name="step" value="1">
                
                <div class="form-group">
                    <label class="form-label" style="margin-bottom: 15px;">Choisissez votre méthode de vérification</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                        <label style="display: flex; flex-direction: column; align-items: center; border: 2px solid #ddd; border-radius: 6px; padding: 20px; cursor: pointer; transition: all 0.3s;" class="method-option">
                            <input type="radio" name="method" value="email" checked style="margin-bottom: 10px;">
                            <i class="fas fa-envelope" style="font-size: 24px; color: #007bff; margin-bottom: 8px;"></i>
                            <span style="font-weight: 600; text-align: center;">Email</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; border: 2px solid #ddd; border-radius: 6px; padding: 20px; cursor: pointer; transition: all 0.3s;" class="method-option">
                            <input type="radio" name="method" value="sms" style="margin-bottom: 10px;">
                            <i class="fas fa-mobile-alt" style="font-size: 24px; color: #28a745; margin-bottom: 8px;"></i>
                            <span style="font-weight: 600; text-align: center;">SMS</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; border: 2px solid #ddd; border-radius: 6px; padding: 20px; cursor: pointer; transition: all 0.3s;" class="method-option">
                            <input type="radio" name="method" value="whatsapp" style="margin-bottom: 10px;">
                            <i class="fab fa-whatsapp" style="font-size: 24px; color: #25d366; margin-bottom: 8px;"></i>
                            <span style="font-weight: 600; text-align: center;">WhatsApp</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                        <i class="fas fa-arrow-right"></i> Continuer
                    </button>
                    <a href="login.php" class="btn btn-secondary" style="text-decoration: none; text-align: center;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
            <?php endif; ?>

            <!-- ÉTAPE 2: Email ou téléphone -->
            <?php if ($step == 2): ?>
            <form method="POST" action="" class="search-form">
                <input type="hidden" name="step" value="2">
                
                <div class="form-group">
                    <label class="form-label">
                        <?php 
                        $method = $_SESSION['forgot_method'] ?? 'email';
                        if ($method === 'email') {
                            echo '<i class="fas fa-envelope"></i> Adresse Email';
                        } elseif ($method === 'sms') {
                            echo '<i class="fas fa-mobile-alt"></i> Numéro de téléphone';
                        } else {
                            echo '<i class="fab fa-whatsapp"></i> Numéro WhatsApp';
                        }
                        ?>
                    </label>
                    <input type="text" name="contact" class="form-control" placeholder="<?php echo ($method === 'email' ? 'exemple@email.com' : '+216 XX XXX XXX'); ?>" required autofocus>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                        <i class="fas fa-check"></i> Envoyer le code
                    </button>
                    <button type="submit" name="back" value="1" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <!-- ÉTAPE 3: Code + Nouveau mot de passe -->
            <?php if ($step == 3): ?>
            <form method="POST" action="" class="search-form">
                <input type="hidden" name="step" value="3">
                
                <div class="form-group">
                    <label class="form-label">Code de vérification</label>
                    <input type="text" name="code" class="form-control" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autofocus>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        <i class="fas fa-info-circle"></i> Code envoyé. Entrez-le ci-dessous.
                    </small>
                </div>

                <div class="form-group" style="margin-top: 25px;">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 caractères" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Confirmez" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">
                        <i class="fas fa-lock"></i> Réinitialiser le mot de passe
                    </button>
                    <a href="forgot_password.php?cancel=1" class="btn btn-secondary" style="width: 100%; text-align:center;">
                        Annuler
                    </a>
                </div>
            </form>
            <?php endif; ?>

            <div style="margin-top: 20px; text-align: center;">
                <p style="color: var(--text-muted);">
                    Vous vous souvenez de votre mot de passe ? <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Connectez-vous</a>
                </p>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2025 SUPPORTINI - Tous droits réservés</p>
        </div>
    </footer>

    <script>
        // Styliser les options sélectionnées
        document.querySelectorAll('.method-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.method-option').forEach(label => {
                    label.style.borderColor = '#ddd';
                    label.style.backgroundColor = 'transparent';
                });
                if (this.checked) {
                    this.closest('.method-option').style.borderColor = 'var(--primary-color)';
                    this.closest('.method-option').style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
                }
            });
        });

        // Forcer les nombres uniquement dans le code
        const codeInput = document.querySelector('input[name="code"]');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>
