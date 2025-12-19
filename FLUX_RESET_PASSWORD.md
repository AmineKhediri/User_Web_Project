# 📊 DIAGRAMME: Flux de Réinitialisation du Mot de Passe

## Architecture Complète

```
┌─────────────────────────────────────────────────────────────────┐
│                    UTILISATEUR                                   │
├─────────────────────────────────────────────────────────────────┤
│  Accès: /Web_Project_Utilisateurs/View/FrontOffice/forgot_password.php
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│              ÉTAPE 1: Sélection de Méthode                       │
├─────────────────────────────────────────────────────────────────┤
│  Formulaire avec 3 boutons radio:                                │
│  • 📧 Email                                                      │
│  • 📱 SMS                                                        │
│  • 💬 WhatsApp                                                   │
└────────────────────┬────────────────────────────────────────────┘
                     │
        $_POST['step'] = 1
        $_POST['method'] = 'email|sms|whatsapp'
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│         VALIDATION: forgot_password.php (Ligne 27-38)            │
├─────────────────────────────────────────────────────────────────┤
│  if (in_array($method, ['email', 'sms', 'whatsapp'])) {         │
│    $_SESSION['forgot_method'] = $method;                        │
│    $step = 2;  // Passer à l'étape 2                           │
│  }                                                               │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│              ÉTAPE 2: Saisie du Contact                          │
├─────────────────────────────────────────────────────────────────┤
│  Formulaire avec champ unique:                                   │
│  • Si email: "Entrez votre email"                               │
│  • Si SMS: "Entrez votre numéro de téléphone"                  │
│  • Si WhatsApp: "Entrez votre numéro WhatsApp"                 │
└────────────────────┬────────────────────────────────────────────┘
                     │
        $_POST['step'] = 2
        $_POST['contact'] = 'user@example.com' ou '+216XXXXXXXX'
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│         VALIDATION: forgot_password.php (Ligne 44-80)            │
├─────────────────────────────────────────────────────────────────┤
│  1. Vérifier que contact n'est pas vide                         │
│     if (!$contact) → Error: "Veuillez entrer votre ..."         │
│     goto step 2                                                  │
│                                                                  │
│  2. Chercher l'utilisateur                                      │
│     $user = $controller->getUserByContact($contact)             │
│     if (!$user) → Error: "Aucun compte trouvé"                 │
│     goto step 2                                                  │
│                                                                  │
│  3. Générer le code et l'envoyer                                │
│     $code = generateForgottenPasswordCode($email, $method)      │
│     goto step 3                                                 │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│    APPEL CONTRÔLEUR: userController.php (Ligne 410-450)          │
├─────────────────────────────────────────────────────────────────┤
│  public function generateForgottenPasswordCode($email, $method)  │
│  {                                                               │
│    1. Générer code aléatoire: str_pad(random_int(...))          │
│       CODE: 123456 (6 chiffres)                                 │
│                                                                  │
│    2. Définir expiration: +15 minutes                           │
│                                                                  │
│    3. Stocker en base de données:                               │
│       UPDATE users SET                                          │
│         forgotten_password_code = '123456'                      │
│         forgotten_password_method = 'email|sms|whatsapp'        │
│         forgotten_password_expires = NOW() + 15min              │
│       WHERE email = ?                                           │
│                                                                  │
│    4. Envoyer la notification:                                  │
│       if ($method === 'sms') {                                  │
│         notifier->sendSMS($phone, "Code: 123456")              │
│       } elseif ($method === 'whatsapp') {                       │
│         notifier->sendWhatsApp($phone, "Code: 123456")         │
│       } else {                                                  │
│         notifier->sendEmail($email, "Code: 123456")            │
│       }                                                         │
│                                                                  │
│    5. Logger pour debug:                                        │
│       error_log("RESET CODE EMAIL for user@example.com: 123456")
│                                                                  │
│    6. Retourner le code: return '123456'                        │
│  }                                                               │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│    NOTIFICATION: NotificationService.php (Ligne 1-45)            │
├─────────────────────────────────────────────────────────────────┤
│  Selon la méthode:                                               │
│                                                                  │
│  ▶ EMAIL:                                                       │
│    sendEmail($to, $subject, $message)                          │
│    • Mail to: user@example.com                                 │
│    • Subject: "Réinitialisation Mot de Passe"                  │
│    • Message: "Bonjour,\nVotre code: 123456\n..."              │
│    • Headers: From, MIME-Version, Content-Type                 │
│    • Retour: true (loggé dans error_log)                       │
│                                                                  │
│  ▶ SMS:                                                        │
│    sendSMS($phone, $message)                                   │
│    • Phone: +216XXXXXXXX                                       │
│    • Message: "Supportini: Votre code est 123456"             │
│    • Retour: true (loggé dans error_log)                       │
│                                                                  │
│  ▶ WHATSAPP:                                                   │
│    sendWhatsApp($phone, $message)                              │
│    • Phone: +216XXXXXXXX                                       │
│    • Message: "Supportini: Votre code est 123456"             │
│    • Retour: true (loggé dans error_log)                       │
│                                                                  │
│  Logging:                                                       │
│  error_log("=== NOTIFICATION SENT ===");                        │
│  error_log(json_encode([                                        │
│    'type' => 'EMAIL|SMS|WHATSAPP',                             │
│    'to/phone' => '...',                                        │
│    'message' => '...',                                         │
│    'timestamp' => '2025-12-19 14:35:22'                        │
│  ]));                                                           │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│    FALLBACK: forgot_password.php (Ligne 67-75)                   │
├─────────────────────────────────────────────────────────────────┤
│  Si le code ne peut pas être stocké en base (erreur rare):      │
│                                                                  │
│  if ($code === false || $code === null) {                       │
│    // Générer un code local                                    │
│    $code = str_pad(random_int(0, 999999), 6, '0', ...)         │
│                                                                  │
│    // Stocker en session (temporaire)                           │
│    $_SESSION['forgot_code'] = $code;                           │
│    $_SESSION['forgot_email'] = $user['email'];                 │
│    $_SESSION['forgot_method'] = $method;                       │
│                                                                  │
│    // Message utilisateur                                       │
│    $message = "✓ Code généré et enregistré";                   │
│  }                                                               │
│                                                                  │
│  goto step 3                                                    │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│              ÉTAPE 3: Vérification du Code                       │
├─────────────────────────────────────────────────────────────────┤
│  Formulaire avec 3 champs:                                       │
│  • Code de vérification (6 chiffres)                            │
│  • Nouveau mot de passe                                        │
│  • Confirmer mot de passe                                      │
└────────────────────┬────────────────────────────────────────────┘
                     │
        $_POST['step'] = 3
        $_POST['code'] = '123456'
        $_POST['password'] = 'NewPassword123'
        $_POST['password_confirm'] = 'NewPassword123'
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│         VALIDATION: forgot_password.php (Ligne 83-123)           │
├─────────────────────────────────────────────────────────────────┤
│  1. Vérifier que le code n'est pas vide                         │
│     if (!$code) → Error: "Veuillez entrer le code"              │
│     goto step 3                                                  │
│                                                                  │
│  2. Vérifier le code (1ère source: BD)                          │
│     $verified = verifyForgottenPasswordCode($email, $code)      │
│     Vérifie:                                                    │
│     - Le code correspond                                        │
│     - Le code n'a pas expiré                                    │
│                                                                  │
│  3. Fallback (2ème source: Session)                             │
│     if (!$verified && $_SESSION['forgot_code'] === $code) {     │
│       $verified = true;  // Accepter le code local              │
│     }                                                            │
│                                                                  │
│  4. Vérifier les mots de passe                                  │
│     if ($password !== $passwordConfirm)                         │
│       → Error: "Les mots de passe ne correspondent pas"         │
│                                                                  │
│     if (strlen($password) < 6)                                  │
│       → Error: "Minimum 6 caractères"                           │
│                                                                  │
│  5. Réinitialiser le mot de passe                               │
│     $res = resetPasswordWithCode($email, $code, $password)      │
│     └─ Hash avec password_hash() (PASSWORD_DEFAULT)            │
│     └─ Nettoyer les colonnes temporaires                        │
│                                                                  │
│  6. Succès!                                                     │
│     Détruire la session                                         │
│     Rediriger: login.php?reset=success                          │
│     Afficher: "✓ Mot de passe réinitialisé avec succès!"        │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                   ✅ SUCCÈS                                       │
├─────────────────────────────────────────────────────────────────┤
│  Message: "✓ Mot de passe réinitialisé avec succès!"            │
│  Bouton: "Connectez-vous"                                       │
│  → Redirige vers: login.php                                     │
│  → Utilisateur peut se connecter avec nouveau mot de passe      │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Vue Alternative: Appels de Fonction

```
UTILISATEUR
    │
    └──────► forgot_password.php
                 │
                 ├──► $_POST['step'] = 1
                 │    $_SESSION['forgot_method'] = 'email|sms|whatsapp'
                 │
                 ├──► $_POST['step'] = 2
                 │    getUserByContact($contact)
                 │    └──► getUserByEmail() OU search by phone_number
                 │
                 └──► generateForgottenPasswordCode($email, $method)
                      │
                      ├──► Générer code: str_pad(random_int(...))
                      │
                      ├──► UPDATE users SET forgotten_password_code = ?
                      │
                      ├──► Appeler NotificationService
                      │    ├──► sendEmail($to, $subject, $message)
                      │    │    └──► mail() + error_log()
                      │    │
                      │    ├──► sendSMS($phone, $message)
                      │    │    └──► error_log()
                      │    │    └──► (Production: Twilio API)
                      │    │
                      │    └──► sendWhatsApp($phone, $message)
                      │         └──► error_log()
                      │         └──► (Production: WhatsApp API)
                      │
                      └──► return $code

                 ├──► $_POST['step'] = 3
                 │    verifyForgottenPasswordCode($email, $code)
                 │    └──► SELECT * FROM users WHERE email = ? AND code = ? AND expires > NOW()
                 │
                 └──► resetPasswordWithCode($email, $code, $newPassword)
                      ├──► password_hash($newPassword, PASSWORD_DEFAULT)
                      ├──► UPDATE users SET password = ?, code = NULL, ...
                      └──► session_destroy()
                           └──► header("Location: login.php?reset=success")

                                     SUCCÈS ✅
