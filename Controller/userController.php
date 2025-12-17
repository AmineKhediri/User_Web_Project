<?php
require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../config.php';

class userController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function addUser(User $user, $demande_psy = 0) {
        try {
            // Essayer d'abord avec demande_psy
            $sql = "INSERT INTO users (username, email, password, location, phone_number, bio, role, demande_psy, status, created_at, updated_at) 
                    VALUES (:username, :email, :password, :location, :phone_number, :bio, :role, :demande_psy, :status, NOW(), NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => password_hash($user->getPassword(), PASSWORD_DEFAULT),
                'location' => $user->getLocation(),
                'phone_number' => $user->getPhoneNumber(),
                'bio' => $user->getBio(),
                'role' => $user->getRole(),
                'demande_psy' => $demande_psy,
                'status' => $user->getStatus()
            ]);
            return "User added successfully";
        } catch (Exception $e) {
            // Si la colonne demande_psy n'existe pas, insérer sans elle
            if (strpos($e->getMessage(), '1054') !== false && strpos($e->getMessage(), 'demande_psy') !== false) {
                try {
                    $sql = "INSERT INTO users (username, email, password, location, phone_number, bio, role, status, created_at, updated_at) 
                            VALUES (:username, :email, :password, :location, :phone_number, :bio, :role, :status, NOW(), NOW())";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        'username' => $user->getUsername(),
                        'email' => $user->getEmail(),
                        'password' => password_hash($user->getPassword(), PASSWORD_DEFAULT),
                        'location' => $user->getLocation(),
                        'phone_number' => $user->getPhoneNumber(),
                        'bio' => $user->getBio(),
                        'role' => $user->getRole(),
                        'status' => $user->getStatus()
                    ]);
                    return "User added successfully";
                } catch (Exception $e2) {
                    return "Error: " . $e2->getMessage();
                }
            }
            return "Error: " . $e->getMessage();
        }
    }

    public function updateUser(User $user, $id) {
        try {
            $sql = "UPDATE users SET username = :username, email = :email, location = :location, phone_number = :phone_number, bio = :bio, role = :role, updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'location' => $user->getLocation(),
                'phone_number' => $user->getPhoneNumber(),
                'bio' => $user->getBio(),
                'role' => $user->getRole(),
                'id' => $id
            ]);
            return "User updated successfully";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return "User deleted successfully";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function getAllUsers() {
        try {
            $sql = "SELECT * FROM users";
            $stmt = $this->pdo->query($sql);
            $users = [];
            while ($row = $stmt->fetch()) {
                $user = new User($row['username'], $row['email'], $row['password'], $row['location'], $row['phone_number'], $row['bio'], $row['role']);
                $user->setId($row['id']);
                $user->setStatus($row['status']);
                $user->setCreatedAt($row['created_at']);
                $user->setUpdatedAt($row['updated_at']);
                $users[] = $user;
            }
            return $users;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function getUserById($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if ($row) {
                $user = new User($row['username'], $row['email'], $row['password'], $row['location'], $row['phone_number'], $row['bio'], $row['role']);
                $user->setId($row['id']);
                $user->setStatus($row['status']);
                $user->setCreatedAt($row['created_at']);
                $user->setUpdatedAt($row['updated_at']);
                return $user;
            }
            return null;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function getPsyRequests() {
        try {
            // Vérifier si la colonne demande_psy existe
            $sql = "SHOW COLUMNS FROM users LIKE 'demande_psy'";
            $stmt = $this->pdo->query($sql);
            $columnExists = $stmt->fetch() ? true : false;
            
            if (!$columnExists) {
                // Retourner un tableau vide si la colonne n'existe pas
                return [];
            }
            
            $sql = "SELECT * FROM users WHERE demande_psy = 1 AND role = 'utilisateur' ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);
            $users = [];
            while ($row = $stmt->fetch()) {
                $user = new User($row['username'], $row['email'], $row['password'], $row['location'], $row['phone_number'], $row['bio'], $row['role']);
                $user->setId($row['id']);
                $user->setStatus($row['status']);
                $user->setCreatedAt($row['created_at']);
                $user->setUpdatedAt($row['updated_at']);
                $users[] = $user;
            }
            return $users;
        } catch (Exception $e) {
            return [];
        }
    }

    public function approvePsyRequest($id) {
        try {
            $sql = "UPDATE users SET role = 'psychologue', demande_psy = 0, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return "Request approved successfully";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function rejectPsyRequest($id) {
        try {
            $sql = "UPDATE users SET demande_psy = 0, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return "Request rejected successfully";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
?>