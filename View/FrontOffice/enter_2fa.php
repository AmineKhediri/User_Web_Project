<?php
session_start();
require_once __DIR__ . '/../../Controller/userController.php';

// Verify we are in the pending state
if (!isset($_SESSION['pending_2fa_user']) || !isset($_SESSION['pending_2fa_status'])) {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['otp_code'];
    $ctrl = new userController();
    $userId = $_SESSION['pending_2fa_user'];
    
    if ($ctrl->verifyLoginTwoFA($userId, $code)) {
        // SUCCESS: Elevate Session
        $user = $ctrl->getUserById($userId);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        // Remove pending flags
        unset($_SESSION['pending_2fa_user']);
        unset($_SESSION['pending_2fa_status']);
        
        if ($user['role'] == 'admin') {
            header("Location: ../BackOffice/users.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Code incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification 2FA - SUPPORTINI</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="frontoffice.css">
    <style>
        body { background-color: #1a1a1a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Montserrat', sans-serif; }
        .auth-container { background: #2a2a2a; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 400px; text-align: center; }
        .form-control { background: #333; border: 1px solid #444; color: white; margin-bottom: 20px; }
        h2 { color: #d32f2f; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <i class="fas fa-shield-alt" style="font-size: 50px; color: #d32f2f; margin-bottom: 20px;"></i>
        <h2>Vérification 2FA</h2>
        <p style="color: #ccc; margin-bottom: 30px;">Ouvrez Google Authenticator et entrez le code.</p>
        
        <?php if ($error): ?>
            <div style="color: #ff5252; margin-bottom: 20px; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 4px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp_code" class="form-control" placeholder="000000" style="text-align: center; font-size: 24px; letter-spacing: 5px;" required autofocus autocomplete="off">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">Vérifier</button>
        </form>
        
        <p style="margin-top: 20px;">
            <a href="login.php" style="color: #777; font-size: 14px; text-decoration: none;">Retour à la connexion</a>
        </p>
    </div>
</body>
</html>
