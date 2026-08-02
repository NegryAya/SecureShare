# 🔒 SecureShare

Plateforme web sécurisée de partage de fichiers — projet de stage (Génie Informatique, 1ère année).

Développée avec **Laravel 12**, **PHP 8.2+**, **MySQL**, **Blade** et **Bootstrap 5**, en respectant
l'architecture MVC et les bonnes pratiques Laravel (Form Requests, Middleware, Eloquent, CSRF, hashage bcrypt).

Le projet est développé **sprint par sprint**. Ce dépôt contient actuellement le **Sprint 1**.

---

## 📌 État d'avancement

| Sprint | Contenu | Statut |
|--------|---------|--------|
| **Sprint 1** | Projet Laravel, authentification complète, dashboard, structure MVC | ✅ Terminé |
| **Sprint 2** | Upload, liste, téléchargement, suppression, partage sécurisé par lien (mot de passe + expiration), logs | ✅ Terminé |
| **Sprint 3** | Profil utilisateur, historique d'activité, purge automatique des liens expirés | ✅ Terminé |
| V2 | Espace administrateur | ⏳ À venir |

---

## ✅ Contenu du Sprint 1

- Création et configuration du projet Laravel 12
- Connexion à une base de données **MySQL**
- **Migrations** : `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`, `files`, `shared_links`, `logs`
  *(les tables `files`, `shared_links` et `logs` sont créées dès maintenant pour préparer la base de données
  complète du projet, mais aucune fonctionnalité d'upload / partage / téléchargement n'est développée dans ce sprint)*
- **Modèles Eloquent** : `User`, `File`, `SharedLink`, `Log`, avec leurs relations
- **Authentification complète** :
  - Inscription (Register)
  - Connexion (Login) avec protection anti brute-force (rate limiting)
  - Déconnexion (Logout)
  - Mot de passe oublié (Forgot Password / Reset Password)
- **Dashboard** protégé après connexion (statistiques prêtes pour le Sprint 2)
- **Structure MVC complète** : Controllers, Models, Form Requests, Blade Views, Routes, Middleware
- Interface **responsive** avec Bootstrap 5
- Tests automatisés (PHPUnit) sur l'authentification

Voir le fichier **[EXPLICATIONS.md](EXPLICATIONS.md)** pour le détail de chaque fichier créé.

---

## ✅ Contenu du Sprint 2

- **Upload de fichiers** : PDF, DOCX, XLSX, JPG, JPEG, PNG, ZIP — 20 Mo max, validation MIME + extension,
  renommage physique en **UUID** (le nom stocké n'a aucun rapport avec le nom d'origine)
- **Mes fichiers** : liste paginée (Nom, Taille, Type, Date, Actions)
- **Téléchargement** : réservé au propriétaire du fichier (`FilePolicy`), chaque téléchargement journalisé
- **Suppression** : retire la ligne en base **et** le fichier physique du disque
- **Partage sécurisé** :
  - Génération d'un **token aléatoire de 40 caractères**, non devinable
  - **Mot de passe optionnel**, jamais stocké en clair (haché avec `Hash::make`)
  - **Expiration** au choix : 24 heures, 7 jours, ou jamais — vérifiée automatiquement à chaque accès
  - Page publique `/share/{token}` accessible sans compte
  - Compteur de téléchargements par lien
- **Fichiers partagés** : liste des liens créés par l'utilisateur (statut actif/expiré, révocation possible)
- **Journalisation (logs)** : upload, download, delete, share — avec utilisateur, action, date et adresse IP

---

## ✅ Contenu du Sprint 3

- **Profil utilisateur** : modifier prénom / nom / email, changer de mot de passe (avec vérification du mot de passe actuel)
- **Historique d'activité** : chaque utilisateur consulte son propre journal (`logs`) — connexions, uploads, téléchargements, suppressions, partages
- **Purge automatique** des liens de partage expirés : commande `php artisan shared-links:prune`, planifiée quotidiennement
- **Gestion avancée des fichiers** : renommer un fichier, remplacer son contenu (même enregistrement, les liens de partage restent valides)
- **Recherche, filtrage et tri** dans "Mes fichiers" : par nom, par type, par date/taille
- **Statistiques enrichies** sur le tableau de bord : liens générés, liens actifs, liens expirés
- **Interface** : icônes Bootstrap Icons, alertes améliorées, boutons d'action plus clairs

---

## 🚀 Installation rapide

Voir le guide détaillé **[INSTALL.md](INSTALL.md)**.

```bash
composer install
cp .env.example .env
php artisan key:generate
# configurer .env avec vos identifiants MySQL (voir INSTALL.md)
php artisan migrate
php artisan storage:link
php artisan serve
```

Application disponible sur : **http://localhost:8000**

Compte de démonstration (après `php artisan db:seed`) :
- **Email** : `test@secureshare.test`
- **Mot de passe** : `password`

---

## 🗂️ Structure du projet

```
app/
  Http/
    Controllers/
      Auth/                     Authentification (Register, Login, Password Reset)
      DashboardController.php   Tableau de bord utilisateur
    Requests/
      Auth/                     Validation des formulaires (Form Requests)
  Models/                       User, File, SharedLink, Log
  Providers/
database/
  migrations/                   Structure de la base de données
  factories/
  seeders/
resources/
  views/
    layouts/                    Layouts Blade (guest, app)
    auth/                       Vues de connexion / inscription / mot de passe oublié
    dashboard/                  Vue du tableau de bord
    welcome.blade.php           Page d'accueil
routes/
  web.php                       Toutes les routes de l'application
tests/
  Feature/AuthenticationTest.php
```

---

## 🔐 Sécurité (Sprint 1)

- Protection **CSRF** sur tous les formulaires (`@csrf`)
- **Validation** complète via Form Requests (`RegisterRequest`, `LoginRequest`)
- **Hashage bcrypt** des mots de passe (`Hash::make`, cast `password => hashed`)
- **Middleware `auth`** : protège les routes qui nécessitent une connexion
- **Middleware `guest`** : empêche un utilisateur déjà connecté d'accéder aux pages login/register
- **Rate limiting** sur la connexion (protection anti brute-force, 5 tentatives)
- Protection **SQL Injection** native via Eloquent / Query Builder (requêtes préparées)
- Protection **XSS** native via l'échappement automatique de Blade (`{{ $variable }}`)

## 🔐 Sécurité (Sprint 2)

- **Validation stricte des uploads** : extension + MIME réel (`mimes:pdf,docx,xlsx,jpg,jpeg,png,zip`) et taille (`max:20480` Ko)
- **Noms de fichiers en UUID** : le nom physique sur le disque est un UUID v4, jamais devinable
- **Autorisation par Policy** (`FilePolicy`) : un utilisateur ne peut télécharger/partager/supprimer que ses propres fichiers (403 sinon)
- **Stockage sur disque privé** (`local`) : aucun fichier n'est exposé par une URL publique directe ; tout accès passe par un contrôleur qui vérifie les droits
- **Token de partage aléatoire** (`Str::random(40)`) : espace de recherche bien trop grand pour une attaque par force brute
- **Mot de passe de partage haché** (jamais stocké ni transmis en clair après création)
- **Expiration vérifiée côté serveur** à chaque accès (le lien "se ferme" automatiquement, aucune tâche planifiée requise)
- **CSRF** sur tous les formulaires d'upload, de suppression, de partage et de vérification de mot de passe

---

## 🧪 Lancer les tests

```bash
php artisan test
```
