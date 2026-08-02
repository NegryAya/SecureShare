<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SharedLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Page d'accueil (publique)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Routes publiques de partage (Sprint 2)
|--------------------------------------------------------------------------
| Accessibles a QUICONQUE possede le lien, sans authentification, qu'il
| soit connecte ou non (donc pas de middleware "guest" ni "auth" ici).
*/
Route::get('share/{token}', [ShareController::class, 'show'])
    ->name('share.show');
Route::post('share/{token}', [ShareController::class, 'verifyPassword'])
    ->name('share.verify');
Route::get('share/{token}/download', [ShareController::class, 'download'])
    ->name('share.download');

/*
|--------------------------------------------------------------------------
| Routes invite (accessibles uniquement si l'utilisateur N'EST PAS connecte)
|--------------------------------------------------------------------------
| Le middleware "guest" redirige automatiquement vers /dashboard si
| l'utilisateur est deja authentifie.
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Routes protegees (utilisateur authentifie uniquement)
|--------------------------------------------------------------------------
| Le middleware "auth" redirige vers /login si l'utilisateur n'est pas
| connecte. Chaque utilisateur ne peut acceder qu'a son propre espace.
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Gestion des fichiers (Sprint 2)
    |----------------------------------------------------------------------
    */
    Route::get('files', [FileController::class, 'index'])
        ->name('files.index');
    Route::get('files/upload', [FileController::class, 'create'])
        ->name('files.upload');
    Route::post('files', [FileController::class, 'store'])
        ->name('files.store');
    Route::get('files/{file}/download', [FileController::class, 'download'])
        ->name('files.download');
    Route::delete('files/{file}', [FileController::class, 'destroy'])
        ->name('files.destroy');
    Route::put('files/{file}/rename', [FileController::class, 'rename'])
        ->name('files.rename');
    Route::put('files/{file}/replace', [FileController::class, 'replace'])
        ->name('files.replace');

    /*
    |----------------------------------------------------------------------
    | Partage de fichiers (Sprint 2) - cote proprietaire
    |----------------------------------------------------------------------
    */
    Route::get('shared-links', [SharedLinkController::class, 'index'])
        ->name('shared-links.index');
    Route::post('files/{file}/share', [SharedLinkController::class, 'store'])
        ->name('files.share');
    Route::delete('shared-links/{sharedLink}', [SharedLinkController::class, 'destroy'])
        ->name('shared-links.destroy');

    /*
    |----------------------------------------------------------------------
    | Profil utilisateur (Sprint 3)
    |----------------------------------------------------------------------
    */
    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    /*
    |----------------------------------------------------------------------
    | Historique d'activite (Sprint 3)
    |----------------------------------------------------------------------
    */
    Route::get('activity', [ActivityLogController::class, 'index'])
        ->name('activity.index');
});
