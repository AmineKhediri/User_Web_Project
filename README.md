# SUPPORTINI - Plateforme de Gestion d'Utilisateurs

## Vue d'ensemble
SUPPORTINI est une plateforme web moderne et sécurisée dédiée à la gestion d'utilisateurs. Elle offre une interface intuitive avec authentification, gestion de profils et fonctionnalités d'administration.

## Caractéristiques principales

✅ **Authentification sécurisée**
- Inscription d'utilisateurs avec validation des données
- Connexion sécurisée avec hachage de mots de passe
- Gestion de sessions utilisateur

✅ **Gestion de profils**
- Création et modification de profils utilisateur
- Stockage d'informations complètes (email, localisation, téléphone, bio)
- Tableau de bord utilisateur personnalisé

✅ **Panneau d'administration**
- Gestion complète des utilisateurs (CRUD)
- Attribution de rôles (Utilisateur, Psychologue, Admin)
- Interface intuitive avec sidebar de navigation
- Tables de gestion avec actions (modifier, supprimer)

✅ **Design moderne**
- Interface responsive avec CSS personnalisé
- Thème sombre professionnel
- Navigation fluide et intuitive
- Logo et branding SUPPORTINI

## Architecture

```
Web_Project_Utilisateurs/
├── config.php                 # Configuration base de données
├── index.php                  # Point d'entrée
├── logout.php                 # Gestion déconnexion
├── Controller/
│   └── userController.php    # Contrôleur CRUD utilisateurs
├── Model/
│   └── User.php              # Modèle utilisateur
└── View/
    ├── FrontOffice/
    │   ├── index.html        # Page d'accueil
    │   ├── login.php         # Page connexion
    │   ├── signup.php        # Page inscription
    │   ├── dashboard.php     # Tableau de bord utilisateur
    │   ├── frontoffice.css   # Styles front
    │   └── logout.php        # Lien déconnexion
    └── BackOffice/
        ├── users.php         # Liste des utilisateurs (admin)
        ├── add_user.php      # Ajouter utilisateur (admin)
        ├── edit_user.php     # Modifier utilisateur (admin)
        └── backoffice.css    # Styles admin
```

## Installation et Configuration

### Prérequis
- XAMPP (ou serveur local Apache + PHP 7.4+)
- MySQL 5.7+

### Étapes d'installation

1. **Cloner/Télécharger le projet**
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/
```

2. **Créer la base de données**

Ouvrir phpMyAdmin et exécuter ce SQL:

```sql
CREATE DATABASE IF NOT EXISTS `supportini`;
USE `supportini`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `location` VARCHAR(100),
    `phone_number` VARCHAR(20),
    `bio` TEXT,
    `role` ENUM('utilisateur', 'psychologue', 'admin') DEFAULT 'utilisateur',
    `status` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Créer un utilisateur admin par défaut
INSERT INTO `users` (username, email, password, role, status) VALUES 
('admin', 'admin@supportini.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyec6HzVVqk5ZhqYH8VjBFu7kzKVbWUCRK', 'admin', 1);
```

3. **Configurer la base de données**
   - Modifier `config.php` si nécessaire (utilisateur/mot de passe MySQL)
   - Par défaut: root / (sans mot de passe)

4. **Accéder à l'application**
```
http://localhost/Web_Project_Utilisateurs/View/FrontOffice/index.html
```

## Utilisation

### Pour les utilisateurs normaux
1. Accédez à la page d'accueil
2. Cliquez sur **S'inscrire** pour créer un compte
3. Complétez le formulaire (username, email, mot de passe, infos optionnelles)
4. Une fois inscrit, connectez-vous via le formulaire de connexion
5. Accédez à votre **Tableau de Bord** pour voir vos informations

### Pour les administrateurs
1. Connectez-vous avec un compte admin
2. Vous serez redirigé vers le **Panneau d'Administration**
3. Gérez les utilisateurs:
   - **Voir tous les utilisateurs**: liste complète dans une table
   - **Ajouter un utilisateur**: formulaire avec attribution de rôle
   - **Modifier un utilisateur**: éditer les informations
   - **Supprimer un utilisateur**: suppression définitive

## Les 3 rôles disponibles

- **Utilisateur**: Accès basique au tableau de bord
- **Psychologue**: Rôle intermédiaire pour les professionnels
- **Admin**: Accès complet au panneau d'administration

## Fonctionnalités de sécurité

🔐 Mots de passe: Hachés avec password_hash() (PHP)
🔐 Sessions: Gestion sécurisée des sessions PHP
🔐 Validation: Validation des emails et données
🔐 Prepared Statements: Protection contre les injections SQL (PDO)
🔐 Authentification: Vérification des credentials avant redirection

## Technologie

- Backend: PHP 7.4+
- Base de données: MySQL 5.7+
- Frontend: HTML5, CSS3 (grid responsive), JavaScript
- Design: Thème sombre moderne avec variables CSS

## Template CSS

Le projet inclut deux templates CSS personnalisés:

**frontoffice.css**: Interface utilisateur avec header, layout principal et responsive design

**backoffice.css**: Panneau admin avec sidebar fixe (260px) et composants d'administration

Tous deux utilisent des variables CSS pour les couleurs:
```css
--primary-red: #d32f2f
--dark-bg: #121212
--card-bg: #1e1e1e
--text-light: #f5f5f5
```

## URL d'accès directes

Accueil: http://localhost/Web_Project_Utilisateurs/View/FrontOffice/index.html
Connexion: http://localhost/Web_Project_Utilisateurs/View/FrontOffice/login.php
Inscription: http://localhost/Web_Project_Utilisateurs/View/FrontOffice/signup.php
Tableau de bord: http://localhost/Web_Project_Utilisateurs/View/FrontOffice/dashboard.php
Admin - Utilisateurs: http://localhost/Web_Project_Utilisateurs/View/BackOffice/users.php
Admin - Ajouter user: http://localhost/Web_Project_Utilisateurs/View/BackOffice/add_user.php
Déconnexion: http://localhost/Web_Project_Utilisateurs/logout.php

## Compte de test

Email: admin@supportini.com
Password: admin
Rôle: Admin

(Si vous avez exécuté le SQL de création)

## Logo

Le logo SUPPORTINI doit être placé à:
`/Applications/XAMPP/xamppfiles/htdocs/Web_Project_Utilisateurs/logo_supportini.jpg`

(Déjà présent dans le projet)

## Dépannage

### "Erreur de connexion DB"
- Vérifiez que MySQL est en cours d'exécution
- Vérifiez les credentials dans `config.php`
- Assurez-vous que la base de données `supportini` existe

### "Page non trouvée"
- Assurez-vous que XAMPP est en cours d'exécution
- Vérifiez le chemin de l'URL
- Les fichiers doivent être dans `/Applications/XAMPP/xamppfiles/htdocs/`

### "Erreur de session"
- Vérifiez que les sessions PHP sont activées
- Supprimez les cookies du navigateur si problème persiste

## Améliorations futures

- [ ] Intégration OAuth (Google, Facebook)
- [ ] Vérification d'email
- [ ] Réinitialisation de mot de passe
- [ ] Système de notifications
- [ ] API RESTful
- [ ] Tests automatisés

## Licence

Propriétaire - SUPPORTINI 2024

## Support

Pour toute question ou problème, veuillez vérifier la configuration et les logs du serveur.

---

Dernière mise à jour: Décembre 2024
Version: 1.0# WEB_Final
