<?php
/**
 * Script d'Installation SUPPORTINI
 * Ce script crée automatiquement la base de données et la table users
 */

$host = 'localhost';
$user = 'root';
$password = '';

// Connexion sans sélectionner de base de données
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `supportini` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Sélectionner la base de données
    $pdo->exec("USE `supportini`");
    
    // Créer la table users
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `location` VARCHAR(100),
        `phone_number` VARCHAR(20),
        `bio` TEXT,
        `role` ENUM('utilisateur', 'psychologue', 'admin') DEFAULT 'utilisateur',
        `status` INT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role),
        INDEX idx_created_at (created_at)
    )";
    
    $pdo->exec($sql);
    
    // Vérifier si la table est vide
    $result = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
    $userCount = $result['count'];
    
    // Si vide, ajouter les comptes de test
    if ($userCount == 0) {
        $pdo->exec("INSERT INTO `users` (username, email, password, role, status) VALUES 
            ('admin', 'admin@supportini.com', '\$2y\$12\$OgRHHLtThjiuo4ypIP4Ms.77Ms2ZKO5p3rDo2rEfyCvNDfhsSzauy', 'admin', 1)");
        
        $pdo->exec("INSERT INTO `users` (username, email, password, location, phone_number, bio, role, status) VALUES 
            ('johndoe', 'user@supportini.com', '\$2y\$12\$9fTdvBrCfuo2bPQ5dzOtjeOk0FDDPxQP/Sdn3Sp4Hku5wNWhuGjb6', 'Paris', '0123456789', 'Utilisateur de la plateforme', 'utilisateur', 1)");
        
        $pdo->exec("INSERT INTO `users` (username, email, password, location, phone_number, bio, role, status) VALUES 
            ('psychologist', 'psy@supportini.com', '\$2y\$12\$URoP70zdXa50eNeYewY0S.D9yYpo8zWoget.cHjM.o4xlEcrzc2Om', 'Lyon', '0987654321', 'Psychologue professionnel', 'psychologue', 1)");
    }
    
    $success = true;
    
} catch (PDOException $e) {
    $error = "Erreur: " . $e->getMessage();
    $success = false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation SUPPORTINI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #121212 0%, #1e1e1e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid #333;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section h1 {
            color: #d32f2f;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .logo-section p {
            color: #aaaaaa;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .content {
            margin-bottom: 30px;
        }
        
        .status-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .success-message {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #81c784;
            font-size: 14px;
        }
        
        .error-message {
            background: rgba(211, 47, 47, 0.1);
            border: 1px solid #d32f2f;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #ef5350;
            font-size: 14px;
        }
        
        .info-box {
            background: rgba(33, 150, 243, 0.05);
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            color: #64B5F6;
            font-size: 13px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-box p {
            color: #aaaaaa;
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .info-box strong {
            color: #f5f5f5;
        }
        
        .account-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            color: #aaaaaa;
        }
        
        .account-table th {
            background: #121212;
            color: #d32f2f;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #333;
        }
        
        .account-table td {
            padding: 10px;
            border-bottom: 1px solid #333;
        }
        
        .account-table td strong {
            color: #f5f5f5;
        }
        
        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: #d32f2f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
        }
        
        .btn-secondary {
            background: #333;
            color: #f5f5f5;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            <h1>SUPPORTINI</h1>
            <p>Installation</p>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="status-icon">✅</div>
                <div class="success-message">
                    <strong>Installation réussie!</strong><br>
                    La base de données 'supportini' a été créée avec succès et est prête à l'emploi.
                </div>
                
                <div class="info-box">
                    <h3>📝 Comptes de Test Disponibles</h3>
                    <table class="account-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Mot de passe</th>
                                <th>Rôle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>admin@supportini.com</strong></td>
                                <td>admin123</td>
                                <td>Admin</td>
                            </tr>
                            <tr>
                                <td><strong>user@supportini.com</strong></td>
                                <td>user123</td>
                                <td>Utilisateur</td>
                            </tr>
                            <tr>
                                <td><strong>psy@supportini.com</strong></td>
                                <td>psy123</td>
                                <td>Psychologue</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="info-box">
                    <h3>🚀 Prochaines Étapes</h3>
                    <p>1️⃣ Cliquez sur "Accéder à l'Application"</p>
                    <p>2️⃣ Connectez-vous avec un compte de test</p>
                    <p>3️⃣ Explorez les fonctionnalités (Admin peut gérer les utilisateurs)</p>
                </div>
                
            <?php else: ?>
                <div class="status-icon">❌</div>
                <div class="error-message">
                    <strong>Erreur lors de l'installation:</strong><br>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                
                <div class="info-box">
                    <h3>⚙️ Vérifications</h3>
                    <p>✓ XAMPP/MySQL est en cours d'exécution</p>
                    <p>✓ Identifiants: root / (sans mot de passe)</p>
                    <p>✓ Vérifiez les erreurs ci-dessus</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="buttons">
            <?php if ($success): ?>
                <a href="View/FrontOffice/index.html" class="btn btn-primary">Accéder à l'Application</a>
                <a href="setup.php" class="btn btn-secondary">Réinstaller</a>
            <?php else: ?>
                <a href="setup.php" class="btn btn-primary">Réessayer</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Retour</a>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>SUPPORTINI © 2024 | Plateforme de Gestion d'Utilisateurs</p>
        </div>
    </div>
</body>
</html>
