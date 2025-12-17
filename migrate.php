<?php
/**
 * Script de migration pour ajouter le support des demandes de psychologue
 * À exécuter une seule fois après la mise à jour du code
 * Accès : http://localhost/Web_Project_Utilisateurs/migrate.php
 */

require_once __DIR__ . '/config.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migration Base de Données</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 600px; margin: 0 auto; }
        .success { color: #4CAF50; padding: 20px; background: rgba(76, 175, 80, 0.1); border-left: 4px solid #4CAF50; border-radius: 4px; }
        .warning { color: #ff9800; padding: 20px; background: rgba(255, 152, 0, 0.1); border-left: 4px solid #ff9800; border-radius: 4px; }
        .error { color: #f44336; padding: 20px; background: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; border-radius: 4px; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>🔄 Migration Base de Données SUPPORTINI</h1>";

try {
    $pdo = config::getConnexion();
    
    // Vérifier si la colonne demande_psy existe
    $sql = "SHOW COLUMNS FROM users LIKE 'demande_psy'";
    $stmt = $pdo->query($sql);
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // Ajouter la colonne demande_psy
        $sql = "ALTER TABLE users ADD COLUMN demande_psy INT DEFAULT 0 AFTER role";
        $pdo->exec($sql);
        
        // Ajouter un index
        $sql = "ALTER TABLE users ADD INDEX idx_demande_psy (demande_psy)";
        $pdo->exec($sql);
        
        echo "<div class='success'>
            <h2>✅ Migration réussie !</h2>
            <p><strong>Colonne 'demande_psy' ajoutée avec succès !</strong></p>
            <p>Vous pouvez maintenant utiliser le système de demandes de psychologue.</p>
            <p style='margin-top: 20px;'><a href='View/BackOffice/psy_requests.php' style='color: #4CAF50; text-decoration: none; font-weight: bold;'>→ Aller à la page des demandes psychologue</a></p>
        </div>";
    } else {
        echo "<div class='warning'>
            <h2>ℹ️ Colonne déjà existante</h2>
            <p>La colonne 'demande_psy' existe déjà dans la base de données.</p>
            <p>Migration non nécessaire.</p>
            <p style='margin-top: 20px;'><a href='View/BackOffice/psy_requests.php' style='color: #ff9800; text-decoration: none; font-weight: bold;'>→ Aller à la page des demandes psychologue</a></p>
        </div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>
        <h2>❌ Erreur lors de la migration</h2>
        <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p style='margin-top: 20px; color: #999; font-size: 12px;'>Assurez-vous que :</p>
        <ul style='color: #999; font-size: 12px;'>
            <li>XAMPP/MySQL est démarré</li>
            <li>La base de données 'supportini' existe</li>
            <li>La table 'users' existe</li>
        </ul>
    </div>";
}

echo "</body>
</html>";
?>
