<?php
require_once __DIR__ . '/../../Controller/userController.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../FrontOffice/login.php");
    exit;
}

$ctrl = new userController();
$users = $ctrl->getAllUsers();

// Tri par rôle (admin, psychologue, utilisateur) puis par ID
if (!empty($users)) {
    usort($users, function($a, $b) {
        $roleOrder = ['admin' => 0, 'psychologue' => 1, 'utilisateur' => 2];
        $roleA = $roleOrder[$a['role']] ?? 3;
        $roleB = $roleOrder[$b['role']] ?? 3;
        if ($roleA !== $roleB) {
            return $roleA - $roleB;
        }
        return $a['id'] - $b['id'];
    });
}

$message = "";
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $res = $ctrl->deleteUser($id);
        // Simple redirect prevents seeing the message, but at least action is done.
        // Ideally we should pass message via session or GET
        header("Location: users.php");
        exit;
    }
    if (isset($_POST['approve_psy'])) {
        $id = $_POST['id'];
        $ctrl->approvePsyRequest($id);
        $message = "Demande approuvée avec succès";
        header("Location: users.php");
        exit;
    }
    if (isset($_POST['reject_psy'])) {
        $id = $_POST['id'];
        $ctrl->rejectPsyRequest($id);
        $message = "Demande rejetée";
        header("Location: users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - SUPPORTINI Admin</title>
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
            <a href="users.php" class="sidebar-link active">
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
            <h1 class="page-title"><i class="fas fa-users"></i> Gestion des Utilisateurs</h1>
            <a href="add_user.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter Utilisateur
            </a>
        </div>

        <?php if ($message): ?>
            <div class="msg-success" style="margin-bottom: 20px; padding: 12px; border-radius: 4px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Localisation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                <?php 
                                $roles = [
                                    'utilisateur' => 'Utilisateur',
                                    'psychologue' => 'Psychologue',
                                    'admin' => 'Admin'
                                ];
                                echo $roles[$user['role']] ?? htmlspecialchars($user['role']);
                                ?>
                            </span>
                        </td>
                        <td><?php echo !empty($user['location']) ? htmlspecialchars($user['location']) : 'Non défini'; ?></td>
                        <td>
                            <div class="action-buttons" style="display: flex; flex-direction: column; gap: 5px;">
                                <?php if (!empty($user['demande_psy']) && $user['demande_psy'] == 1): ?>
                                    <form method="POST" style="display: flex; flex-direction: column; gap: 5px; width: 100%;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        
                                        <div style="font-weight: bold; color: #555; font-size: 0.85em; margin-bottom: 2px;">Demande role psy:</div>
                                        
                                        <button type="submit" name="approve_psy" class="btn btn-success btn-sm" style="background-color: #28a745; border-color: #28a745; color: white; width: 100%; text-align: left;">
                                            <i class="fas fa-check"></i> Approuver role psy
                                        </button>
                                        <button type="submit" name="reject_psy" class="btn btn-warning btn-sm" style="background-color: #ffc107; border-color: #ffc107; color: black; width: 100%; text-align: left;">
                                            <i class="fas fa-times"></i> Refuser role psy
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: left;">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <form method="POST" style="width: 100%;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');" style="width: 100%; text-align: left;">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>