<?php
/**
 * UserController - Métier avancé
 * Gestion complète : CRUD + Authentification + Sécurité + Profil enrichi
 */
require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../config.php';

class userController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    /**
     * === CRUD DE BASE ===
     */
    public function addUser(User $user, $demande_psy = 0) {
        try {
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

    public function updateUser(User $user) {
        try {
            $sql = "UPDATE users SET username = :username, email = :email, location = :location, 
                    phone_number = :phone_number, bio = :bio, role = :role, status = :status, updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'location' => $user->getLocation(),
                'phone_number' => $user->getPhoneNumber(),
                'bio' => $user->getBio(),
                'role' => $user->getRole(),
                'status' => $user->getStatus(),
                'id' => $user->getId()
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllUsers() {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getUserById($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * === AUTHENTIFICATION BASIQUE ===
     */
    public function validateLogin($email, $password) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Mise à jour du dernier login
                $this->updateLastLogin($user['id']);
                // Réinitialiser les tentatives échouées
                $this->resetFailedLoginAttempts($user['id']);
                return $user;
            }
            
            // Incrémenter les tentatives échouées
            if ($user) {
                $this->incrementFailedLoginAttempts($user['id']);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * === GESTION DES MOTS DE PASSE & AUTHENTIFICATION AVANCÉE ===
     */
    public function generatePasswordResetToken($email) {
        try {
            $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) return false;
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $sql = "UPDATE users SET password_reset_token = :token, password_reset_expires = :expires WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'token' => $token,
                'expires' => $expires,
                'id' => $user['id']
            ]) ? $token : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function generateForgottenPasswordCode($email, $method = 'email') {
        try {
            // Valider la méthode
            if (!in_array($method, ['email', 'sms', 'whatsapp'])) {
                $method = 'email';
            }
            
            $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) return false;
            
            // Générer un code OTP de 6 chiffres
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            $sql = "UPDATE users SET forgotten_password_code = :code, 
                    forgotten_password_method = :method, 
                    forgotten_password_expires = :expires WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                'code' => $code,
                'method' => $method,
                'expires' => $expires,
                'id' => $user['id']
            ]) ? $code : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function verifyForgottenPasswordCode($email, $code) {
        try {
            $sql = "SELECT id, forgotten_password_code, forgotten_password_expires FROM users 
                    WHERE email = :email AND forgotten_password_code = :code LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'code' => $code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) return false;
            
            // Vérifier l'expiration
            if (strtotime($user['forgotten_password_expires']) < time()) {
                return false; // Code expiré
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function resetPasswordWithCode($email, $code, $newPassword, $bypass = false) {
        try {
            // Vérifier le code d'abord
            if (!$bypass && !$this->verifyForgottenPasswordCode($email, $code)) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = :password, 
                    forgotten_password_code = NULL,
                    forgotten_password_expires = NULL,
                    forgotten_password_method = NULL,
                    updated_at = NOW() WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                'password' => $hashedPassword,
                'email' => $email
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            $user = $this->getUserById($userId);
            if (!$user) return false;
            
            // Vérifier l'ancien mot de passe
            if (!password_verify($oldPassword, $user['password'])) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * === AUTHENTIFICATION GOOGLE OAuth ===
     */
    public function handleGoogleAuth($googleId, $email, $name) {
        try {
            // Chercher l'utilisateur par google_id
            $sql = "SELECT * FROM users WHERE google_id = :google_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['google_id' => $googleId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Mise à jour du dernier login
                $this->updateLastLogin($user['id']);
                return $user;
            }
            
            // Chercher par email
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Lier le compte Google
                $sql = "UPDATE users SET google_id = :google_id, updated_at = NOW() WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['google_id' => $googleId, 'id' => $user['id']]);
                $this->updateLastLogin($user['id']);
                return $user;
            }
            
            // Créer un nouvel utilisateur
            $username = explode('@', $email)[0] . '_' . substr(uniqid(), -4);
            $tempPassword = bin2hex(random_bytes(16));
            
            $sql = "INSERT INTO users (username, email, password, role, google_id, status, created_at, updated_at) 
                    VALUES (:username, :email, :password, :role, :google_id, 1, NOW(), NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($tempPassword, PASSWORD_DEFAULT),
                'role' => 'utilisateur',
                'google_id' => $googleId
            ]);
            
            return $this->getUserByEmail($email);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * === SÉCURITÉ & TENTATIVES DE CONNEXION ===
     */
    public function incrementFailedLoginAttempts($userId) {
        try {
            $user = $this->getUserById($userId);
            $attempts = ($user['failed_login_attempts'] ?? 0) + 1;
            
            // Verrouiller après 5 tentatives
            if ($attempts >= 5) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $sql = "UPDATE users SET failed_login_attempts = :attempts, is_locked = 1, locked_until = :locked_until WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    'attempts' => $attempts,
                    'locked_until' => $lockUntil,
                    'id' => $userId
                ]);
            } else {
                $sql = "UPDATE users SET failed_login_attempts = :attempts WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    'attempts' => $attempts,
                    'id' => $userId
                ]);
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function resetFailedLoginAttempts($userId) {
        try {
            $sql = "UPDATE users SET failed_login_attempts = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function unlockAccount($userId) {
        try {
            $sql = "UPDATE users SET is_locked = 0, locked_until = NULL, failed_login_attempts = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function isAccountLocked($userId) {
        try {
            $user = $this->getUserById($userId);
            if (!$user || !$user['is_locked']) return false;
            
            // Vérifier si le verrouillage a expiré
            if (strtotime($user['locked_until']) < time()) {
                $this->unlockAccount($userId);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateLastLogin($userId) {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * === CONTRÔLE ADMIN : BLOQUER/BANNIR ===
     */
    public function blockUser($userId, $reason = '') {
        try {
            $sql = "UPDATE users SET is_blocked = 1, blocked_reason = :reason, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'reason' => $reason ?: 'Compte bloqué par un administrateur',
                'id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function unblockUser($userId) {
        try {
            $sql = "UPDATE users SET is_blocked = 0, blocked_reason = NULL, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function banUser($userId, $reason = '') {
        try {
            $sql = "UPDATE users SET is_banned = 1, banned_reason = :reason, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'reason' => $reason ?: 'Compte banni par un administrateur',
                'id' => $userId
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function unbanUser($userId) {
        try {
            $sql = "UPDATE users SET is_banned = 0, banned_reason = NULL, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * === PROFIL UTILISATEUR ENRICHI ===
     */
    public function updateProfile($userId, array $profileData) {
        try {
            // Tous les champs modifiables du profil
            $allowedFields = [
                'profile_photo', 'gender', 'date_of_birth', 'profession', 
                'company', 'nationality', 'social_links', 'location', 
                'phone_number', 'bio'
            ];
            $updates = [];
            $params = ['id' => $userId];
            
            foreach ($profileData as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "`$field` = :$field";
                    $params[$field] = $value;
                }
            }
            
            if (empty($updates)) return false;
            
            $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * === DEMANDES PSYCHOLOGUE (existant) ===
     */
    public function getPsyRequests() {
        try {
            // Vérifier si la colonne demande_psy existe
            $columns = $this->pdo->query("SHOW COLUMNS FROM users LIKE 'demande_psy'")->fetchAll();
            if (empty($columns)) return [];
            
            $sql = "SELECT id, username, email, demande_psy FROM users WHERE demande_psy = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function approvePsyRequest($userId) {
        try {
            $sql = "UPDATE users SET role = 'psychologue', demande_psy = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function rejectPsyRequest($userId) {
        try {
            $sql = "UPDATE users SET demande_psy = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * === UTILITAIRES ===
     */
    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserByUsername($username) {
        try {
            $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function emailExists($email) {
        return $this->getUserByEmail($email) !== null;
    }

    public function usernameExists($username) {
        return $this->getUserByUsername($username) !== null;
    }
}
?>
