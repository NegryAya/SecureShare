<?php

return [

    /*
     * Guard par defaut utilise pour authentifier les requetes web (session).
     */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
     * Le guard "web" utilise le driver "session" et le provider "users"
     * (Eloquent, modele App\Models\User).
     */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
     * Provider Eloquent qui indique a Laravel quel modele representer
     * un utilisateur authentifiable.
     */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
     * Configuration de la reinitialisation de mot de passe
     * (table password_reset_tokens, expiration des liens en minutes).
     */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
     * Duree (en minutes) pendant laquelle un mot de passe confirme
     * reste valide avant d'etre redemande.
     */
    'password_timeout' => 10800,

];
