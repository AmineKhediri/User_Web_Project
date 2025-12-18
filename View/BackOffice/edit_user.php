<?php
require_once __DIR__ . '/../../Controller/userController.php';
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../FrontOffice/login.php", true, 302);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    while (ob_get_level()) ob_end_clean();
    header("Location: users.php", true, 302);
    exit;
}

$ctrl = new userController();
$user = $ctrl->getUserById($id);

if (!$user) {
    while (ob_get_level()) ob_end_clean();
    header("Location: users.php", true, 302);
    exit;
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $location = $_POST['location'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $role = $_POST['role'] ?? 'utilisateur';
    
    // Extended fields
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
    $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $profession = $_POST['profession'] ?? null;
    $company = $_POST['company'] ?? null;
    $nationality = $_POST['nationality'] ?? null;
    
    // Social links
    $socialLinks = [];
    if (!empty($_POST['linkedin'])) $socialLinks['linkedin'] = $_POST['linkedin'];
    if (!empty($_POST['twitter'])) $socialLinks['twitter'] = $_POST['twitter'];
    if (!empty($_POST['facebook'])) $socialLinks['facebook'] = $_POST['facebook'];
    if (!empty($_POST['instagram'])) $socialLinks['instagram'] = $_POST['instagram'];
    
    $social_links_json = !empty($socialLinks) ? json_encode($socialLinks) : null;

    // Créer un objet User pour la mise à jour
    $userObj = new User($username, $email, "", $location, $phone_number, $bio, $role);
    $userObj->setId($id);
    
    // Set extended attributes
    $userObj->setGender($gender);
    $userObj->setDateOfBirth($dob);
    $userObj->setProfession($profession);
    $userObj->setCompany($company);
    $userObj->setNationality($nationality);
    $userObj->setSocialLinks($social_links_json);
    $userObj->setStatus($user['status']); // Preserve status

    // Gestion de la photo de profil - FILE UPLOAD (Fixed)
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['size'] > 0) {
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowedExts) && $file['size'] <= 5242880 && $file['error'] == UPLOAD_ERR_OK) {
             // Upload to FrontOffice/uploads so it's accessible everywhere
             $uploadDir = __DIR__ . '/../FrontOffice/uploads/';
             if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
             
             $fileName = 'profile_' . $id . '_' . time() . '.' . $ext;
             if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                 $userObj->setProfilePhoto('uploads/' . $fileName);
             } else {
                 $message = "Erreur upload.";
                 $message_type = "error";
             }
        } else {
            $message = "Fichier invalide ou trop volumineux.";
            $message_type = "error";
        }
    } else {
        $userObj->setProfilePhoto($user['profile_photo']);
    }

    $result = $ctrl->updateUser($userObj);

    if ($result === true) {
        $message = "Utilisateur mis à jour avec succès !";
        $message_type = "success";
        // Refresh data
        $user = $ctrl->getUserById($id);
    } else {
        $message = "Erreur : " . $result;
        $message_type = "error";
    }
}