```

---

## 📱 Exemple d'Exécution

### Cas: Email

```
Utilisateur: user@supportini.com
Mot de passe: OldPassword123

───────────────────────────────────────────

ÉTAPE 1: forgot_password.php?step=1
───────────────────────────────────────────
POST:
- step: 1
- method: email

Résultat:
- $_SESSION['forgot_method'] = 'email'
- Afficher: Étape 2

───────────────────────────────────────────

ÉTAPE 2: forgot_password.php?step=2
───────────────────────────────────────────
POST:
- step: 2
- contact: user@supportini.com

Appels:
1. getUserByContact('user@supportini.com')
   → SELECT * FROM users WHERE email = 'user@supportini.com'
   → Retour: {id: 5, username: 'johndoe', email: 'user@supportini.com', ...}

2. generateForgottenPasswordCode('user@supportini.com', 'email')
   → Code généré: 847392
   → UPDATE users SET forgotten_password_code = '847392', 
                     forgotten_password_expires = '2025-12-19 14:50:22'
   → sendEmail('user@supportini.com', 'Réinitialisation...', '...847392...')
   → error_log: "RESET CODE EMAIL for user@supportini.com: 847392"
   → Retour: 847392

Résultat:
- $_SESSION['forgot_code'] = '847392'
- $_SESSION['forgot_email'] = 'user@supportini.com'
- Message: "✓ Code envoyé à votre email"
- Afficher: Étape 3

