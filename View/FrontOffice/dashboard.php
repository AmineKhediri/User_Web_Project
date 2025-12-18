<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php", true, 302);
    exit;
}

require_once __DIR__ . '/../../Controller/userController.php';
$ctrl = new userController();
$user = $ctrl->getUserById($_SESSION['user_id']);

if (!$user) {
    // User deleted or session invalid
    session_destroy();
    header("Location: login.php?error=session_invalid", true, 302);
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
$message = "";
$message_type = "";
$error = "";

// 1. EDIT PROFILE LOGIC
if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
     $profileData = [
        'gender' => $_POST['gender'] ?? null,
        'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
        'profession' => trim($_POST['profession'] ?? ''),
        'company' => trim($_POST['company'] ?? ''),
        'nationality' => trim($_POST['nationality'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'bio' => trim($_POST['bio'] ?? '')
    ];

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['size'] > 0) {
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowedExts) && $file['size'] <= 5242880 && $file['error'] == UPLOAD_ERR_OK) {
             // Encode as base64 for security and portability
             $imageData = file_get_contents($file['tmp_name']);
             if ($imageData === false) {
                 $error = "Impossible de lire le fichier uploadé.";
             } else {
                 $mimeType = mime_content_type($file['tmp_name']);
                 if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                     $error = "Type d'image non supporté.";
                 } else {
                     $profileData['profile_photo'] = "data:" . $mimeType . ";base64," . base64_encode($imageData);
                 }
             }
        } else {
            $error = "Fichier invalide ou trop volumineux (max 5 MB).";
        }
    }

    $socialLinks = [];
    if (!empty($_POST['linkedin'])) $socialLinks['linkedin'] = $_POST['linkedin'];
    if (!empty($_POST['twitter'])) $socialLinks['twitter'] = $_POST['twitter'];
    if (!empty($_POST['facebook'])) $socialLinks['facebook'] = $_POST['facebook'];
    if (!empty($_POST['instagram'])) $socialLinks['instagram'] = $_POST['instagram'];
    if (!empty($socialLinks)) $profileData['social_links'] = json_encode($socialLinks);

    if (empty($error)) {
        $res = $ctrl->updateProfile($user['id'], $profileData);
        if ($res === true) {
            $message = "Profil mis à jour avec succès!";
            $message_type = "success";
            $user = $ctrl->getUserById($user['id']); 
        } else {
            $error = $res;
            $message_type = "error";
        }
    }
}

// 2. CHANGE PASSWORD LOGIC
if ($action == 'password' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $cnf = $_POST['confirm_password'];
    
    if ($new !== $cnf) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($new) < 6) {
        $error = "Le mot de passe doit faire 6 caractères minimum.";
    } else {
        $res = $ctrl->changePassword($user['id'], $old, $new);
        if ($res === true) {
            $message = "Mot de passe modifié avec succès!";
            $message_type = "success";
        } else {
            $error = "Ancien mot de passe incorrect.";
        }
    }
}

// 3. 2FA LOGIC
$twofa_secret_new = '';
$twofa_qr = '';
$show_recovery = false;
$recovery_codes_display = [];

if ($action == 'security') {
    // ENABLE INIT
    if (isset($_POST['init_2fa'])) {
        $data = $ctrl->enableTwoFA($user['id']);
        $twofa_secret_new = $data['secret'];
        $twofa_qr = $data['qrCodeUrl'];
    }
    
    // CONFIRM ENABLE
    if (isset($_POST['confirm_2fa'])) {
        $code = $_POST['otp_code'];
        $res = $ctrl->verifyTwoFAActivation($user['id'], $code);
        if ($res['success']) {
            $message = "2FA Activé avec succès ! Sauvegardez vos codes de secours.";
            $message_type = "success";
            $show_recovery = true;
            $recovery_codes_display = $res['recovery_codes'];
            $user = $ctrl->getUserById($user['id']); // Refresh
        } else {
            $error = "Code incorrect.";
        }
    }
    
    // DISABLE
    if (isset($_POST['disable_2fa'])) {
        $pass = $_POST['password'];
        $code = $_POST['otp_code'];
        $res = $ctrl->disableTwoFA($user['id'], $pass, $code);
        if ($res === true) {
             $message = "2FA Désactivé.";
             $message_type = "success";
             $user = $ctrl->getUserById($user['id']); // Refresh
        } else {
            $error = $res; // Error message
        }
    }
}

