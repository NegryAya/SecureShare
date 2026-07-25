# Explication détaillée des fichiers créés — Sprint 1

## 1. Fichiers de base du projet

| Fichier | Rôle |
|---|---|
| `composer.json` | Déclare le projet et ses dépendances PHP (`laravel/framework`, `laravel/tinker`, outils de test). `composer install` lit ce fichier pour télécharger tout le framework. |
| `artisan` | Point d'entrée de la CLI Laravel (`php artisan migrate`, `php artisan serve`, etc.). |
| `bootstrap/app.php` | Cœur de la configuration Laravel 12 : déclare où se trouvent les routes (`routes/web.php`), configure les middlewares globaux et la gestion des exceptions. |
| `bootstrap/providers.php` | Liste les Service Providers de l'application (ici `AppServiceProvider`). |
| `public/index.php` | Front controller : **point d'entrée HTTP unique** de l'application. Toutes les requêtes passent par ce fichier. |
| `public/.htaccess` | Réécrit les URLs pour qu'Apache redirige tout vers `index.php` (nécessaire si vous utilisez Apache plutôt que `php artisan serve`). |
| `.env.example` | Modèle des variables d'environnement (base de données, mail, etc.) à copier en `.env`. |

## 2. Configuration (`config/`)

| Fichier | Rôle |
|---|---|
| `config/app.php` | Nom de l'app, timezone, locale (français), clé de chiffrement. |
| `config/auth.php` | Définit le **guard** `web` (authentification par session) et le **provider** Eloquent qui pointe vers `App\Models\User`. Définit aussi la config de reset de mot de passe (table, expiration 60 min). |
| `config/database.php` | Connexion **MySQL** par défaut, lit les variables `DB_*` du `.env`. |
| `config/filesystems.php` | Disques de stockage `local` et `public` — préparés pour le Sprint 2 (upload de fichiers). |
| `config/session.php` | Sessions stockées en base de données (table `sessions`), cookie sécurisé (`http_only`, `same_site=lax`). |
| `config/cache.php`, `config/queue.php` | Cache et files d'attente stockés en base de données. |
| `config/logging.php` | Où sont écrits les logs techniques Laravel (`storage/logs/laravel.log`) — à ne pas confondre avec la table métier `logs` créée pour le cahier des charges. |
| `config/mail.php` | Configuration de l'envoi d'email (utilisé par "mot de passe oublié"). |
| `config/services.php` | Configuration de services tiers (non utilisé au Sprint 1, présent par défaut). |

## 3. Base de données (`database/migrations/`)

Chaque migration est une classe PHP versionnée qui décrit une modification de
la base de données (`up()` = créer, `down()` = annuler). Exécutées par
`php artisan migrate`.

| Migration | Table créée | Détail |
|---|---|---|
| `..._create_users_table.php` | `users`, `password_reset_tokens`, `sessions` | `users` contient `first_name`, `last_name`, `email` (unique), `password` (haché), `role` (user/admin). |
| `..._create_cache_table.php` | `cache`, `cache_locks` | Requis car `CACHE_STORE=database`. |
| `..._create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` | Requis car `QUEUE_CONNECTION=database` (utile pour les futurs traitements en arrière-plan, ex: emails). |
| `..._create_files_table.php` | `files` | Structure prête pour le Sprint 2 : `user_id` (clé étrangère, suppression en cascade), `original_name`, `stored_name`, `extension`, `mime_type`, `size`, `storage_path`. |
| `..._create_shared_links_table.php` | `shared_links` | Structure prête pour le Sprint 3 : `file_id`, `token` unique. |
| `..._create_logs_table.php` | `logs` | Structure prête pour la journalisation : `user_id` (nullable), `action`, `ip_address`, `created_at`. |

## 4. Modèles Eloquent (`app/Models/`)

| Modèle | Rôle |
|---|---|
| `User.php` | Représente un utilisateur. Étend `Authenticatable` (nécessaire pour `Auth::login()`). Relations : `hasMany(File::class)`, `hasMany(Log::class)`. Méthode `isAdmin()` et accesseur `full_name`. Le mot de passe est automatiquement haché grâce au cast `'password' => 'hashed'`. |
| `File.php` | Représente un fichier. Relation `belongsTo(User::class)` et `hasMany(SharedLink::class)`. **Prêt pour le Sprint 2**, aucune logique d'upload ici. |
| `SharedLink.php` | Représente un lien de partage. Relation `belongsTo(File::class)`. **Prêt pour le Sprint 3**. |
| `Log.php` | Représente une entrée du journal d'audit. Contient des constantes (`ACTION_LOGIN`, `ACTION_LOGOUT`, ...) et une méthode statique `Log::record()` utilisée par les contrôleurs d'authentification pour journaliser chaque connexion/déconnexion. |

