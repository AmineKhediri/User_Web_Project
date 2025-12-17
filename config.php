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
                    "mysql:host=localhost;dbname=supportini;charset=utf8mb4",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                echo "<!-- Connexion à la base de données 'supportini' réussie -->";
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
?>
