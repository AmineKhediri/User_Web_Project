-- ========================================
-- Création de la Base de Données SUPPORTINI
-- ========================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS `supportini`;
USE `supportini`;

-- ========================================
-- Table: users
-- Description: Stockage des utilisateurs avec leurs informations
-- ========================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `location` VARCHAR(100),
    `phone_number` VARCHAR(20),
    `bio` TEXT,
    `role` ENUM('utilisateur', 'psychologue', 'admin') DEFAULT 'utilisateur',
    `demande_psy` INT DEFAULT 0,
    `status` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_demande_psy (demande_psy),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- Données par défaut: Compte Admin
-- ========================================
-- Email: admin@supportini.com
-- Password: admin123
-- Role: admin

INSERT INTO `users` (username, email, password, role, status) VALUES 
('admin', 'admin@supportini.com', '$2y$12$OgRHHLtThjiuo4ypIP4Ms.77Ms2ZKO5p3rDo2rEfyCvNDfhsSzauy', 'admin', 1);

-- ========================================
-- Utilisateurs de test (optionnel)
-- ========================================

-- Utilisateur normal
-- Email: user@supportini.com
-- Password: user123
INSERT INTO `users` (username, email, password, location, phone_number, bio, role, status) VALUES 
('johndoe', 'user@supportini.com', '$2y$12$9fTdvBrCfuo2bPQ5dzOtjeOk0FDDPxQP/Sdn3Sp4Hku5wNWhuGjb6', 'Paris', '0123456789', 'Utilisateur de la plateforme', 'utilisateur', 1);

-- Psychologue
-- Email: psy@supportini.com
-- Password: psy123
INSERT INTO `users` (username, email, password, location, phone_number, bio, role, status) VALUES 
('psychologist', 'psy@supportini.com', '$2y$12$URoP70zdXa50eNeYewY0S.D9yYpo8zWoget.cHjM.o4xlEcrzc2Om', 'Lyon', '0987654321', 'Psychologue professionnel', 'psychologue', 1);

-- ========================================
-- Notes sur les mots de passe
-- ========================================
-- Tous les mots de passe sont hachés avec password_hash() (PHP)
--
-- admin123: $2y$12$OgRHHLtThjiuo4ypIP4Ms.77Ms2ZKO5p3rDo2rEfyCvNDfhsSzauy
-- user123: $2y$12$9fTdvBrCfuo2bPQ5dzOtjeOk0FDDPxQP/Sdn3Sp4Hku5wNWhuGjb6
-- psy123: $2y$12$URoP70zdXa50eNeYewY0S.D9yYpo8zWoget.cHjM.o4xlEcrzc2Om

-- ========================================
-- Rôles disponibles
-- ========================================
-- utilisateur: Accès basique au tableau de bord, gestion de profil
-- psychologue: Rôle professionnel intermédiaire
-- admin: Accès complet au panneau d'administration (CRUD)

-- ========================================
-- Instructions d'exécution
-- ========================================
-- 1. Ouvrir phpMyAdmin
-- 2. Cliquer sur "SQL" en haut
-- 3. Copier-coller tout ce fichier
-- 4. Cliquer sur "Exécuter"
-- OU
-- 1. Depuis la ligne de commande MySQL:
--    mysql -u root -p < database.sql