## 5. Authentification (`app/Http/Requests/Auth/` et `app/Http/Controllers/Auth/`)

Le principe : **Form Request** = validation, **Controller** = logique métier.
Cela garde les contrôleurs courts et centralise les règles de validation.

| Fichier | Rôle |
|---|---|
| `Requests/Auth/RegisterRequest.php` | Valide prénom, nom, email (unique), mot de passe (confirmé, règles de complexité Laravel). Messages d'erreur en français. |
| `Requests/Auth/LoginRequest.php` | Valide email/mot de passe et implémente le **rate limiting** : après 5 tentatives échouées, bloque temporairement les nouvelles tentatives (protection anti brute-force). |
| `Controllers/Auth/RegisteredUserController.php` | `create()` affiche le formulaire d'inscription. `store()` crée l'utilisateur (mot de passe haché via `Hash::make`), le connecte automatiquement et l'envoie vers le dashboard. |
| `Controllers/Auth/AuthenticatedSessionController.php` | `create()` affiche le formulaire de connexion. `store()` authentifie via `LoginRequest::authenticate()` et régénère la session (sécurité anti session-fixation). `destroy()` déconnecte, invalide la session et régénère le token CSRF. Chaque login/logout est journalisé dans la table `logs`. |
| `Controllers/Auth/PasswordResetLinkController.php` | Gère le formulaire "mot de passe oublié" : envoie un email contenant un lien de réinitialisation (via le broker Laravel `Password::sendResetLink`). |
| `Controllers/Auth/NewPasswordController.php` | Affiche le formulaire accessible depuis le lien reçu par email, valide le token, et enregistre le nouveau mot de passe (haché). |

## 6. Dashboard (`app/Http/Controllers/DashboardController.php`)

Contrôleur protégé par le middleware `auth`. Calcule les statistiques de
l'utilisateur connecté (`files_count`, `total_size`, `shared_count`,
`recent_files`) directement depuis les relations Eloquent du modèle `User`.
Comme aucun fichier n'existe encore au Sprint 1, ces valeurs afficheront 0 —
mais **le code est déjà prêt** : dès que le Sprint 2 ajoutera la
fonctionnalité d'upload, le dashboard affichera automatiquement les vraies
données, sans aucune modification nécessaire.

## 7. Routes (`routes/web.php`)

Toutes les routes sont déclarées ici, organisées en deux groupes de
middleware :
- **`guest`** : routes accessibles uniquement si l'utilisateur n'est **pas**
  connecté (register, login, forgot-password). Redirige vers `/dashboard`
  sinon.
- **`auth`** : routes accessibles uniquement si l'utilisateur **est**
  connecté (dashboard, logout). Redirige vers `/login` sinon.

Cette séparation garantit qu'un utilisateur connecté ne peut pas retourner
sur la page de login, et qu'un visiteur non connecté ne peut pas accéder au
dashboard.

## 8. Vues Blade (`resources/views/`)

