@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Fichiers partages</h2>
        <a href="{{ route('files.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Mes fichiers</a>
    </div>

    @if (session('shared_link_url'))
        <div class="alert alert-success">
            <div class="fw-semibold mb-1">Lien de partage genere :</div>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('shared_link_url') }}" id="newShareLink" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyShareLink()">Copier</button>
            </div>
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
                                            <span class="badge bg-danger">Expire</span>
                                        @else
                                            <span class="badge bg-success">Actif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($link->hasPassword())
                                            <span class="badge bg-warning text-dark">Mot de passe</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Libre</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $link->expires_at ? $link->expires_at->format('d/m/Y H:i') : 'Jamais' }}
                                    </td>
                                    <td>{{ $link->downloads }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="navigator.clipboard.writeText('{{ $link->url }}')">
                                                Copier le lien
                                            </button>
                                            <form method="POST" action="{{ route('shared-links.destroy', $link) }}"
                                                  onsubmit="return confirm('Revoquer ce lien de partage ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">Revoquer</button>
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
