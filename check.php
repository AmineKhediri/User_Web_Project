<?php
/**
 * Fichier de vérification du projet
 * Vérifie que toutes les corrections ont été appliquées correctement
 * Accès : http://localhost/Web_Project_Utilisateurs/check.php
 */

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification Projet SUPPORTINI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .check-item { margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-left: 4px solid #ddd; border-radius: 4px; }
        .check-item.success { border-left-color: #4CAF50; background: #e8f5e9; }
        .check-item.error { border-left-color: #f44336; background: #ffebee; }
        .check-item.warning { border-left-color: #ff9800; background: #fff3e0; }
        .check-icon { font-weight: bold; margin-right: 10px; font-size: 18px; }
        .check-title { font-weight: bold; color: #333; margin-bottom: 5px; }
        .check-desc { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Vérification du Projet SUPPORTINI</h1>";

$checks = [];

// 1. Vérifier les fichiers
$requiredFiles = [
    'View/FrontOffice/signup.php',
    'View/FrontOffice/login.php',
    'View/FrontOffice/dashboard.php',
    'View/BackOffice/users.php',
    'View/BackOffice/psy_requests.php',
    'Controller/userController.php',
    'Model/User.php',
    'config.php',
    'database.sql',
    'migrate.php',
    'logo_supportini.jpg'
];

echo "<h2 style='margin-top: 30px; color: #333;'>📁 Fichiers</h2>";
foreach ($requiredFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $class = $exists ? 'success' : 'error';
    $icon = $exists ? '✅' : '❌';
    echo "<div class='check-item $class'>
        <span class='check-icon'>$icon</span>
        <div class='check-title'>$file</div>
        <div class='check-desc'>" . ($exists ? 'Fichier trouvé' : 'Fichier MANQUANT') . "</div>
    </div>";
}

// 2. Vérifier la base de données
echo "<h2 style='margin-top: 30px; color: #333;'>🗄️ Base de Données</h2>";
try {
    require_once __DIR__ . '/config.php';
    $pdo = config::getConnexion();
    
    // Vérifier colonne demande_psy
    $sql = "SHOW COLUMNS FROM users LIKE 'demande_psy'";
    $stmt = $pdo->query($sql);
    $hasDemandePsy = $stmt->fetch() ? true : false;
    
    $icon = $hasDemandePsy ? '✅' : '⚠️';
    $class = $hasDemandePsy ? 'success' : 'warning';
    echo "<div class='check-item $class'>
        <span class='check-icon'>$icon</span>
        <div class='check-title'>Colonne demande_psy</div>
        <div class='check-desc'>" . ($hasDemandePsy ? 'Colonne trouvée' : 'Colonne MANQUANTE - Exécuter migrate.php') . "</div>
    </div>";
    
    // Vérifier index
    $sql = "SHOW INDEX FROM users WHERE Key_name = 'idx_demande_psy'";
    $stmt = $pdo->query($sql);
    $hasIndex = $stmt->fetch() ? true : false;
    
    $icon = $hasIndex ? '✅' : '⚠️';
    $class = $hasIndex ? 'success' : 'warning';
    echo "<div class='check-item $class'>
        <span class='check-icon'>$icon</span>
        <div class='check-title'>Index demande_psy</div>
        <div class='check-desc'>" . ($hasIndex ? 'Index trouvé' : 'Index MANQUANT') . "</div>
    </div>";
    
    // Compter les utilisateurs
    $sql = "SELECT COUNT(*) as count FROM users";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch();
    echo "<div class='check-item success'>
        <span class='check-icon'>📊</span>
        <div class='check-title'>Nombre d'utilisateurs</div>
        <div class='check-desc'>" . $row['count'] . " utilisateur(s) en base</div>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='check-item error'>
        <span class='check-icon'>❌</span>
        <div class='check-title'>Connexion Base de Données</div>
        <div class='check-desc'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>
    </div>";
}

// 3. Vérifier le code PHP
echo "<h2 style='margin-top: 30px; color: #333;'>💻 Code PHP</h2>";

// Vérifier userController
$ctrl_content = file_get_contents(__DIR__ . '/Controller/userController.php');
$hasFunctions = [
    'getUserById' => strpos($ctrl_content, 'function getUserById') !== false,
    'getPsyRequests' => strpos($ctrl_content, 'function getPsyRequests') !== false,
    'approvePsyRequest' => strpos($ctrl_content, 'function approvePsyRequest') !== false,
    'rejectPsyRequest' => strpos($ctrl_content, 'function rejectPsyRequest') !== false,
];

foreach ($hasFunctions as $funcName => $exists) {
    $icon = $exists ? '✅' : '❌';
    $class = $exists ? 'success' : 'error';
    echo "<div class='check-item $class'>
        <span class='check-icon'>$icon</span>
        <div class='check-title'>Fonction : $funcName()</div>
        <div class='check-desc'>" . ($exists ? 'Implémentée' : 'MANQUANTE') . "</div>
    </div>";
}

// Vérifier signup.php
$signup_content = file_get_contents(__DIR__ . '/View/FrontOffice/signup.php');
$signupChecks = [
    'Gestion demande_psy' => strpos($signup_content, '$demande_psy') !== false,
    'Checkbox psychologue' => strpos($signup_content, 'demande_psy') !== false,
    'Chemin logo correct' => strpos($signup_content, '../../logo_supportini.jpg') !== false,
    'Passage au contrôleur' => strpos($signup_content, 'addUser($user, $demande_psy)') !== false,
];

foreach ($signupChecks as $checkName => $exists) {
    $icon = $exists ? '✅' : '❌';
    $class = $exists ? 'success' : 'error';
    echo "<div class='check-item $class'>
        <span class='check-icon'>$icon</span>
        <div class='check-title'>signup.php : $checkName</div>
        <div class='check-desc'>" . ($exists ? 'OK' : 'PROBLÈME') . "</div>
    </div>";
}

// 4. Résumé
echo "<h2 style='margin-top: 30px; color: #333;'>📋 Résumé</h2>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 4px; border-left: 4px solid #2196f3;'>
    <p style='margin-bottom: 10px;'>✅ <strong>Corrections appliquées :</strong></p>
    <ul style='margin-left: 20px; color: #333;'>
        <li>HTML malformé de signup.php corrigé</li>
        <li>Chemins des logos standardisés</li>
        <li>Fonctions manquantes ajoutées au Controller</li>
        <li>Support complet du système de demandes de psychologue</li>
        <li>Page de gestion des demandes créée (psy_requests.php)</li>
        <li>Script de migration pour mise à jour BD</li>
    </ul>
</div>";

echo "</div>
</body>
</html>";
?>
