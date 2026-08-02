@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-share-fill me-2"></i>Fichiers partages</h2>
        <a href="{{ route('files.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Mes fichiers
        </a>
    </div>

    @if (session('shared_link_url'))
        <div class="alert alert-success alert-dismissible fade show">
            <div class="fw-semibold mb-1"><i class="bi bi-check-circle"></i> Lien de partage genere :</div>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('shared_link_url') }}" id="newShareLink" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyShareLink()">
                    <i class="bi bi-clipboard"></i> Copier
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($sharedLinks->isEmpty())
                <p class="text-muted mb-0">
                    Vous n'avez encore genere aucun lien de partage. Rendez-vous
                    dans <a href="{{ route('files.index') }}">Mes fichiers</a> et
                    cliquez sur « Partager ».
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fichier</th>
                                <th>Statut</th>
                                <th>Protection</th>
                                <th>Expiration</th>
                                <th>Telechargements</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sharedLinks as $link)
                                <tr>
                                    <td class="text-break">{{ $link->file->original_name }}</td>
                                    <td>
                                        @if ($link->isExpired())
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Expire</span>
                                        @else
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Actif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($link->hasPassword())
                                            <span class="badge bg-warning text-dark"><i class="bi bi-lock-fill"></i> Mot de passe</span>
                                        @else
                                            <span class="badge bg-light text-dark border"><i class="bi bi-unlock"></i> Libre</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $link->expires_at ? $link->expires_at->format('d/m/Y H:i') : 'Jamais' }}
                                    </td>
                                    <td>{{ $link->downloads }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" title="Copier le lien"
                                                    onclick="navigator.clipboard.writeText('{{ $link->url }}')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                            <form method="POST" action="{{ route('shared-links.destroy', $link) }}"
                                                  onsubmit="return confirm('Revoquer ce lien de partage ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Revoquer">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $sharedLinks->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

<script>
    function copyShareLink() {
        const input = document.getElementById('newShareLink');
        input.select();
        navigator.clipboard.writeText(input.value);
    }
</script>
