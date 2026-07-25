<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureShare - Partagez vos fichiers en toute securite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
            color: #fff;
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">🔒 SecureShare</h1>
        <p class="lead mb-4">
            Stockez, gerez et partagez vos fichiers en toute securite.
        </p>

        <div class="d-flex justify-content-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4">Mon tableau de bord</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4">Se connecter</a>
                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">Creer un compte</a>
            @endauth
        </div>
    </div>
</body>
</html>
