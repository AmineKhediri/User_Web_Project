<?php
require_once __DIR__ . '/../../Controller/userController.php';
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../FrontOffice/login.php");
    exit;
}

$message = "";
$message_type = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: users.php");
    exit;
}

$ctrl = new userController();
$user = $ctrl->getUserById($id);

if (!$user) {
    header("Location: users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $location = $_POST['location'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $role = $_POST['role'] ?? 'utilisateur';

    // Créer un objet User pour la mise à jour
    $userObj = new User($username, $email, null, $location, $phone_number, $bio, $role);
    $userObj->setId($id);

    $success = $ctrl->updateUser($userObj);

    if ($success) {
        $message = "Utilisateur mis à jour avec succès !";
        $message_type = "success";
        header("refresh:2;url=users.php");
    } else {
        $message = "Erreur lors de la mise à jour de l'utilisateur";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - SUPPORTINI Admin</title>
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
            <a href="../../logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-edit"></i> Modifier Utilisateur</h1>
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
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Rôle *</label>
                    <select name="role" class="form-control" required>
                        <option value="utilisateur" <?php echo $user['role'] == 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                        <option value="psychologue" <?php echo $user['role'] == 'psychologue' ? 'selected' : ''; ?>>Psychologue</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
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