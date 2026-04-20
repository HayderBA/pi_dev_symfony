# 📧 Service Email - GrowMind

## Configuration Symfony 6.4 optimisée

Ce service utilise **TemplatedEmail** de Symfony 6.4 avec MailerBundle pour une intégration Twig native.

### ✨ Fonctionnalités

- ✅ 5 types d'emails (Confirmation, Bienvenue, Rappel, Satisfaction, Offre)
- ✅ Templates Twig natifs
- ✅ Support MailPit pour développement
- ✅ Commande cron automatisée

### 🔧 Configuration

#### `.env` (Développement avec MailPit)
```env
MAILER_DSN=smtp://localhost:1025
SITE_URL=http://localhost:8000
```

#### `.env.prod` (Production SMTP)
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587?encryption=tls
SITE_URL=https://growmind.com
```

### 📨 Emails Disponibles

| Email | Méthode | Déclencheur | Template |
|---|---|---|---|
| Confirmation | `sendReservationConfirmation()` | Immédiat | `reservation_confirmation.html.twig` |
| Bienvenue | `sendWelcomeEmail()` | 1ère réservation | `welcome.html.twig` |
| Rappel 24h | `sendReminderEmail()` | Cron | `reminder_before_event.html.twig` |
| Satisfaction | `sendSatisfactionEmail()` | Cron + 24h | `satisfaction_after_event.html.twig` |
| Offre 20% | `sendSpecialOfferEmail()` | Manuel | `special_offer.html.twig` |

### 🚀 Utilisation

#### Envoyer un email de confirmation
```php
$this->emailService->sendReservationConfirmation($reservation);
```

#### Envoyer un email personnalisé
```php
$this->emailService->sendCustomEmail(
    'user@example.com',
    'Mon sujet',
    'emails/my_template.html.twig',
    ['key' => 'value']
);
```

### 🐧 Commande Cron

```bash
# Envoyer les rappels (24h avant) et satisfactions (24h après)
php bin/console app:send-reminders

# À ajouter dans crontab:
0 8 * * * cd /path/to/project && php bin/console app:send-reminders
```

### 🧪 Test avec MailPit

1. Démarrer MailPit:
```bash
mailpit
```

2. Accéder à l'interface:
```
http://localhost:1025
```

### 📦 Bundles utilisés

- `symfony/mailer` - Envoi d'emails
- `symfony/mime` - Construction MIME
- `symfony/twig-bridge` - Integration Twig

### 🔐 Best Practices Symfony 6.4

✅ Utilise `TemplatedEmail` (natif Twig)
✅ Configuration YAML externalisée
✅ Autowiring des services
✅ Variables globales Twig
✅ Enveloppe d'email configurée

### 📝 Variables Globales Twig

Ces variables sont disponibles dans tous les templates d'email:

```twig
{{ app_name }}        {# GrowMind #}
{{ app_email }}       {# noreply@growmind.com #}
{{ site_url }}        {# http://localhost:8000 #}
```

---

**Tout est prêt à l'emploi! 🎉**
