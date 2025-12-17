<?php
// Vérifier si l'utilisateur est connecté
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: View/BackOffice/users.php");
    } else {
        header("Location: View/FrontOffice/dashboard.php");
    }
    exit;
}

// Vérifier si la base de données existe
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    $databases = $pdo->query("SHOW DATABASES LIKE 'supportini'")->fetchAll();
    $dbExists = !empty($databases);
} catch (Exception $e) {
    $dbExists = false;
}

if (!$dbExists) {
    header("Location: setup.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUPPORTINI - Plateforme de Gestion d'Utilisateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            color: #f5f5f5;
        }
        
        .navbar {
            background: rgba(18, 18, 18, 0.95);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border-bottom: 2px solid #d32f2f;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #d32f2f;
            font-size: 24px;
            font-weight: 700;
        }
        
        .navbar-brand img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .navbar-nav {
            display: flex;
            gap: 30px;
        }
        
        .navbar-nav a {
            color: #f5f5f5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .navbar-nav a:hover {
            color: #d32f2f;
        }
        
        .container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .hero-section h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: #d32f2f;
            line-height: 1.2;
        }
        
        .hero-section p {
            font-size: 18px;
            color: #aaaaaa;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .features {
            list-style: none;
            margin: 30px 0;
        }
        
        .features li {
            padding: 10px 0;
            color: #aaaaaa;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .features li i {
            color: #d32f2f;
            font-size: 20px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }
        
        .btn {
            padding: 15px 35px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .btn-primary {
            background: #d32f2f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #b71c1c;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(211, 47, 47, 0.3);
        }
        
        .btn-outline {
            border: 2px solid #d32f2f;
            color: #d32f2f;
            background: transparent;
        }
        
        .btn-outline:hover {
            background: #d32f2f;
            color: white;
        }
        
        .illustration {
            text-align: center;
            font-size: 100px;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                margin: 40px auto;
            }
            
            .hero-section h1 {
                font-size: 36px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-heartbeat"></i>
            SUPPORTINI
        </a>
        <div class="navbar-nav">
            <a href="View/FrontOffice/login.php">Connexion</a>
            <a href="View/FrontOffice/signup.php">Inscription</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="hero-section">
            <h1>Gestion d'Utilisateurs Moderne</h1>
            <p>SUPPORTINI est une plateforme web intuitive pour gérer vos utilisateurs, psychologues et administrateurs en un seul endroit.</p>
            
            <ul class="features">
                <li>
                    <i class="fas fa-lock"></i>
                    <span>Authentification sécurisée</span>
                </li>
                <li>
                    <i class="fas fa-users"></i>
                    <span>Gestion CRUD complète</span>
                </li>
                <li>
                    <i class="fas fa-roles"></i>
                    <span>3 rôles: Utilisateur, Psychologue, Admin</span>
                </li>
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Protection contre les injections SQL</span>
                </li>
                <li>
                    <i class="fas fa-mobile-alt"></i>
                    <span>Interface responsive et moderne</span>
                </li>
            </ul>
            
            <div class="cta-buttons">
                <a href="View/FrontOffice/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
                <a href="View/FrontOffice/signup.php" class="btn btn-outline">
                    <i class="fas fa-user-plus"></i> S'inscrire
                </a>
            </div>
        </div>
        
        <div class="illustration">
            👥
        </div>
    </div>
</body>
</html>