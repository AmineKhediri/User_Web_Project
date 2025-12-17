# 📌 INSTRUCTIONS DE MISE EN PLACE - DEMANDES PSYCHOLOGUE

## 🎯 Objectif
Implémenter un système où les utilisateurs peuvent demander le statut "Psychologue" lors de l'inscription, et les administrateurs approuvent ou rejettent ces demandes.

## ✅ ÉTAPES À SUIVRE

### ÉTAPE 1 : Migration de la Base de Données

#### Option A - Nouvelle installation (base vierge)
1. Ouvrir phpMyAdmin
2. Créer une nouvelle base de données `supportini` (ou laisser le setup.php le faire)
3. Importer le fichier `database.sql` complètement
4. ✅ Prêt à l'emploi

#### Option B - Installation existante (base avec données)
1. Accéder à : `http://localhost/Web_Project_Utilisateurs/migrate.php`
2. Attendre le message "✓ Migration réussie"
3. La colonne `demande_psy` est ajoutée automatiquement
4. ✅ Prêt à l'emploi

### ÉTAPE 2 : Vérifier le logo
- **Localisation** : Racine du projet `logo_supportini.jpg`
- Si le fichier n'existe pas :
  - Créer un fichier `logo_supportini.jpg` dans `/Applications/XAMPP/xamppfiles/htdocs/Web_Project_Utilisateurs/`
  - OU utiliser un placeholder JPG/PNG
  - OU remplacer les chemins `../../logo_supportini.jpg` par une autre image existante

### ÉTAPE 3 : Tester le système

#### A. Inscription avec demande psychologue
1. Accéder à `http://localhost/Web_Project_Utilisateurs/View/FrontOffice/signup.php`
2. Remplir le formulaire
3. ✅ Cocher "Je suis un psychologue/praticien"
4. Cliquer "S'inscrire"
5. Compte créé avec `demande_psy = 1` et `role = 'utilisateur'`

#### B. Gestion des demandes (Admin)
1. Se connecter en tant qu'admin
   - Email : `admin@supportini.com`
   - Mot de passe : `admin123`
2. Cliquer sur "Demandes Psychologue" dans le menu
3. Voir la liste des utilisateurs ayant demandé le statut
4. Deux options :
   - ✅ **Approuver** : Change le rôle à "psychologue" + marque comme traité
   - ❌ **Rejeter** : Refuse la demande + reste "utilisateur"

#### C. Vérification dans la gestion utilisateurs
1. Admin → "Utilisateurs"
2. Les utilisateurs approuvés ont le badge "Psychologue"
3. Ceux rejetés restent "Utilisateur"

---

## 📊 Flux de données

```
┌─────────────────────────────────────────┐
│ Inscription (signup.php)                 │
│ - Checkbox: "Je suis psychologue"       │
│ - Données : demande_psy = 1             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│ Base de données (users)                  │
│ - role = "utilisateur"                  │
│ - demande_psy = 1                       │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│ Page Admin (psy_requests.php)            │
│ - Liste les demandes en attente         │
│ - Boutons : Approuver / Rejeter         │
└──────────────┬──────────────────────────┘
               │
       ┌───────┴────────┐
       │                │
       ▼                ▼
   APPROUVER        REJETER
       │                │
       ▼                ▼
  role =            demande_psy
  "psychologue"     = 0 uniquement
  demande_psy = 0
```

---

## 🔐 Comptes de test

### Admin
```
Email: admin@supportini.com
Mot de passe: admin123
```

### Utilisateur normal
```
Email: user@supportini.com
Mot de passe: user123
```

### Psychologue
```
Email: psy@supportini.com
Mot de passe: psy123
```

---

## 📝 Modifications techniques

### Fichiers PHP modifiés
- `signup.php` - Gestion du POST demande_psy
- `userController.php` - Nouvelles méthodes
- `users.php` - Lien vers demandes psy

### Fichiers PHP créés
- `psy_requests.php` - Nouvelle page admin
- `migrate.php` - Script de migration

### Base de données
- Nouvelle colonne : `demande_psy` (INT DEFAULT 0)
- Nouvel index : `idx_demande_psy`

---

## 🐛 Dépannage

### Le logo n'apparaît pas
- ✅ Vérifier que `logo_supportini.jpg` existe dans la racine
- ✅ Vérifier l'extension (sensible à la casse)
- ✅ Vérifier les permissions du fichier (lecture)

### Migration échoue
- ✅ Vérifier que la base est créée
- ✅ Vérifier les permissions d'ALTER TABLE
- ✅ Vérifier que config.php a les bonnes données de connexion

### Les demandes n'apparaissent pas
- ✅ Vérifier que `demande_psy = 1` en base
- ✅ Vérifier que `role = 'utilisateur'` (condition)
- ✅ Vérifier les droits admin (session)

### Erreur "Connexion refusée"
- ✅ Vérifier que XAMPP est lancé (MySQL actif)
- ✅ Vérifier les données dans config.php
- ✅ Vérifier phpmyadmin : `http://localhost/phpmyadmin`

---

## 🎓 Prochaines améliorations possibles

1. Notification email à l'admin quand demande reçue
2. Notification à l'utilisateur quand demande approuvée/rejetée
3. Justificatif/document de certification pour les psychologues
4. Filtrage avancé dans la page de gestion
5. Historique des approvals/rejections
6. Renouvellement du statut psychologue (annuel)

---

**Document créé** : 17 décembre 2025  
**Version** : 1.0  
**Statut** : ✅ Production Ready
