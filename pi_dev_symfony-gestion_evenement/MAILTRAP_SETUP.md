# 📧 Configuration Mailtrap - Guide d'installation

## ✅ Mailtrap: Service SMTP gratuit pour tester les emails

### Étape 1: Créer un compte Mailtrap GRATUIT

1. Allez sur **https://mailtrap.io**
2. Cliquez sur **"Sign Up"** (100% gratuit)
3. Validez votre email
4. Connectez-vous au tableau de bord

### Étape 2: Récupérer vos credentials SMTP

1. Dans le dashboard Mailtrap, allez dans **"Email Testing"** 
2. Sélectionnez (ou créez) un "Project" (ex: "GrowMind")
3. Cliquez sur **"Integrations"**
4. Cherchez **"Symfony"** ou **"SMTP"**
5. Vous verrez un format SMTP comme:

```
Host: smtp.mailtrap.io
Port: 2525
Username: votre_username
Password: votre_password
Encryption: TLS
```

### Étape 3: Mettre à jour .env

Remplacez dans `.env` les valeurs USERNAME et PASSWORD:

```bash
MAILER_DSN=smtp://USERNAME:PASSWORD@smtp.mailtrap.io:2525/?encryption=tls
```

**Exemple:**
```bash
MAILER_DSN=smtp://abc123xyz:pass456@smtp.mailtrap.io:2525/?encryption=tls
```

### Étape 4: Valider la configuration

```bash
# Vider le cache pour l'appliquer
php bin/console cache:clear

# Tester que le service est toujours OK
php bin/console debug:container EmailService
```

### Étape 5: Tester l'envoi d'email

```bash
# Lancer la commande de rappels
php bin/console app:send-reminders
```

### Étape 6: Vérifier les emails reçus

1. Retournez sur **https://mailtrap.io/dashboard**
2. Dans votre inbox Mailtrap, vous verrez tous les emails envoyés
3. Vous pouvez voir le contenu HTML complet et le sujet

---

## 📌 Avantages de Mailtrap vs MailPit

| Feature | Mailtrap | MailPit |
|---------|----------|---------|
| Interface web | ✅ Gratuit SaaS | ✅ Local |
| Partage d'équipe | ✅ Oui | ❌ Non |
| Stockage cloud | ✅ Illimité | ❌ Local only |
| Port standard | ✅ 2525 | ❌ 1025 local |
| Email réel testé | ✅ Oui | ✅ Oui |
| Installation | ✅ Zéro config | ✅ Docker |

---

## 🔧 Troubleshooting

### Erreur: "SMTP: 5.7.1 Invalid credentials"
→ Vérifiez que USERNAME et PASSWORD sont corrects (copier-coller depuis Mailtrap)

### Erreur: "Could not send email"
→ Assurez-vous que port 2525 est ouvert (généralement OK partout)

### Pas de réception d'email?
→ Allez dans Mailtrap → "Spam" folder

---

## 💡 Notes
- Mailtrap capture 50 emails/jour GRATUIT (plus que suffisant pour tester)
- Pas besoin de lancer un service local
- Tous les emails de test sont stockés et consultables en ligne
- Configuration PROD différente (services comme SendGrid, Mailgun, etc.)
