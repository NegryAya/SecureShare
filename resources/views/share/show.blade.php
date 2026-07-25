<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fichier partage - SecureShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}" class="text-decoration-none text-white fs-3 fw-bold">🔒 SecureShare</a>
                </div>

                <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                    <div class="card-body p-4 p-md-5 text-center">
                        @if ($needsPassword)
                            <h4 class="mb-3">🔐 Fichier protege</h4>
                            <p class="text-muted">Ce lien est protege par un mot de passe.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('share.verify', $sharedLink->token) }}">
                                @csrf
                                <div class="mb-3 text-start">
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="password" class="form-control" required autofocus>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Valider</button>
                            </form>
                        @else
                            <h4 class="mb-3">📄 {{ $sharedLink->file->original_name }}</h4>
                            <p class="text-muted mb-1">Taille : {{ $sharedLink->file->human_size }}</p>
                            <p class="text-muted mb-4">
                                Type : {{ strtoupper($sharedLink->file->extension) }}
                            </p>

                            <a href="{{ route('share.download', $sharedLink->token) }}" class="btn btn-success btn-lg w-100">
                                Telecharger le fichier
                            </a>

                            @if ($sharedLink->expires_at)
                                <p class="text-muted small mt-3 mb-0">
                                    Ce lien expire le {{ $sharedLink->expires_at->format('d/m/Y a H:i') }}.
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