───────────────────────────────────────────

ÉTAPE 3: forgot_password.php?step=3
───────────────────────────────────────────
POST:
- step: 3
- code: 847392
- password: NewPassword456
- password_confirm: NewPassword456

Appels:
1. verifyForgottenPasswordCode('user@supportini.com', '847392')
   → SELECT * FROM users WHERE email = 'user@supportini.com' AND code = '847392'
   → Vérifier: forgotten_password_expires > NOW()
   → Retour: true

2. resetPasswordWithCode('user@supportini.com', '847392', 'NewPassword456')
   → Hacher: password_hash('NewPassword456', PASSWORD_DEFAULT)
   → Hash: $2y$12$aBcDefGhIjKlMnOpQrStUvWxYz...
   → UPDATE users SET password = '$2y$12$...', forgotten_password_code = NULL, ...
   → Retour: true

3. session_destroy()
   → Détruire tous les $_SESSION variables

Résultat:
- Message: "✓ Mot de passe réinitialisé avec succès!"
- Redirection: login.php?reset=success
- Utilisateur peut se connecter avec: user@supportini.com / NewPassword456

───────────────────────────────────────────

CONNEXION: login.php
───────────────────────────────────────────
POST:
- email: user@supportini.com
- password: NewPassword456

Résultat:
- validateLogin() → password_verify retourne true ✓
- Session créée: $_SESSION['user_id'] = 5
- Redirection: dashboard.php
- Message: "✓ Mot de passe réinitialisé avec succès!"

───────────────────────────────────────────
```

---

## 🛠️ Debugging avec Logs

### Afficher les codes générés

```bash
# macOS XAMPP
tail -20 /Applications/XAMPP/xamppfiles/logs/php_error.log | grep "RESET CODE"

# Output:
# [19-Dec-2025 14:35:22] RESET CODE EMAIL for user@supportini.com: 847392
# [19-Dec-2025 14:36:15] RESET CODE SMS for +21650000000: 392156
# [19-Dec-2025 14:37:08] RESET CODE WHATSAPP for +21650000000: 512743
```

### Format complet du log

```json
=== NOTIFICATION SENT ===
{"type":"EMAIL","to":"user@supportini.com","subject":"Réinitialisation Mot de Passe","message":"Bonjour johndoe,\nVotre code de réinitialisation est : 847392\nIl expire dans 15 minutes.","timestamp":"2025-12-19 14:35:22","ip":"127.0.0.1"}
=========================
```

---

**Créé le:** 19 Décembre 2025
**Version:** 1.0
**Status:** ✅ Complet et testé
