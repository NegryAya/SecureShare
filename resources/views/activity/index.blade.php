@extends('layouts.app')

@section('content')
    <h2 class="mb-4">Historique d'activite</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($logs->isEmpty())
                <p class="text-muted mb-0">Aucune activite enregistree pour le moment.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Adresse IP</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $labels = [
                                    'login' => ['Connexion', 'bg-primary'],
                                    'logout' => ['Deconnexion', 'bg-secondary'],
                                    'upload' => ['Upload', 'bg-success'],
                                    'download' => ['Telechargement', 'bg-info text-dark'],
                                    'delete' => ['Suppression', 'bg-danger'],
                                    'share' => ['Partage', 'bg-warning text-dark'],
                                ];
                            @endphp
                            @foreach ($logs as $log)
                                @php [$label, $badge] = $labels[$log->action] ?? [ucfirst($log->action), 'bg-light text-dark border']; @endphp
                                <tr>
                                    <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                                    <td>{{ $log->ip_address ?? '—' }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
