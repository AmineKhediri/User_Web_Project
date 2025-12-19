# 🧪 GUIDE DE TEST RAPIDE - Réinitialisation Mot de Passe

## ⚡ Test 30 secondes

### Étape 1: Vérifier la génération du code
```bash
# Ouvrir un terminal et accédez aux logs
tail -20 /Applications/XAMPP/xamppfiles/logs/php_error.log
```

### Étape 2: Tester via le web
1. Accédez à: `http://localhost/Web_Project_Utilisateurs/test_email.php`
2. Cliquez: "Test Email (Admin)"
3. Voir le message ✅ et vérifier les logs

### Étape 3: Test complet du flux
1. Allez à: `http://localhost/Web_Project_Utilisateurs/View/FrontOffice/forgot_password.php`
2. Sélectionnez: **Email**
3. Saisissez: `admin@supportini.com`
4. Cliquez: **Continuer**
5. Message: "✓ Code envoyé à votre email"
6. **Consultez les logs** pour obtenir le code (voir ci-dessous)
7. Saisissez le code
8. Créez un nouveau mot de passe
9. Cliquez: **Réinitialiser**
10. Message: "✓ Mot de passe réinitialisé avec succès!"

---

## 📝 Comptes de Test Disponibles

### Admin
```
Email: admin@supportini.com
Password: admin123
```

### Utilisateur
```
Email: user@supportini.com
Password: user123
```

### Psychologue
```
Email: psy@supportini.com
Password: psy123
```

---

## 🔍 Comment Récupérer le Code Généré

### Méthode 1: Terminal (Recommandé)

```bash
# Afficher les 30 dernières lignes des logs
tail -30 /Applications/XAMPP/xamppfiles/logs/php_error.log

# Output attendu:
# [19-Dec-2025 14:35:22 Europe/Zurich] RESET CODE EMAIL for admin@supportini.com: 847392
#
# Cherchez la ligne: RESET CODE EMAIL
# Le code est: 847392
```

### Méthode 2: Rechercher spécifiquement

```bash
# Filtrer pour les codes de reset
grep "RESET CODE" /Applications/XAMPP/xamppfiles/logs/php_error.log | tail -1

# Output:
# [19-Dec-2025 14:35:22 Europe/Zurich] RESET CODE EMAIL for admin@supportini.com: 847392
```

### Méthode 3: Suivi en temps réel

```bash
# Dans un terminal, afficher les logs en temps réel
tail -f /Applications/XAMPP/xamppfiles/logs/php_error.log | grep "RESET CODE"

# Ensuite, déclenchez la génération du code dans le navigateur
# Et vous verrez le code s'afficher instantanément
```

---

## ✅ Checklist de Validation

- [ ] Accédez à `test_email.php` sans erreur
- [ ] Cliquez sur un test et voyez le message ✓
- [ ] Allez à `forgot_password.php` sans erreur
- [ ] Sélectionnez Email / SMS / WhatsApp
- [ ] Saisissez un email valide
- [ ] Message: "Code envoyé"
- [ ] Trouvez le code dans les logs
- [ ] Saisissez le code correct
- [ ] Saisissez un nouveau mot de passe
- [ ] Cliquez Réinitialiser
- [ ] Message: "Succès!"
- [ ] Pouvez-vous vous connecter avec le nouveau mot de passe?

---

## 🚨 Troubleshooting

### Problème: "Fichier non trouvé" sur test_email.php

**Solution:**
```bash
# Vérifier que le fichier existe
ls -la /Applications/XAMPP/xamppfiles/htdocs/Web_Project_Utilisateurs/test_email.php

# Vérifier la syntaxe
php -l /Applications/XAMPP/xamppfiles/htdocs/Web_Project_Utilisateurs/test_email.php
```

### Problème: Pas de logs PHP

**Solution:**
```bash
# Vérifier que le fichier log existe
ls -la /Applications/XAMPP/xamppfiles/logs/php_error.log

# Donner les permissions correctes
chmod 666 /Applications/XAMPP/xamppfiles/logs/php_error.log

# Redémarrer XAMPP
open /Applications/XAMPP/XAMPP\ Control.app
# → Stop all
# → Start all
```

### Problème: Code expiré

**Details:**
- Le code expire après **15 minutes**
- Si vous ne voyez pas le code, rafraîchissez les logs

### Problème: "Aucun compte trouvé"

**Solution:**
```bash
# Vérifier les utilisateurs existants
php -r "
  require 'config.php';
  \$pdo = config::getConnexion();
  \$stmt = \$pdo->query('SELECT id, email FROM users');
  foreach (\$stmt as \$user) echo \$user['email'] . PHP_EOL;
"

# Output:
# admin@supportini.com
# user@supportini.com
# psy@supportini.com
```

---

## 📊 Exemple d'Exécution Complète

### Terminal 1: Suivi des logs

```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error.log | grep -E "RESET|EMAIL|SMS|WHATSAPP"
```

### Terminal 2: Test via CLI

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Web_Project_Utilisateurs

php -r "
  require 'Controller/userController.php';
  \$ctrl = new userController();
  \$code = \$ctrl->generateForgottenPasswordCode('admin@supportini.com', 'email');
  echo 'Code généré: ' . \$code . PHP_EOL;
"
```

### Navigateur

```
1. Allez à: http://localhost/Web_Project_Utilisateurs/forgot_password.php
2. Sélectionnez: Email
3. Saisissez: admin@supportini.com
4. Cliquez: Continuer

→ Vous verrez: "✓ Code envoyé à votre email"
→ Terminal 1 affichera: "RESET CODE EMAIL for admin@supportini.com: XXXXXX"
```

---

## 🎯 Points Clés à Retenir

| Point | Détail |
|-------|--------|
| **Codes générés** | 6 chiffres aléatoires (000000-999999) |
| **Expiration** | 15 minutes |
| **Stockage** | Colonne `forgotten_password_code` en BD |
| **Logs** | `/Applications/XAMPP/xamppfiles/logs/php_error.log` |
| **Méthodes** | Email, SMS, WhatsApp |
| **Fallback** | Stockage local en session si BD échoue |
| **Hash mot de passe** | `PASSWORD_DEFAULT` (bcrypt) |

---

## 📞 Support

**Pas de codes dans les logs?**

1. Vérifier que XAMPP est en cours d'exécution
2. Vérifier que le fichier log est accessible
3. Consulter: `RAPPORT_CORRECTION_RESET_PASSWORD.md`
4. Consulter: `FLUX_RESET_PASSWORD.md`

**Codes qui ne fonctionnent pas?**

1. Vérifier l'expiration (15 min)
2. Vérifier l'email/téléphone correspondant
3. Vérifier que la BD est à jour

---

**Créé le:** 19 Décembre 2025
**Dernier commit:** 06f9eca
**Status:** ✅ Prêt pour la validation
