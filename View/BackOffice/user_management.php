<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/userController.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$controller = new userController();
$users = $controller->getAllUsers();
$message = '';
$error = '';

// Traiter les actions (bloquer/bannir)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = (int)$_POST['user_id'];
    $reason = trim($_POST['reason'] ?? '');

    if ($_POST['action'] === 'block') {
        if ($controller->blockUser($userId, $reason)) {
            $message = "✓ Utilisateur bloqué avec succès";
            $users = $controller->getAllUsers();
        } else {
            $error = "Erreur lors du blocage";
        }
    } elseif ($_POST['action'] === 'unblock') {
        if ($controller->unblockUser($userId)) {
            $message = "✓ Utilisateur débloqué avec succès";
            $users = $controller->getAllUsers();
        } else {
            $error = "Erreur lors du déblocage";
        }
    } elseif ($_POST['action'] === 'ban') {
        if ($controller->banUser($userId, $reason)) {
            $message = "✓ Utilisateur banni avec succès";
            $users = $controller->getAllUsers();
        } else {
            $error = "Erreur lors du bannissement";
        }
    } elseif ($_POST['action'] === 'unban') {
        if ($controller->unbanUser($userId)) {
            $message = "✓ Utilisateur débanni avec succès";
            $users = $controller->getAllUsers();
        } else {
            $error = "Erreur lors du débannissement";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - SUPPORTINI Admin</title>
    <link rel="stylesheet" href="backoffice.css">
    <style>
        .management-container {
            padding: 20px;
        }
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        .management-header h1 {
            margin: 0;
            font-size: 28px;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .users-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .users-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .users-table tbody tr:hover {
            background: #f9f9f9;
        }
        .user-name {
            font-weight: 600;
            color: #333;
        }
        .user-email {
            font-size: 13px;
            color: #777;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        .badge-psycho {
            background: #007bff;
            color: white;
        }
        .badge-user {
            background: #6c757d;
            color: white;
        }
        .badge-blocked {
            background: #ff9800;
            color: white;
        }
        .badge-banned {
            background: #dc3545;
            color: white;
        }
        .status-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-block {
            background: #ff9800;
            color: white;
        }
        .btn-block:hover {
            background: #e68900;
        }
        .btn-unblock {
            background: #28a745;
            color: white;
        }
        .btn-unblock:hover {
            background: #218838;
        }
        .btn-ban {
            background: #dc3545;
            color: white;
        }
        .btn-ban:hover {
            background: #c82333;
        }
        .btn-unban {
            background: #17a2b8;
            color: white;
        }
        .btn-unban:hover {
            background: #138496;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
        }
        .modal-content h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-confirm {
            background: #dc3545;
            color: white;
        }
        .btn-cancel {
            background: #e9ecef;
            color: #333;
        }
        .search-bar {
            margin-bottom: 20px;
            padding: 0 20px;
        }
        .search-bar input {
            width: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="management-container">
    <div class="management-header">
        <h1>👥 Gestion des utilisateurs</h1>
        <a href="backoffice.php" class="btn" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">← Retour</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 0 20px 20px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success" style="margin: 0 20px 20px;"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="🔍 Rechercher par nom ou email..." onkeyup="filterTable()">
    </div>

    <table class="users-table" id="usersTable">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Raison</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr data-searchtext="<?php echo strtolower($user['username'] . ' ' . $user['email']); ?>">
                <td>
                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                </td>
                <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="badge badge-admin">Admin</span>
                    <?php elseif ($user['role'] === 'psychologue'): ?>
                        <span class="badge badge-psycho">Psychologue</span>
                    <?php else: ?>
                        <span class="badge badge-user">Utilisateur</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="status-cell">
                        <?php if ($user['is_blocked']): ?>
                            <span class="badge badge-blocked">🚫 Bloqué</span>
                        <?php endif; ?>
                        <?php if ($user['is_banned']): ?>
                            <span class="badge badge-banned">🔴 Banni</span>
                        <?php endif; ?>
                        <?php if (!$user['is_blocked'] && !$user['is_banned']): ?>
                            <span style="color: #28a745;">✓ Actif</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <?php if ($user['blocked_reason']): ?>
                        <small style="color: #ff9800;">Bloqué: <?php echo htmlspecialchars(substr($user['blocked_reason'], 0, 30)); ?></small>
                    <?php endif; ?>
                    <?php if ($user['banned_reason']): ?>
                        <small style="color: #dc3545;">Banni: <?php echo htmlspecialchars(substr($user['banned_reason'], 0, 30)); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                </td>
                <td>
                    <div class="actions">
                        <?php if (!$user['is_blocked']): ?>
                            <button class="action-btn btn-block" onclick="openModal('block', <?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Bloquer</button>
                        <?php else: ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="unblock">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="action-btn btn-unblock" onclick="return confirm('Débloquer cet utilisateur?')">Débloquer</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if (!$user['is_banned']): ?>
                            <button class="action-btn btn-ban" onclick="openModal('ban', <?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Bannir</button>
                        <?php else: ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="unban">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="action-btn btn-unban" onclick="return confirm('Débannir cet utilisateur?')">Débannir</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- MODAL POUR BLOQUER/BANNIR -->
<div class="modal" id="actionModal">
    <div class="modal-content">
        <h2 id="modalTitle">Bloquer l'utilisateur</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" id="modalAction">
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div class="form-group">
                <label for="reason">Raison (optionnel):</label>
                <textarea id="reason" name="reason" placeholder="Expliquez pourquoi..."></textarea>
            </div>
            
            <div class="modal-buttons">
                <button type="submit" class="btn-confirm" id="confirmBtn">Confirmer</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(action, userId, username) {
        const modal = document.getElementById('actionModal');
        const title = document.getElementById('modalTitle');
        const actionInput = document.getElementById('modalAction');
        const confirmBtn = document.getElementById('confirmBtn');
        
        if (action === 'block') {
            title.textContent = `Bloquer ${username}`;
            confirmBtn.textContent = 'Bloquer';
            confirmBtn.style.background = '#ff9800';
        } else {
            title.textContent = `Bannir ${username}`;
            confirmBtn.textContent = 'Bannir';
            confirmBtn.style.background = '#dc3545';
        }
        
        actionInput.value = action;
        document.getElementById('modalUserId').value = userId;
        document.getElementById('reason').value = '';
        
        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('actionModal').classList.remove('active');
    }

    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('usersTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const searchText = rows[i].getAttribute('data-searchtext');
            rows[i].style.display = searchText.includes(filter) ? '' : 'none';
        }
    }

    // Fermer la modal en cliquant en dehors
    document.getElementById('actionModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
</body>
</html>
