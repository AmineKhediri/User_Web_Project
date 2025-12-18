# SUPPORTINI - Plateforme de Gestion d'Utilisateurs (MVC)

## Vue d'ensemble
SUPPORTINI est une plateforme web moderne dédiée à la gestion des utilisateurs, développée selon une architecture **MVC stricte**, sécurisée et performante. Elle intègre l'authentification Google OAuth2, la double authentification (2FA TOTP), et une gestion de profil enrichie.

---

## 🏗 Architecture MVC & Contraintes Techniques

Le projet respecte scrupuleusement les principes suivants :
1.  **Modèle (Model)** : Contient uniquement les entités, attributs, Getters/Setters. (`Model/User.php`)
2.  **Vue (View)** : Contient uniquement l'affichage HTML/CSS. (`View/`)
3.  **Contrôleur (Controller)** : Contient uniquement la logique métier et les méthodes. (`Controller/userController.php`)
4.  **Base de Données** : **Une seule table** `users`. Pas de tables multiples.
5.  **Sécurité** : PDO avec requêtes préparées (Pas de MySQLi).
6.  **Minimalisme** : Nombre de fichiers réduit, pas de duplication.

### Structure des Fichiers
```
Web_Project_Utilisateurs/
├── config.php                 # Connexion Singleton PDO
├── index.php                  # Routeur principal
├── setup.php                  # Script d'installation automatique
├── Controller/
│   ├── userController.php     # Logique métier complète (Auth, CRUD, 2FA, Google)
│   └── NotificationService.php # Service d'envoi d'emails (Log & Mail)
├── Model/
│   └── User.php               # Entité User (Attributs + Getters/Setters)
├── View/
│   ├── FrontOffice/
│   │   ├── login.php          # Connexion (Email + Google)
│   │   ├── signup.php         # Inscription
│   │   ├── dashboard.php      # Espace membre & Profil
│   │   ├── enter_2fa.php      # Saisie code TOTP
│   │   └── forgot_password.php # Récupération mot de passe
│   └── BackOffice/
│       ├── users.php          # Gestion Admin (CRUD)
│       ├── add_user.php       # Formulaire Admin
│       └── edit_user.php      # Édition Admin
└── Lib/
    └── TOTP.php               # Librairie helper pour Google Authenticator
```

---

## ✨ Fonctionnalités Réalisées

### Authentification & Sécurité
- [x] **Login Email/Password** : Sécurisé via `password_verify` et protections anti-brute-force (lockout).
- [x] **Google OAuth2** : Connexion/Inscription en un clic via Google.
- [x] **Double Authentification (2FA)** : Intégration complète Google Authenticator (TOTP) avec QR Code.
- [x] **Mot de Passe Oublié** : Envoi de code de récupération (Email/SMS) avec logs serveur.

### Gestion Profil
- [x] **Profil Enrichi** : Photo, Bio, Localisation, Réseaux Sociaux.
- [x] **Upload Photo** : Gestion optimisée (Base de données LongText ou Fichier).
- [x] **Rôles** : Système Admin / Psychologue / Utilisateur.

### Administration (BackOffice)
- [x] **CRUD Complet** : Ajouter, Modifier, Supprimer, Bloquer, Bannir des utilisateurs.
- [x] **Logs d'Activité** : Historique des connexions et actions critiques.
- [x] **Recherche & Filtres** : Tri dynamique des utilisateurs.

---

## 🚀 Installation Rapide

1.  **Déposer les fichiers** :
    Mettre le dossier `Web_Project_Utilisateurs` dans `htdocs`.

2.  **Base de Données** :
    Accéder à `http://localhost/Web_Project_Utilisateurs/setup.php` pour créer automatiquement la base et la table.
    *Ou importer manuellement `database.sql`.*

3.  **Configuration Google (Optionnel)** :
    Modifier les clés `GOOGLE_CLIENT_ID` dans `config.php` si besoin.

---

## 👤 Compte Admin Par Défaut
*   **Email** : `admin@supportini.com`
*   **Mot de passe** : `admin`

---
*Projet réalisé pour le module Web Avancé - 2024*