| Fichier | Rôle |
|---|---|
| `layouts/guest.blade.php` | Layout centré (carte Bootstrap) utilisé par toutes les pages d'authentification. |
| `layouts/app.blade.php` | Layout avec barre de navigation (nom de l'utilisateur connecté + bouton déconnexion), utilisé par le dashboard. |
| `welcome.blade.php` | Page d'accueil publique avec boutons "Se connecter" / "Créer un compte" (ou "Mon tableau de bord" si déjà connecté). |
| `auth/login.blade.php` | Formulaire de connexion (email, mot de passe, "se souvenir de moi", lien mot de passe oublié). |
| `auth/register.blade.php` | Formulaire d'inscription (prénom, nom, email, mot de passe + confirmation). |
| `auth/forgot-password.blade.php` | Formulaire pour demander un lien de réinitialisation. |
| `auth/reset-password.blade.php` | Formulaire pour définir un nouveau mot de passe (pré-rempli avec le token/email reçus par lien). |
| `dashboard/index.blade.php` | Tableau de bord : cartes de statistiques + tableau des derniers fichiers (vide au Sprint 1). |

Toutes les vues sont **responsive** (grille Bootstrap 5) et affichent les
erreurs de validation (`@error`) ainsi que les messages de succès
(`session('status')`).

## 9. Tests (`tests/Feature/AuthenticationTest.php`)

Suite de tests PHPUnit qui vérifie automatiquement :
- l'accessibilité des pages register/login,
- la création de compte,
- la connexion avec bons/mauvais identifiants,
- la protection du dashboard pour les invités,
- la déconnexion.

Lancer avec : `php artisan test`

## 10. Sécurité appliquée dans ce Sprint

- **CSRF** : chaque formulaire contient `@csrf`, vérifié automatiquement par
  le middleware `web`.
- **Validation stricte** via Form Requests (jamais de données brutes
  utilisées sans validation).
- **Hashage bcrypt** des mots de passe (jamais stockés en clair).
- **Middleware `auth`/`guest`** pour contrôler l'accès aux pages.
- **Rate limiting** sur le login (anti brute-force).
- **Requêtes préparées** via Eloquent (protection SQL Injection native).
- **Échappement automatique** des variables dans Blade (`{{ }}`) (protection XSS native).
- **Régénération de session** après login/logout (anti session-fixation).

---

# Sprint 2 — Gestion sécurisée des fichiers

## 1. Base de données

| Fichier | Rôle |
|---|---|
| `database/migrations/2025_02_01_000006_add_security_fields_to_shared_links_table.php` | Migration **additive** (n'a pas modifié celle du Sprint 1) qui ajoute à `shared_links` : `password` (haché, nullable), `expires_at` (nullable), `downloads` (compteur, défaut 0). |
| `database/factories/FileFactory.php`, `SharedLinkFactory.php` | Fabriques utilisées par les tests automatisés pour générer des fichiers/liens de test rapidement. |

La table `files` du Sprint 1 couvrait déjà exactement les besoins du Sprint 2
(`user_id`, `original_name`, `stored_name`, `mime_type`, `size`,
`storage_path`) : aucune modification n'était nécessaire.

## 2. Modèles (`app/Models/`)

| Fichier | Ajouts Sprint 2 |
|---|---|
| `File.php` | Constante `DISK` (disque de stockage privé), accesseur `human_size` (Ko/Mo/Go lisible), méthode `activeSharedLink()`. Les champs et relations du Sprint 1 sont inchangés. |
| `SharedLink.php` | Reécrit pour ajouter : `isExpired()`, `isActive()`, `hasPassword()`, `checkPassword()` (vérifie le hash), `incrementDownloads()`, accesseur `url` (URL publique complète). Le mot de passe est cast en `hidden` pour ne jamais apparaître dans un `toArray()/toJson()`. |

## 3. Autorisation (`app/Policies/FilePolicy.php`)

Implémente la règle centrale du Sprint 2 : **chaque utilisateur ne peut
agir que sur ses propres fichiers**. Trois règles : `view` (téléchargement),
`share` (création de lien), `delete` (suppression) — toutes vérifient
`$user->id === $file->user_id`. Enregistrée dans `AppServiceProvider::boot()`
via `Gate::policy(File::class, FilePolicy::class)`.

Le contrôleur de base (`app/Http/Controllers/Controller.php`) a été
complété avec le trait `AuthorizesRequests` (retiré par défaut du squelette
minimal Laravel 12) afin de permettre `$this->authorize(...)` dans les
contrôleurs.

## 4. Validation (`app/Http/Requests/`)

| Fichier | Rôle |
|---|---|
| `UploadFileRequest.php` | Valide le fichier envoyé : `mimes:pdf,docx,xlsx,jpg,jpeg,png,zip` (vérifie l'extension **et** le vrai type MIME déduit du contenu binaire) + `max:20480` (20 Mo). Messages d'erreur en français. |
| `CreateShareLinkRequest.php` | Valide les options de partage : mot de passe optionnel (`nullable`, 4 caractères min), durée d'expiration (`none`/`24h`/`7d`). |

## 5. Contrôleurs (`app/Http/Controllers/`)

| Fichier | Rôle |
|---|---|
| `FileController.php` | `index()` liste paginée des fichiers ("Mes fichiers"). `create()` affiche le formulaire d'upload. `store()` : renomme le fichier en **UUID**, le stocke sur le disque privé `local` dans `files/{user_id}/`, enregistre les métadonnées, journalise `upload`. `download()` : vérifie la Policy `view`, journalise `download`, renvoie le fichier en streaming avec son nom d'origine. `destroy()` : vérifie la Policy `delete`, supprime le fichier physique **et** la ligne en base (les liens de partage associés sont supprimés automatiquement via `cascadeOnDelete`), journalise `delete`. |
| `SharedLinkController.php` | `index()` liste les liens créés par l'utilisateur ("Fichiers partagés"). `store()` : vérifie la Policy `share`, génère un token aléatoire de 40 caractères (`Str::random(40)`), hache le mot de passe s'il est fourni, calcule `expires_at` selon le choix (24h/7j/jamais), journalise `share`. `destroy()` : révoque (supprime) un lien de partage. |
| `ShareController.php` | Contrôleur **public** (aucune authentification) qui gère `/share/{token}` : `show()` affiche la page du fichier ou un formulaire de mot de passe ; `verifyPassword()` vérifie le mot de passe et mémorise la validation en session (le temps de la visite) ; `download()` vérifie que le lien n'est pas expiré et que le mot de passe (si requis) a été validé, incrémente le compteur de téléchargements, journalise `download` (avec `user_id = null`, car l'action est anonyme). |

## 6. Routes (`routes/web.php`)

Trois nouveaux blocs, ajoutés **sans modifier** les routes du Sprint 1 :
- Routes **publiques** `share.show` / `share.verify` / `share.download` (ni `guest` ni `auth`, accessibles à quiconque a le lien).
- Routes **protégées** (`auth`) : `files.index`, `files.upload`, `files.store`, `files.download`, `files.destroy`.
- Routes **protégées** (`auth`) : `shared-links.index`, `files.share`, `shared-links.destroy`.

## 7. Vues (`resources/views/`)

| Fichier | Rôle |
|---|---|
| `files/upload.blade.php` | Formulaire d'upload (`enctype="multipart/form-data"`). |
| `files/index.blade.php` | Tableau "Mes fichiers" avec actions Télécharger / Partager (ouvre une modale Bootstrap avec choix du mot de passe et de l'expiration) / Supprimer (confirmation JS). |
| `shared-links/index.blade.php` | Liste des liens créés (statut actif/expiré, protection, expiration, nombre de téléchargements, copie du lien, révocation). Affiche aussi le lien fraîchement généré après création. |
| `share/show.blade.php` | Page **publique** : formulaire de mot de passe si nécessaire, sinon informations du fichier + bouton de téléchargement. |
| `share/expired.blade.php` | Page affichée quand le lien a expiré. |
| `layouts/app.blade.php` | Navbar complétée avec les liens "Mes fichiers", "Upload", "Fichiers partagés". |
| `dashboard/index.blade.php` | Ajout de boutons rapides "Uploader" / "Mes fichiers", et lien direct vers l'upload si aucun fichier n'existe encore. |

## 8. Tests (`tests/Feature/`)

| Fichier | Couverture |
|---|---|
| `FileManagementTest.php` | Upload valide/refusé (extension, taille), téléchargement (propriétaire vs. intrus → 403), suppression (base + disque), autorisation. |
| `ShareLinkTest.php` | Création de lien (propriétaire vs. intrus → 403), affichage public, lien expiré, protection par mot de passe (mauvais/bon mot de passe), incrémentation du compteur et journalisation du téléchargement. |

Lancer avec : `php artisan test`

## 9. Points de sécurité du Sprint 2 (checklist du cahier des charges)

| Exigence | Implémentation |
|---|---|
| ✅ Validation | `UploadFileRequest`, `CreateShareLinkRequest` |
| ✅ Authorization | `FilePolicy` (`view`/`share`/`delete`) |
| ✅ UUID | Nom de fichier physique = `Str::uuid()` |
| ✅ Token Random | `Str::random(40)` pour chaque lien de partage |
| ✅ CSRF | `@csrf` sur tous les formulaires (upload, delete, share, password) |
| ✅ File Size Validation | Règle `max:20480` (20 Mo) |
| ✅ MIME Validation | Règle `mimes:` (vérifie le type réel, pas seulement l'extension) |
