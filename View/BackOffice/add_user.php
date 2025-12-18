<?php
require_once __DIR__ . '/../../Controller/userController.php';
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../FrontOffice/login.php");
    exit;
}

$message = "";
$message_type = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $location = $_POST['location'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $role = $_POST['role'] ?? 'utilisateur';

    $user = new User($username, $email, $password, $location, $phone_number, $bio, $role);
    $ctrl = new userController();
    $result = $ctrl->addUser($user);
    
    if (strpos($result, "successfully") !== false) {
        $message = "Utilisateur ajouté avec succès !";
        $message_type = "success";
        header("refresh:2;url=users.php");
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
    <title>Ajouter Utilisateur - SUPPORTINI Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="backoffice.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../../logo_supportini.jpg" alt="SUPPORTINI Logo" class="sidebar-logo">
            <h2 class="sidebar-title">SUPPORTINI <span>Admin</span></h2>
        </div>
        <nav class="sidebar-nav">
            <a href="users.php" class="sidebar-link">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="../FrontOffice/dashboard.php" class="sidebar-link">
                <i class="fas fa-user-circle"></i>
                <span>Mon Profil</span>
            </a>
            <a href="../../logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-user-plus"></i> Ajouter Utilisateur</h1>
        </div>

        <div class="form-container">
            <?php if ($message): ?>
                <div id="msg" class="<?php echo $message_type === 'success' ? 'msg-success' : 'msg-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur *</label>
                    <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Rôle *</label>
                    <select name="role" class="form-control" required>
                        <option value="utilisateur">Utilisateur</option>
                        <option value="psychologue">Psychologue</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="location" class="form-control" placeholder="Localisation">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone_number" class="form-control" placeholder="Numéro de téléphone">
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" placeholder="Bio"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ajouter
                    </button>
                    <a href="users.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>