// Decode social links
$socials = json_decode($user['social_links'] ?? '{}', true) ?: [];
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
    <!-- Inline styles copied from edit_profile.php to ensure identical look -->
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto; /* Centered in main-content */
            padding: 40px;
            background: #1e1e1e; /* Dark card match */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            color: #f5f5f5;
        }
        .profile-header-card {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid #333;
            padding-bottom: 20px;
        }
        .profile-photo-section {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #333;
        }
        .photo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: #333;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #444;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-controls label {
            display: block;
            margin-bottom: 10px;
            color: #f5f5f5;
            font-weight: 500;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h3 {
            font-size: 18px;
            color: #d32f2f; /* Primary Red */
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
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
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #bbb;
            font-size: 14px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
            border-radius: 4px;
            color: white;
            font-family: 'Montserrat', sans-serif;
        }
        .form-group input:focus {
            border-color: #d32f2f;
            outline: none;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        /* Admin specific section */
        .admin-section {
            background: rgba(211, 47, 47, 0.05);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid rgba(211, 47, 47, 0.2);
            margin-bottom: 30px;
        }
    </style>
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
            <h1 class="page-title"><i class="fas fa-edit"></i> Modifier Utilisateur</h1>
        </div>

        <?php if ($message): ?>
            <div id="msg" class="<?php echo $message_type === 'success' ? 'msg-success' : 'msg-error'; ?>" style="max-width: 800px; margin: 0 auto 20px auto;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-header-card">
                <h2>👤 Profil de <?php echo htmlspecialchars($user['username']); ?></h2>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                
                 <!-- ADMIN SETTINGS -->
                 <div class="admin-section">
                    <h3><i class="fas fa-shield-alt"></i> Paramètres Admin</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Rôle</label>
                            <select name="role">
                                <option value="utilisateur" <?php echo $user['role'] == 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                                <option value="psychologue" <?php echo $user['role'] == 'psychologue' ? 'selected' : ''; ?>>Psychologue</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email (Connexion)</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                     <div class="form-row">
                        <div class="form-group">
                            <label>Nom d'utilisateur</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- PHOTO -->
                <div class="profile-photo-section">
                    <!-- Correct path logic: if relative 'uploads/', prepend ../FrontOffice/ for display if needed, 
                         BUT edit_profile uses relative 'uploads/xxx'. 
                         In FrontOffice, 'uploads/' works because edit_profile is in FrontOffice.
                         In BackOffice, 'uploads/' is in ../FrontOffice/.
                    -->
                    <?php 
                        $photoPath = $user['profile_photo']; 
                        // Fix display path for BackOffice view
                        if ($photoPath && !str_starts_with($photoPath, 'http')) {
                            // If it's a relative path like 'uploads/foo.jpg', we need '../FrontOffice/uploads/foo.jpg'
                            $displayPath = '../FrontOffice/' . $photoPath;
                        } else {
                            $displayPath = $photoPath;
                        }
                    ?>
                    <div class="photo-preview <?php echo empty($photoPath) ? 'empty' : ''; ?>">
                        <?php if ($photoPath): ?>
                            <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Photo de profil">
                        <?php else: ?>
                            <span style="font-size: 40px;">👤</span>
                        <?php endif; ?>
                    </div>
                    <div class="photo-controls">
                        <label for="profile_photo">Photo de profil</label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                        <div style="font-size: 12px; color: #999; margin-top: 5px;">JPG, PNG, GIF (Max 5MB)</div>
                    </div>
                </div>

                <!-- INFOS PERSONNELLES -->
                <div class="form-section">
                    <h3>Informations personnelles</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Genre</label>
                            <select name="gender">
                                <option value="">-- Sélectionner --</option>
                                <option value="male" <?php echo ($user['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Homme</option>
                                <option value="female" <?php echo ($user['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Femme</option>
                                <option value="other" <?php echo ($user['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date de naissance</label>
                            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nationalité</label>
                            <input type="text" name="nationality" value="<?php echo htmlspecialchars($user['nationality'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Localisation</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                        </div>
                    </div>
                     <div class="form-row full">
                         <div class="form-group">
                            <label>Bio</label>
                            <textarea name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- INFOS PROFESSIONNELLES -->
                <div class="form-section">
                    <h3>Informations professionnelles</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Profession</label>
                            <input type="text" name="profession" value="<?php echo htmlspecialchars($user['profession'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Entreprise</label>
                            <input type="text" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                        </div>
                    </div>
                     <div class="form-row full">
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- RÉSEAUX SOCIAUX -->
                <div class="form-section">
                    <h3>Réseaux sociaux</h3>
                    <div class="form-row full">
                        <div class="form-group">
                            <label>LinkedIn</label>
                            <input type="text" name="linkedin" placeholder="https://linkedin.com/..." value="<?php echo htmlspecialchars($socials['linkedin'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Twitter/X</label>
                            <input type="text" name="twitter" placeholder="https://twitter.com/..." value="<?php echo htmlspecialchars($socials['twitter'] ?? ''); ?>">
                        </div>
                         <div class="form-group">
                            <label>Instagram</label>
                            <input type="text" name="instagram" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($socials['instagram'] ?? ''); ?>">
                        </div>
                    </div>
                     <div class="form-row full">
                        <div class="form-group">
                            <label>Facebook</label>
                            <input type="text" name="facebook" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($socials['facebook'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="users.php" class="btn btn-outline" style="flex: 1; text-align: center;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </main>
    <script>
        // Photo preview script matched from edit_profile.php
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
<?php ob_end_flush(); ?>