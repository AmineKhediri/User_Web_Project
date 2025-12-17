<?php
/**
 * Classe User - Entité représentant un utilisateur
 * Métier avancé : profil enrichi, sécurité, authentification multi-modes
 */
class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private ?string $password;
    private ?string $location;
    private ?string $phone_number;
    private ?string $bio;
    private string $role;
    private int $status;
    
    // Profil enrichi
    private ?string $profile_photo;
    private ?string $gender;
    private ?string $date_of_birth;
    private ?string $profession;
    private ?string $company;
    private ?string $nationality;
    private ?string $social_links; // JSON
    
    // Sécurité & Contrôle admin
    private int $is_blocked;
    private int $is_banned;
    private ?string $blocked_reason;
    private ?string $banned_reason;
    private int $is_locked;
    private ?string $locked_until;
    private int $failed_login_attempts;
    
    // Authentification avancée
    private ?string $password_reset_token;
    private ?string $password_reset_expires;
    private ?string $forgotten_password_code;
    private ?string $forgotten_password_method;
    private ?string $forgotten_password_expires;
    private ?string $google_id;
    private ?string $last_login;
    
    private ?string $created_at;
    private ?string $updated_at;

    public function __construct(string $username, string $email, string $password, ?string $location = null, ?string $phone_number = null, ?string $bio = null, string $role = 'utilisateur')
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->location = $location;
        $this->phone_number = $phone_number;
        $this->bio = $bio;
        $this->role = $role;
        $this->status = 1;
        
        // Profil enrichi - défauts
        $this->profile_photo = null;
        $this->gender = null;
        $this->date_of_birth = null;
        $this->profession = null;
        $this->company = null;
        $this->nationality = null;
        $this->social_links = null;
        
        // Sécurité - défauts
        $this->is_blocked = 0;
        $this->is_banned = 0;
        $this->blocked_reason = null;
        $this->banned_reason = null;
        $this->is_locked = 0;
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        
        // Authentification - défauts
        $this->password_reset_token = null;
        $this->password_reset_expires = null;
        $this->forgotten_password_code = null;
        $this->forgotten_password_method = null;
        $this->forgotten_password_expires = null;
        $this->google_id = null;
        $this->last_login = null;
        
        $this->id = null;
        $this->created_at = null;
        $this->updated_at = null;
    }

    // ===== GETTERS =====
    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): ?string { return $this->password; }
    public function getLocation(): ?string { return $this->location; }
    public function getPhoneNumber(): ?string { return $this->phone_number; }
    public function getBio(): ?string { return $this->bio; }
    public function getRole(): string { return $this->role; }
    public function getStatus(): int { return $this->status; }
    
    // Profil enrichi
    public function getProfilePhoto(): ?string { return $this->profile_photo; }
    public function getGender(): ?string { return $this->gender; }
    public function getDateOfBirth(): ?string { return $this->date_of_birth; }
    public function getProfession(): ?string { return $this->profession; }
    public function getCompany(): ?string { return $this->company; }
    public function getNationality(): ?string { return $this->nationality; }
    public function getSocialLinks(): ?string { return $this->social_links; }
    
    // Sécurité
    public function getIsBlocked(): int { return $this->is_blocked; }
    public function getIsBanned(): int { return $this->is_banned; }
    public function getBlockedReason(): ?string { return $this->blocked_reason; }
    public function getBannedReason(): ?string { return $this->banned_reason; }
    public function getIsLocked(): int { return $this->is_locked; }
    public function getLockedUntil(): ?string { return $this->locked_until; }
    public function getFailedLoginAttempts(): int { return $this->failed_login_attempts; }
    
    // Authentification
    public function getPasswordResetToken(): ?string { return $this->password_reset_token; }
    public function getPasswordResetExpires(): ?string { return $this->password_reset_expires; }
    public function getForgottenPasswordCode(): ?string { return $this->forgotten_password_code; }
    public function getForgottenPasswordMethod(): ?string { return $this->forgotten_password_method; }
    public function getForgottenPasswordExpires(): ?string { return $this->forgotten_password_expires; }
    public function getGoogleId(): ?string { return $this->google_id; }
    public function getLastLogin(): ?string { return $this->last_login; }
    
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // ===== SETTERS =====
    public function setId(int $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(?string $password): void { $this->password = $password; }
    public function setLocation(?string $location): void { $this->location = $location; }
    public function setPhoneNumber(?string $phone): void { $this->phone_number = $phone; }
    public function setBio(?string $bio): void { $this->bio = $bio; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setStatus(int $status): void { $this->status = $status; }
    
    // Profil enrichi
    public function setProfilePhoto(?string $photo): void { $this->profile_photo = $photo; }
    public function setGender(?string $gender): void { $this->gender = $gender; }
    public function setDateOfBirth(?string $dob): void { $this->date_of_birth = $dob; }
    public function setProfession(?string $profession): void { $this->profession = $profession; }
    public function setCompany(?string $company): void { $this->company = $company; }
    public function setNationality(?string $nationality): void { $this->nationality = $nationality; }
    public function setSocialLinks(?string $links): void { $this->social_links = $links; }
    
    // Sécurité
    public function setIsBlocked(int $blocked): void { $this->is_blocked = $blocked; }
    public function setIsBanned(int $banned): void { $this->is_banned = $banned; }
    public function setBlockedReason(?string $reason): void { $this->blocked_reason = $reason; }
    public function setBannedReason(?string $reason): void { $this->banned_reason = $reason; }
    public function setIsLocked(int $locked): void { $this->is_locked = $locked; }
    public function setLockedUntil(?string $until): void { $this->locked_until = $until; }
    public function setFailedLoginAttempts(int $attempts): void { $this->failed_login_attempts = $attempts; }
    
    // Authentification
    public function setPasswordResetToken(?string $token): void { $this->password_reset_token = $token; }
    public function setPasswordResetExpires(?string $expires): void { $this->password_reset_expires = $expires; }
    public function setForgottenPasswordCode(?string $code): void { $this->forgotten_password_code = $code; }
    public function setForgottenPasswordMethod(?string $method): void { $this->forgotten_password_method = $method; }
    public function setForgottenPasswordExpires(?string $expires): void { $this->forgotten_password_expires = $expires; }
    public function setGoogleId(?string $id): void { $this->google_id = $id; }
    public function setLastLogin(?string $login): void { $this->last_login = $login; }
    
    public function setCreatedAt(?string $ts): void { $this->created_at = $ts; }
    public function setUpdatedAt(?string $ts): void { $this->updated_at = $ts; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'location' => $this->location,
            'phone_number' => $this->phone_number,
            'bio' => $this->bio,
            'role' => $this->role,
            'status' => $this->status,
            'profile_photo' => $this->profile_photo,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'profession' => $this->profession,
            'company' => $this->company,
            'nationality' => $this->nationality,
            'social_links' => $this->social_links,
            'is_blocked' => $this->is_blocked,
            'is_banned' => $this->is_banned,
            'blocked_reason' => $this->blocked_reason,
            'banned_reason' => $this->banned_reason,
            'is_locked' => $this->is_locked,
            'locked_until' => $this->locked_until,
            'failed_login_attempts' => $this->failed_login_attempts,
            'password_reset_token' => $this->password_reset_token,
            'password_reset_expires' => $this->password_reset_expires,
            'forgotten_password_code' => $this->forgotten_password_code,
            'forgotten_password_method' => $this->forgotten_password_method,
            'forgotten_password_expires' => $this->forgotten_password_expires,
            'google_id' => $this->google_id,
            'last_login' => $this->last_login,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
