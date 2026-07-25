<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lien expire - SecureShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 text-center">
                <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                    <div class="card-body p-5">
                        <h2 class="mb-3">⏱️ Lien expire</h2>
                        <p class="text-muted mb-4">
                            Ce lien de partage n'est plus valide. Demandez au
                            proprietaire du fichier de generer un nouveau lien.
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-primary">Retour a l'accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
