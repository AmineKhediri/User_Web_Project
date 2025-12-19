<?php
/**
 * Script de test pour vérifier l'envoi du code de réinitialisation
 * Accédez à: http://localhost/Web_Project_Utilisateurs/test_email.php?action=test
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/userController.php';

$action = $_GET['action'] ?? '';
$email = $_GET['email'] ?? 'admin@supportini.com';
$method = $_GET['method'] ?? 'email';

if ($action === 'test') {
    $ctrl = new userController();
    
    echo "<h1>TEST: Génération de code de réinitialisation</h1>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Méthode:</strong> $method</p>";
    
    // Vérifier que l'utilisateur existe
    $user = $ctrl->getUserByEmail($email);
    if (!$user) {
        echo "<p style='color: red;'><strong>❌ Erreur:</strong> Utilisateur non trouvé</p>";
        echo "<p>Utilisateurs disponibles:</p>";
        $allUsers = $ctrl->getAllUsers();
        foreach ($allUsers as $u) {
            echo "- " . $u['email'] . "<br>";
        }
        exit;
    }
    
    echo "<p style='color: green;'><strong>✓ Utilisateur trouvé:</strong> {$user['username']}</p>";
    
    // Générer le code
    $code = $ctrl->generateForgottenPasswordCode($email, $method);
    
    if ($code) {
        echo "<p style='color: green;'><strong>✓ Code généré:</strong> <span style='font-weight: bold; font-size: 18px;'>$code</span></p>";
        echo "<p>Ce code a été enregistré dans la base de données et loggé dans error_log</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Erreur:</strong> Impossible de générer le code</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>À faire maintenant:</strong></p>";
    echo "<ol>";
    echo "<li>Vérifiez la boîte de réception de l'email (ou spam)</li>";
    echo "<li>Consultez le fichier error_log de PHP pour voir le code loggé</li>";
    echo "<li>Allez sur <a href='View/FrontOffice/forgot_password.php'>forgot_password.php</a> et testez le flux complet</li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<p><strong>Commandes utiles:</strong></p>";
    echo "<pre>";
    echo "# Voir les logs PHP (macOS XAMPP)\n";
    echo "tail -50 /Applications/XAMPP/xamppfiles/logs/php_error.log\n";
    echo "\n";
    echo "# Ou consulter directement\n";
    echo "cat /Applications/XAMPP/xamppfiles/logs/php_error.log | grep 'RESET CODE'\n";
    echo "</pre>";
} else {
    echo "<h1>Test d'Envoi de Code de Réinitialisation</h1>";
    echo "<p>Cliquez sur un lien pour tester:</p>";
    echo "<ul>";
    echo "<li><a href='test_email.php?action=test&email=admin@supportini.com&method=email'>Test Email (Admin)</a></li>";
    echo "<li><a href='test_email.php?action=test&email=user@supportini.com&method=email'>Test Email (User)</a></li>";
    echo "<li><a href='test_email.php?action=test&email=psy@supportini.com&method=sms'>Test SMS (Psychologue)</a></li>";
    echo "<li><a href='test_email.php?action=test&email=admin@supportini.com&method=whatsapp'>Test WhatsApp (Admin)</a></li>";
    echo "</ul>";
}
?>
