# SUPPORTINI - Guide Simple pour Débutants

## 📌 Situation Actuelle

✅ **Votre compte est maintenant ADMIN!**

Vous avez changé le rôle dans PHPMyAdmin:
- Email: `khediri.amine.inceptumje@gmail.com`
- Rôle: changé de "utilisateur" à "admin"

---

## 🚀 Qu'est-ce que vous pouvez faire maintenant?

### 1️⃣ Se connecter en tant qu'ADMIN

Allez à: http://localhost/Web_Project_Utilisateurs/View/FrontOffice/login.php

**Entrez:**
- Email: `khediri.amine.inceptumje@gmail.com`
- Mot de passe: (votre mot de passe personnel)

**Résultat:** Vous serez automatiquement redirigé vers le **Panel Admin**

---

### 2️⃣ Utiliser le Panel Admin

Une fois connecté, vous pouvez:

✏️ **Voir tous les utilisateurs** - Liste complète dans une table
➕ **Ajouter un utilisateur** - Créer un nouveau compte
🔄 **Modifier un utilisateur** - Changer ses informations ou son rôle
🗑️ **Supprimer un utilisateur** - Supprimer un compte

---

## 📁 Fichiers Importants à Connaître

```
Web_Project_Utilisateurs/
├── config.php ..................... Connexion à la base de données
├── View/FrontOffice/
│   ├── login.php .................. Page de connexion
│   ├── signup.php ................. Page d'inscription
│   └── dashboard.php .............. Profil utilisateur
└── View/BackOffice/
    └── users.php .................. Gestion des utilisateurs (ADMIN)
```

---

## 🔑 Les 3 Rôles du Système

| Rôle | Peut faire | Accès |
|------|-----------|-------|
| **Utilisateur** | Voir son profil | Dashboard utilisateur |
| **Psychologue** | Voir son profil | Dashboard utilisateur |
| **Admin** | Tout gérer | Panel administratif |

---

## 💻 Choses à Essayer

### ✅ Test 1: Se connecter en Admin (5 min)
1. Visitez: http://localhost/Web_Project_Utilisateurs/View/FrontOffice/login.php
2. Entrez votre email et mot de passe
3. Vous verrez le panel admin avec la liste des utilisateurs

### ✅ Test 2: Créer un nouvel utilisateur (2 min)
1. Allez à: View/BackOffice/users.php
2. Cliquez sur **"+ Ajouter Utilisateur"**
3. Remplissez le formulaire
4. Cliquez sur "Ajouter"

### ✅ Test 3: Modifier un utilisateur (2 min)
1. Dans la liste des utilisateurs
2. Cliquez sur **"Modifier"** pour cet utilisateur
3. Changez son rôle ou ses informations
4. Cliquez sur "Mettre à jour"

### ✅ Test 4: Supprimer un utilisateur (1 min)
1. Dans la liste des utilisateurs
2. Cliquez sur **"Supprimer"** pour cet utilisateur
3. Confirmez la suppression

---

## ⚙️ Configuration Minimale

Le fichier `config.php` contient:

```php
Database: supportini
Host: localhost
User: root
Password: (vide)
```

**Changez SEULEMENT si:**
- Votre serveur MySQL a un mot de passe
- Vous utilisez une BD différente

---

## 🛟 Résolution Rapide de Problèmes

### ❌ "Impossible de se connecter"
**Solution:** 
- Vérifiez que vous entrez le BON email
- Vérifiez le mot de passe (sensible à la casse!)
- Votre compte existe-t-il?

### ❌ "Je suis redirigé vers la page de connexion"
**Solution:**
- Vous êtes probablement pas connecté
- Connectez-vous d'abord!

### ❌ "Je ne vois pas le panel admin"
**Solution:**
- Votre rôle est-il vraiment "admin" dans PHPMyAdmin?
- Reconnectez-vous après le changement

### ❌ "Base de données introuvable"
**Solution:**
- Visitez: http://localhost/Web_Project_Utilisateurs/setup.php
- Cliquez sur "Installation"
- La BD sera créée automatiquement

---

## 📊 Flux de Connexion

```
1. Visitez login.php
   ↓
2. Entrez email + mot de passe
   ↓
3. Système vérifie le mot de passe
   ↓
4a. Si ADMIN → Redirige vers BackOffice/users.php
4b. Si Utilisateur/Psychologue → Redirige vers FrontOffice/dashboard.php
   ↓
5. Accédez aux fonctions selon votre rôle
```

---

## 🔐 Sécurité - Les Bases

- ✅ Les mots de passe sont chiffrés en BD
- ✅ Les sessions sont sécurisées
- ✅ Les données sont validées
- ✅ Les requêtes SQL sont protégées

---

## 📝 Prochaines Étapes

1. **Testez le login** - Vérifiez que vous pouvez vous connecter
2. **Explorez le panel admin** - Ajoutez/modifiez/supprimez des utilisateurs
3. **Créez des comptes de test** - Pour comprendre les différents rôles
4. **Invitez des gens** - Partagez le lien d'inscription

---

## 📞 Aide Rapide

- **Code pas clair?** → Regardez les commentaires PHP
- **Erreur strange?** → Vérifiez la console (F12 dans le navigateur)
- **Session expire?** → Reconnectez-vous

---

## ✨ Bon à Savoir

- Les utilisateurs reçoivent "utilisateur" par défaut
- Vous pouvez changer le rôle dans le panel admin
- Les emails doivent être uniques
- Les noms d'utilisateur doivent être uniques

---

**Vous êtes prêt! Commencez à explorer! 🚀**

Dernière mise à jour: Décembre 2024