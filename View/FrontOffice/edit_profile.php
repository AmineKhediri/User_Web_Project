<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/userController.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$controller = new userController();
$user = $controller->getUserById($_SESSION['user_id']);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileData = [
        'gender' => $_POST['gender'] ?? null,
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'profession' => $_POST['profession'] ?? null,
        'company' => $_POST['company'] ?? null,
        'nationality' => $_POST['nationality'] ?? null,
    ];

    // Gestion de la photo de profil
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['size'] > 0) {
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Vérification du type de fichier
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        
        // Vérifier l'extension et la taille
        if (empty($ext) || !in_array($ext, $allowedExts)) {
            $error = "Fichier invalide. Formats acceptés: JPG, PNG, GIF (Max 5 Mo)";
        } elseif ($file['size'] > $maxSize) {
            $error = "Fichier trop volumineux. Maximum 5 Mo autorisé.";
        } elseif ($file['error'] != UPLOAD_ERR_OK) {
            $error = "Erreur lors du téléchargement du fichier. Code: " . $file['error'];
        } else {
            // Lire le fichier et convertir en base64
            $fileContent = file_get_contents($file['tmp_name']);
            $mimeType = mime_content_type($file['tmp_name']);
            $base64Data = base64_encode($fileContent);
            $profileData['profile_photo'] = 'data:' . $mimeType . ';base64,' . $base64Data;
        }
    }

    // Liens réseaux sociaux (JSON)
    $socialLinks = [];
    if (!empty($_POST['linkedin'])) $socialLinks['linkedin'] = $_POST['linkedin'];
    if (!empty($_POST['twitter'])) $socialLinks['twitter'] = $_POST['twitter'];
    if (!empty($_POST['facebook'])) $socialLinks['facebook'] = $_POST['facebook'];
    if (!empty($_POST['instagram'])) $socialLinks['instagram'] = $_POST['instagram'];

    if (!empty($socialLinks)) {
        $profileData['social_links'] = json_encode($socialLinks);
    }

    if (empty($error) && $controller->updateProfile($_SESSION['user_id'], $profileData)) {
        $message = "✓ Profil mis à jour avec succès!";
        $user = $controller->getUserById($_SESSION['user_id']);
    } else {
        if (!$error) $error = "Erreur lors de la mise à jour du profil";
    }
}

// Décoder les liens sociaux JSON
$socialLinks = [];
if ($user['social_links']) {
    $socialLinks = json_decode($user['social_links'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil - SUPPORTINI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontoffice.css">
    <style>
        .profile-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .profile-header h1 {
            font-size: 28px;
            color: #333;
            margin: 0;
        }
        .profile-photo-section {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        .photo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: #f0f0f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #ddd;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-preview.empty {
            font-size: 50px;
        }
        .photo-controls {
            flex: 1;
        }
        .photo-controls label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
        }
        .photo-controls input[type="file"] {
            display: block;
            margin-bottom: 10px;
        }
        .photo-info {
            font-size: 12px;
            color: #999;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h3 {
            font-size: 16px;
            color: #333;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.1);
        }
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
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
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #e9ecef;
            color: #333;
            flex: 1;
            text-decoration: none;
            text-align: center;
        }
        .btn-secondary:hover {
            background: #dfe4e9;
        }
    </style>
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
                <span style="color: white; padding: 10px 20px;">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                </span>
                <a href="../../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

<div class="profile-container">
    <div class="profile-header">
        <h1>👤 Mon Profil</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- PHOTO DE PROFIL -->
        <div class="profile-photo-section">
            <div class="photo-preview <?php echo empty($user['profile_photo']) ? 'empty' : ''; ?>">
                <?php if ($user['profile_photo']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Photo de profil">
                <?php else: ?>
                    👤
                <?php endif; ?>
            </div>
            <div class="photo-controls">
                <label for="profile_photo">Photo de profil</label>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                <div class="photo-info">JPG, PNG, GIF (Max 5MB)</div>
            </div>
        </div>

        <!-- INFOS PERSONNELLES -->
        <div class="form-section">
            <h3>Informations personnelles</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Genre</label>
                    <select id="gender" name="gender">
                        <option value="">-- Sélectionner --</option>
                        <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Homme</option>
                        <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Femme</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date de naissance</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $user['date_of_birth'] ?: ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="nationality">Nationalité</label>
                    <input type="text" id="nationality" name="nationality" placeholder="ex: Française" value="<?php echo htmlspecialchars($user['nationality'] ?: ''); ?>">
                </div>
            </div>
        </div>

        <!-- INFOS PROFESSIONNELLES -->
        <div class="form-section">
            <h3>Informations professionnelles</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="profession">Profession</label>
                    <input type="text" id="profession" name="profession" placeholder="ex: Psychologue" value="<?php echo htmlspecialchars($user['profession'] ?: ''); ?>">
                </div>
                <div class="form-group">
                    <label for="company">Entreprise/Cabinet</label>
                    <input type="text" id="company" name="company" placeholder="ex: Cabinet Médical" value="<?php echo htmlspecialchars($user['company'] ?: ''); ?>">
                </div>
            </div>
        </div>

        <!-- RÉSEAUX SOCIAUX -->
        <div class="form-section">
            <h3>Réseaux sociaux</h3>
            <div class="form-row full">
                <div class="form-group">
                    <label for="linkedin">LinkedIn</label>
                    <input type="text" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/..." value="<?php echo htmlspecialchars($socialLinks['linkedin'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="twitter">Twitter/X</label>
                    <input type="text" id="twitter" name="twitter" placeholder="https://twitter.com/..." value="<?php echo htmlspecialchars($socialLinks['twitter'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="instagram">Instagram</label>
                    <input type="text" id="instagram" name="instagram" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($socialLinks['instagram'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row full">
                <div class="form-group">
                    <label for="facebook">Facebook</label>
                    <input type="text" id="facebook" name="facebook" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($socialLinks['facebook'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            <a href="dashboard.php" class="btn btn-secondary">← Retour</a>
        </div>
    </form>
</div>

    <footer style="background: rgba(0,0,0,0.9); color: white; text-align: center; padding: 20px; margin-top: 40px;">
        <p>&copy; 2025 SUPPORTINI. Tous droits réservés.</p>
    </footer>

<script>
    // Aperçu de la photo avant upload
    document.getElementById('profile_photo')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.querySelector('.photo-preview');
                preview.innerHTML = '<img src="' + event.target.result + '" alt="Aperçu">';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
