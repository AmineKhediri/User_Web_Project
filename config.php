<?php
/**
 * Classe config - Configuration de la base de données
 */
class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (self::$pdo == null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=127.0.0.1;dbname=supportini;charset=utf8mb4",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                // echo "<!-- Connexion à la base de données 'supportini' réussie -->";
            } catch (PDOException $e) {
                // Si la base de données n'existe pas, rediriger vers le setup
                if (strpos($e->getMessage(), '1049') !== false) {
                    header('Location: /Web_Project_Utilisateurs/setup.php');
                    exit;
                }
                die("Erreur de connexion DB: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// GOOGLE OAUTH CONFIGURATION
define('GOOGLE_CLIENT_ID', '149823985594-mtugna9hirf4ak4ommsgio3i0jm07910.apps.googleusercontent.com'); 
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/Web_Project_Utilisateurs/View/FrontOffice/login.php');