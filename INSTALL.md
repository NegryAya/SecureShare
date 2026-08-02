# Guide d'installation — SecureShare (Sprint 1)

## 1. Prérequis

- PHP >= 8.2 avec les extensions : `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- Composer 2.x
- MySQL 8 (ou MariaDB 10.4+)
- Git (optionnel)

Vérifiez votre version de PHP :
```bash
php -v
```

## 2. Récupérer le projet

Décompressez l'archive `secureshare.zip`, puis placez-vous dans le dossier :
```bash
cd secureshare
```

## 3. Installer les dépendances PHP

```bash
composer install
```

Cette commande télécharge le framework Laravel et toutes les librairies
listées dans `composer.json` (dossier `vendor/`).

## 4. Configurer l'environnement

Copiez le fichier d'exemple :
```bash
cp .env.example .env
```

Générez la clé d'application (utilisée pour le chiffrement des sessions/cookies) :
```bash
php artisan key:generate
```

## 5. Créer la base de données MySQL

Connectez-vous à MySQL et créez une base vide :
```sql
CREATE DATABASE secureshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis modifiez le fichier `.env` avec vos identifiants :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=secureshare
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

## 6. Exécuter les migrations

```bash
php artisan migrate
```

Cette commande crée toutes les tables : `users`, `password_reset_tokens`,
`sessions`, `cache`, `jobs`, `files`, `shared_links`, `logs`.

(Optionnel) Pour créer un compte de démonstration :
```bash
php artisan db:seed
```
Identifiants créés :
- `admin@secureshare.test` / `password` (rôle admin)
- `test@secureshare.test` / `password` (rôle utilisateur)

## 7. Créer le lien symbolique de stockage

Nécessaire pour les futurs fichiers uploadés (Sprint 2) :
```bash
php artisan storage:link
```

## 8. Lancer le serveur de développement

```bash
php artisan serve
```

L'application est accessible sur : **http://localhost:8000**

## 9. Configuration de l'envoi d'email (Mot de passe oublié)

Par défaut, `.env` utilise `MAIL_MAILER=log` : les emails de réinitialisation
de mot de passe sont écrits dans `storage/logs/laravel.log` au lieu d'être
réellement envoyés (pratique pour tester en local sans serveur SMTP).

Pour tester : allez sur `/forgot-password`, entrez un email existant, puis
ouvrez `storage/logs/laravel.log` pour récupérer le lien de réinitialisation.

Pour un envoi réel, configurez un service SMTP dans `.env` (ex: Mailtrap,
Gmail, etc.) :
```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

## 10. Vérifier que tout fonctionne

- `http://localhost:8000/` → page d'accueil
- `http://localhost:8000/register` → créer un compte
- `http://localhost:8000/login` → se connecter
- `http://localhost:8000/dashboard` → tableau de bord (protégé, redirige vers /login si non connecté)
- `http://localhost:8000/forgot-password` → mot de passe oublié

## 11. Lancer les tests automatisés (optionnel)

```bash
php artisan test
```

## 12. Sprint 2 — Gestion des fichiers

Aucune étape supplémentaire n'est requise : les migrations du Sprint 2
(`shared_links` enrichie) sont exécutées automatiquement par
`php artisan migrate` (étape 6).

Assurez-vous simplement que le dossier `storage/` est accessible en
écriture par le serveur web (les fichiers uploadés sont stockés dans
`storage/app/private/files/{user_id}/`) :
```bash
chmod -R 775 storage bootstrap/cache
```

Pour tester le partage :
1. Connectez-vous, allez sur **Upload**, envoyez un fichier (PDF/DOCX/XLSX/JPG/PNG/ZIP, max 20 Mo)
2. Allez sur **Mes fichiers**, cliquez sur **Partager**, choisissez une expiration et (optionnellement) un mot de passe
3. Le lien généré apparaît sur la page **Fichiers partagés** — copiez-le et ouvrez-le dans une navigation privée pour vérifier qu'il fonctionne sans être connecté

## 13. Sprint 3 — Profil, historique, purge automatique

Aucune migration supplémentaire n'est requise (le schéma était déjà prêt).

Pour que la purge automatique des liens expirés fonctionne
(`shared-links:prune`, planifiée quotidiennement), ajoutez cette ligne au
crontab de votre serveur (inutile en local pour simplement tester
l'application) :
```
* * * * * cd /chemin/vers/secureshare && php artisan schedule:run >> /dev/null 2>&1
```

Vous pouvez aussi lancer la purge manuellement à tout moment :
```bash
php artisan shared-links:prune
```

## Dépannage courant

| Problème | Solution |
|---|---|
| `SQLSTATE[HY000] [1049] Unknown database` | La base `secureshare` n'existe pas encore : créez-la (étape 5) |
| `No application encryption key has been specified` | Lancez `php artisan key:generate` |
| Page blanche / erreur 500 | Vérifiez `storage/logs/laravel.log`, et que `storage/` + `bootstrap/cache/` sont accessibles en écriture (`chmod -R 775 storage bootstrap/cache` sous Linux/Mac) |
| CSS Bootstrap non chargé | Vérifiez votre connexion internet (le CDN Bootstrap est chargé depuis jsdelivr.net) |
