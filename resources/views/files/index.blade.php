@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Mes fichiers</h2>
        <a href="{{ route('files.upload') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-cloud-upload"></i> Uploader un fichier
        </a>
    </div>

    {{-- Barre de recherche / filtre / tri (Sprint 3) --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('files.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted mb-1">Rechercher par nom</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="ex : rapport.pdf" value="{{ $filters['search'] }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Type de fichier</label>
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach ($availableTypes as $type)
                            <option value="{{ $type }}" @selected($filters['type'] === $type)>
                                {{ strtoupper($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Trier par</label>
                    <select name="sort" class="form-select">
                        <option value="date_desc" @selected($filters['sort'] === 'date_desc')>Plus recent</option>
                        <option value="date_asc" @selected($filters['sort'] === 'date_asc')>Plus ancien</option>
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Nom (A-Z)</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Nom (Z-A)</option>
                        <option value="size_desc" @selected($filters['sort'] === 'size_desc')>Taille (plus gros)</option>
                    </select>
                </div>

                <div class="col-12 col-md-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary" title="Appliquer">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </form>

            @if ($filters['search'] || $filters['type'])
                <div class="mt-2">
                    <a href="{{ route('files.index') }}" class="small text-decoration-none">
                        <i class="bi bi-x-circle"></i> Reinitialiser les filtres
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($files->isEmpty())
                <p class="text-muted mb-0">
                    @if ($filters['search'] || $filters['type'])
                        <i class="bi bi-info-circle"></i> Aucun fichier ne correspond a votre recherche.
                        <a href="{{ route('files.index') }}">Reinitialiser les filtres</a>.
                    @else
                        Vous n'avez encore uploade aucun fichier.
                        <a href="{{ route('files.upload') }}">Uploader mon premier fichier</a>.
                    @endif
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Taille</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($files as $file)
                                <tr>
                                    <td class="text-break">{{ $file->original_name }}</td>
                                    <td>{{ $file->human_size }}</td>
                                    <td><span class="badge bg-secondary">{{ strtoupper($file->extension) }}</span></td>
                                    <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('files.download', $file) }}" class="btn btn-outline-primary" title="Telecharger">
                                                <i class="bi bi-download"></i>
                                            </a>

                                            <button type="button" class="btn btn-outline-secondary" title="Renommer"
                                                    data-bs-toggle="modal" data-bs-target="#renameModal"
                                                    data-rename-action="{{ route('files.rename', $file) }}"
                                                    data-rename-name="{{ $file->original_name }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button type="button" class="btn btn-outline-warning" title="Remplacer le contenu"
                                                    data-bs-toggle="modal" data-bs-target="#replaceModal"
                                                    data-replace-action="{{ route('files.replace', $file) }}"
                                                    data-replace-name="{{ $file->original_name }}">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>

                                            <button type="button" class="btn btn-outline-success" title="Partager"
                                                    data-bs-toggle="modal" data-bs-target="#shareModal"
                                                    data-share-action="{{ route('files.share', $file) }}"
                                                    data-share-name="{{ $file->original_name }}">
                                                <i class="bi bi-share"></i>
                                            </button>

                                            <form method="POST" action="{{ route('files.destroy', $file) }}"
                                                  onsubmit="return confirm('Supprimer definitivement « {{ $file->original_name }} » ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <span class="text-muted small">
                        {{ $files->total() }} fichier(s) au total
                    </span>
                    {{ $files->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modale : renommer un fichier --}}
    <div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="renameForm" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Renommer le fichier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Nouveau nom</label>
                        <input type="text" name="name" id="renameInput" class="form-control" required maxlength="245">
                        <div class="form-text">L'extension du fichier est conservee automatiquement.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Renommer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modale : remplacer le contenu d'un fichier --}}
    <div class="modal fade" id="replaceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="replaceForm" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Remplacer « <span id="replaceFileName"></span> »</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Nouveau fichier</label>
                        <input type="file" name="file" class="form-control" required>
                        <div class="form-text">
                            Le fichier actuel sera remplace par ce nouveau contenu (meme enregistrement,
                            les liens de partage existants restent valides). Formats acceptes : PDF, DOCX, XLSX, JPG, PNG, ZIP — 20 Mo max.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Remplacer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modale de creation d'un lien de partage --}}
    <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="shareForm" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-share"></i> Partager « <span id="shareFileName"></span> »</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Duree d'expiration</label>
                            <select name="expires_in" class="form-select" required>
                                <option value="24h">24 heures</option>
                                <option value="7d">7 jours</option>
                                <option value="none">Sans expiration</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe (optionnel)</label>
                            <input type="text" name="password" class="form-control" placeholder="Laisser vide = pas de mot de passe" minlength="4">
                            <div class="form-text">Toute personne possedant le lien devra saisir ce mot de passe pour telecharger le fichier.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Generer le lien</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modale de partage (Sprint 2, inchangee).
        const shareModal = document.getElementById('shareModal');
        shareModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('shareForm').setAttribute('action', button.getAttribute('data-share-action'));
            document.getElementById('shareFileName').textContent = button.getAttribute('data-share-name');
        });

        // Modale de renommage (Sprint 3).
        const renameModal = document.getElementById('renameModal');
        renameModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('renameForm').setAttribute('action', button.getAttribute('data-rename-action'));
            document.getElementById('renameInput').value = button.getAttribute('data-rename-name');
        });

        // Modale de remplacement (Sprint 3).
        const replaceModal = document.getElementById('replaceModal');
        replaceModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('replaceForm').setAttribute('action', button.getAttribute('data-replace-action'));
            document.getElementById('replaceFileName').textContent = button.getAttribute('data-replace-name');
        });
    });
</script>
