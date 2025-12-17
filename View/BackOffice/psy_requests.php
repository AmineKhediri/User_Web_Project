<?php
require_once __DIR__ . '/../../Controller/userController.php';
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../FrontOffice/login.php");
    exit;
}

$ctrl = new userController();
$psyRequests = $ctrl->getPsyRequests();

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $id = $_POST['id'];
        $result = $ctrl->approvePsyRequest($id);
        $message = "Demande approuvée ! L'utilisateur est maintenant psychologue.";
        $message_type = "success";
    } elseif (isset($_POST['reject'])) {
        $id = $_POST['id'];
        $result = $ctrl->rejectPsyRequest($id);
        $message = "Demande rejetée.";
        $message_type = "info";
    }
    // Recharger les demandes
    $psyRequests = $ctrl->getPsyRequests();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes Psychologue - SUPPORTINI Admin</title>
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
            <a href="psy_requests.php" class="sidebar-link active">
                <i class="fas fa-user-check"></i>
                <span>Demandes Psychologue</span>
            </a>
            <a href="../../logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-user-check"></i> Demandes Psychologue</h1>
            <p style="color: var(--text-muted); margin-top: 5px;">Gérez les demandes des utilisateurs souhaitant devenir psychologue</p>
        </div>

        <?php if ($message): ?>
            <div style="margin-bottom: 20px; padding: 12px; border-radius: 4px; background: <?php echo $message_type === 'success' ? 'rgba(76, 175, 80, 0.1)' : 'rgba(33, 150, 243, 0.1)'; ?>; border-left: 4px solid <?php echo $message_type === 'success' ? '#4caf50' : '#2196f3'; ?>; color: <?php echo $message_type === 'success' ? '#4caf50' : '#2196f3'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if (empty($psyRequests)): ?>
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <p style="font-size: 16px; margin-bottom: 10px;">Aucune demande en attente</p>
                    <p style="font-size: 14px;">Les demandes des utilisateurs apparaîtront ici<br><small style="color: #999; margin-top: 10px; display: block;">💡 Astuce : Les utilisateurs doivent cocher "Je suis un psychologue/praticien" à l'inscription</small></p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Localisation</th>
                            <th>Bio</th>
                            <th>Demande Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($psyRequests as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo !empty($user['location']) ? htmlspecialchars($user['location']) : 'Non défini'; ?></td>
                            <td><?php echo !empty($user['bio']) ? htmlspecialchars(substr($user['bio'], 0, 50)) . (strlen($user['bio']) > 50 ? '...' : '') : 'Non défini'; ?></td>
                            <td><?php echo !empty($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'Non défini'; ?></td>
                            <td>
                                <div class="action-buttons" style="gap: 8px;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <button type="submit" name="approve" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <button type="submit" name="reject" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