$socials = json_decode($user['social_links'] ?? '{}', true) ?: [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - SUPPORTINI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontoffice.css?v=<?php echo time(); ?>">
</head>
<body class="<?php echo ($user['role'] === 'admin') ? 'admin-layout' : 'user-layout'; ?>">

    <?php if ($user['role'] === 'admin'): ?>
    <!-- ADMIN SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../../logo_supportini.jpg" alt="SUPPORTINI Logo" class="logo">
            <h2 class="sidebar-title">SUPPORTINI <span>Admin</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="../BackOffice/users.php" class="sidebar-link">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="dashboard.php" class="sidebar-link active">
                <i class="fas fa-user-circle"></i>
                <span>Mon Profil</span>
            </a>
            <a href="../../logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </div>
    <?php else: ?>
    <!-- USER HEADER -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section" style="display:flex; align-items:center; gap:10px;">
                <img src="../../logo_supportini.jpg" alt="SUPPORTINI Logo" class="logo">
                <div class="site-title" style="font-size:24px; font-weight:700; color:white;">SUPPORTINI.TN</div>
            </div>
            <nav class="nav-links" style="display:flex; gap:20px;">
                <a href="index.html" style="color:white; text-decoration:none; padding:10px;">Accueil</a>
                <span style="color:white; padding:10px;"><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="../../logout.php" style="color:white; text-decoration:none; padding:10px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </nav>
        </div>
    </header>
    <?php endif; ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php 
                if ($action == 'edit') echo '<i class="fas fa-user-edit"></i> Modifier mon profil';
                elseif ($action == 'password') echo '<i class="fas fa-key"></i> Changer mot de passe';
                else echo '<i class="fas fa-tachometer-alt"></i> Tableau de bord';
                ?>
            </h1>
            <?php if ($action == 'dashboard'): ?>
            <p class="page-subtitle">Bienvenue <?php echo htmlspecialchars($user['username']); ?> !</p>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="msg-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- VIEW: DASHBOARD (Default) -->
        <?php if ($action == 'dashboard'): ?>
        <div class="dash-container">
            <div class="dash-card">
                <h4 class="dash-title"><i class="fas fa-info-circle"></i> Vos informations</h4>
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">Nom d'utilisateur</div>
                        <div class="info-val"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Email</div>
                        <div class="info-val"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Rôle</div>
                        <div class="info-val"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Localisation</div>
                        <div class="info-val"><?php echo !empty($user['location']) ? htmlspecialchars($user['location']) : 'Non défini'; ?></div>
                    </div>
                </div>
            </div>


            <div class="dash-card">
                <h4 class="dash-title"><i class="fas fa-shield-alt"></i> Sécurité</h4>
                <div class="info-grid">
                    <div class="info-box" style="grid-column: span 2;">
                        <div class="info-label">Double Authentification (2FA)</div>
                        <div class="info-val">
                            <?php if ($user['twofa_enabled']): ?>
                                <span style="color: green; font-weight: bold;">Activé <i class="fas fa-check-circle"></i></span>
                            <?php else: ?>
                                <span style="color: #aaa;">Désactivé</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="action-grid" style="margin-top: 15px;">
                     <a href="dashboard.php?action=security" class="action-btn" style="background: linear-gradient(135deg, #444, #222);">
                        <i class="fas fa-lock" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        Gérer la sécurité / 2FA
                    </a>
                </div>
            </div>

            <div class="dash-card">
                <h4 class="dash-title"><i class="fas fa-cogs"></i> Votre compte</h4>
                <div class="action-grid">
                    <a href="dashboard.php?action=edit" class="action-btn btn-edit">
                        <i class="fas fa-user-edit" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        Modifier votre profil
                    </a>
                    <a href="dashboard.php?action=password" class="action-btn btn-pass">
                        <i class="fas fa-key" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        Changer votre mot de passe
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- VIEW: SECURITY / 2FA -->
        <?php if ($action == 'security'): ?>
        <div class="profile-container" style="max-width: 600px;">
            <h3 style="border-bottom: 1px solid #444; padding-bottom: 10px; margin-bottom: 20px; color: #d32f2f;">
                <i class="fas fa-shield-alt"></i> Gestion double authentification
            </h3>

            <?php if ($show_recovery): ?>
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <h4 style="margin-top:0;"><i class="fas fa-exclamation-triangle"></i> CODES DE SECOURS</h4>
                    <p>Sauvegardez ces codes en lieu sûr. Ils ne s'afficheront plus jamais.</p>
                    <div style="background: white; padding: 10px; font-family: monospace; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <?php foreach($recovery_codes_display as $rc): ?>
                            <span><?php echo $rc; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 15px; text-align: center;">
                        <a href="dashboard.php?action=security" class="btn btn-primary">J'ai bien noté mes codes</a>
                    </div>
                </div>
            <?php elseif (!$user['twofa_enabled']): ?>
                <!-- ENABLE FLOW -->
                <?php if ($twofa_qr): ?>
                    <div style="text-align: center;">
                        <p>1. Scannez ce QR Code avec Google Authenticator</p>
                        <img src="<?php echo $twofa_qr; ?>" alt="QR Code" style="border: 5px solid white; border-radius: 4px;">
                        <p style="font-size: 12px; color: #aaa; margin-top: 10px;">Secret: <?php echo $twofa_secret_new; ?></p>
                        
                        <form method="POST" style="margin-top: 20px;">
                            <input type="hidden" name="action" value="security">
                            <label>2. Entrez le code à 6 chiffres</label>
                            <input type="text" name="otp_code" class="form-control" style="text-align: center; font-size: 20px; letter-spacing: 5px; width: 200px; margin: 10px auto;" placeholder="000000" required>
                            
                            <button type="submit" name="confirm_2fa" class="btn btn-primary">Activer</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-lock-open" style="font-size: 50px; color: #aaa; margin-bottom: 20px;"></i>
                        <p>Le 2FA n'est pas activé. Pour sécuriser votre compte, activez-le maintenant.</p>
                        <form method="POST">
                            <button type="submit" name="init_2fa" class="btn btn-primary">
                                Commencer l'activation
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- DISABLE FLOW -->
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-lock" style="font-size: 50px; color: green; margin-bottom: 20px;"></i>
                    <p style="color: green; font-weight: bold; font-size: 18px;">Votre compte est sécurisé par 2FA.</p>
                    
                    <div style="background: rgba(255,0,0,0.05); border: 1px solid rgba(255,0,0,0.2); padding: 20px; border-radius: 5px; margin-top: 30px;">
                        <h4 style="color: #d32f2f;">Désactiver 2FA</h4>
                        <p style="font-size: 14px; margin-bottom: 15px;">Pour désactiver, confirmez votre identité.</p>
                        <form method="POST">
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="Mot de passe actuel" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="otp_code" class="form-control" placeholder="Code 2FA actuel" required style="text-align: center; letter-spacing: 2px;">
                            </div>
                            <button type="submit" name="disable_2fa" class="btn btn-primary" style="background: #d32f2f; border-color: #d32f2f;">
                                Désactiver
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
             <div style="margin-top: 20px; text-align: center;">
                <a href="dashboard.php" class="btn btn-outline" style="border: 1px solid #444; color: #aaa;">
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- VIEW: EDIT PROFILE -->
        <?php if ($action == 'edit'): ?>
        <div class="profile-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="photo-section">
                    <?php 
                        $photo = $user['profile_photo']; 
                        // FrontOffice uses direct path usually 'uploads/xxx'
                        $displayPhoto = $photo;
                    ?>
                    <div class="photo-preview">
                        <?php if ($displayPhoto): ?>
                            <img src="<?php echo htmlspecialchars($displayPhoto); ?>" alt="Photo">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:40px;">👤</div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; color:#ccc;">Changer la photo</label>
                        <input type="file" name="profile_photo">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Genre</label>
                        <select name="gender">
                            <option value="">-- Sélectionner --</option>
                            <option value="male" <?php echo ($user['gender']=='male')?'selected':''; ?>>Homme</option>
                            <option value="female" <?php echo ($user['gender']=='female')?'selected':''; ?>>Femme</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date de naissance</label>
                        <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']??''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Profession</label>
                        <input type="text" name="profession" value="<?php echo htmlspecialchars($user['profession']??''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Entreprise</label>
                        <input type="text" name="company" value="<?php echo htmlspecialchars($user['company']??''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nationalité</label>
                        <input type="text" name="nationality" value="<?php echo htmlspecialchars($user['nationality']??''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Localisation</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']??''); ?>">
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" rows="3"><?php echo htmlspecialchars($user['bio']??''); ?></textarea>
                    </div>
                </div>

                <h4 style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px; margin-top:30px; color:#d32f2f;">Réseaux Sociaux</h4>
                
                <div class="form-row full">
                    <div class="form-group">
                        <label>LinkedIn</label>
                        <input type="text" name="linkedin" value="<?php echo htmlspecialchars($socials['linkedin']??''); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Twitter</label>
                        <input type="text" name="twitter" value="<?php echo htmlspecialchars($socials['twitter']??''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Instagram</label>
                        <input type="text" name="instagram" value="<?php echo htmlspecialchars($socials['instagram']??''); ?>">
                    </div>
                </div>

                <div class="form-actions-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="dashboard.php" class="btn btn-outline" style="border: 1px solid #444; color: #aaa;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- VIEW: CHANGE PASSWORD -->
        <?php if ($action == 'password'): ?>
        <div class="profile-container" style="max-width: 500px;">
            <form method="POST">
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Ancien mot de passe</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Confirmer nouveau mot de passe</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <div class="form-actions-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="dashboard.php" class="btn btn-outline" style="border: 1px solid #444; color: #aaa;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </main>
    <?php if ($user['role'] !== 'admin'): ?>
    <footer style="background: rgba(0,0,0,0.9); color: white; text-align: center; padding: 20px; margin-top: 40px;">
        <p>&copy; 2025 SUPPORTINI. Tous droits réservés.</p>
    </footer>
    <?php endif; ?>
</body>
</html>