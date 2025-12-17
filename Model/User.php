<?php
/**
 * Classe User - Entité représentant un utilisateur
 * 
 * Cette classe encapsule les données d'un utilisateur avec des getters/setters typés.
 * Elle ne contient pas de logique métier, juste la gestion des propriétés.
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
    private ?string $created_at;
    private ?string $updated_at;

    /**
     * Constructeur User
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string|null $location
     * @param string|null $phone_number
     * @param string|null $bio
     * @param string $role
     */
    public function __construct(string $username, string $email, string $password, ?string $location = null, ?string $phone_number = null, ?string $bio = null, string $role = 'utilisateur')
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->location = $location;
        $this->phone_number = $phone_number;
        $this->bio = $bio;
        $this->role = $role;
        $this->status = 1; // default active
        $this->id = null;
        $this->created_at = null;
        $this->updated_at = null;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): ?string { return $this->password; }
    public function getLocation(): ?string { return $this->location; }
    public function getPhoneNumber(): ?string { return $this->phone_number; }
    public function getBio(): ?string { return $this->bio; }
    public function getRole(): string { return $this->role; }
    public function getStatus(): int { return $this->status; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(?string $password): void { $this->password = $password; }
    public function setLocation(?string $location): void { $this->location = $location; }
    public function setPhoneNumber(?string $phone): void { $this->phone_number = $phone; }
    public function setBio(?string $bio): void { $this->bio = $bio; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setStatus(int $status): void { $this->status = $status; }
    public function setCreatedAt(?string $ts): void { $this->created_at = $ts; }
    public function setUpdatedAt(?string $ts): void { $this->updated_at = $ts; }

    /**
     * Convertir l'objet en tableau associatif
     * Utile pour les opérations de base de données
     * 
     * @return array Tableau avec toutes les propriétés
     */
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
