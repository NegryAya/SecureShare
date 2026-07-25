@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Mes fichiers</h2>
        <a href="{{ route('files.upload') }}" class="btn btn-primary btn-sm">+ Uploader un fichier</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($files->isEmpty())
                <p class="text-muted mb-0">
                    Vous n'avez encore uploade aucun fichier.
                    <a href="{{ route('files.upload') }}">Uploader mon premier fichier</a>.
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
                                            <a href="{{ route('files.download', $file) }}" class="btn btn-outline-primary">
                                                Telecharger
                                            </a>

                                            <button type="button" class="btn btn-outline-success"
                                                    data-bs-toggle="modal" data-bs-target="#shareModal"
                                                    data-share-action="{{ route('files.share', $file) }}"
                                                    data-share-name="{{ $file->original_name }}">
                                                Partager
                                            </button>

                                            <form method="POST" action="{{ route('files.destroy', $file) }}"
                                                  onsubmit="return confirm('Supprimer definitivement « {{ $file->original_name }} » ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $files->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modale de creation d'un lien de partage --}}
    <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="shareForm" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Partager « <span id="shareFileName"></span> »</h5>
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
    // Remplit dynamiquement la modale de partage avec le fichier concerne.
    document.addEventListener('DOMContentLoaded', function () {
        const shareModal = document.getElementById('shareModal');
        shareModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('shareForm').setAttribute('action', button.getAttribute('data-share-action'));
            document.getElementById('shareFileName').textContent = button.getAttribute('data-share-name');
        });
    });
</script>
