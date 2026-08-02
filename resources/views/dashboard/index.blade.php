@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Tableau de bord</h2>
            <p class="text-muted mb-0">Bienvenue, {{ auth()->user()->full_name }} 👋</p>
        </div>
        <div class="d-none d-md-flex gap-2">
            <a href="{{ route('files.upload') }}" class="btn btn-primary btn-sm">+ Uploader</a>
            <a href="{{ route('files.index') }}" class="btn btn-outline-secondary btn-sm">Mes fichiers</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-3 text-primary"><i class="bi bi-files"></i></div>
                    <div>
                        <div class="text-muted small">Nombre de fichiers</div>
                        <div class="fs-4 fw-bold">{{ $stats['files_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-3 text-secondary"><i class="bi bi-hdd"></i></div>
                    <div>
                        <div class="text-muted small">Taille totale</div>
                        <div class="fs-4 fw-bold">
                            {{ $stats['total_size'] ? number_format($stats['total_size'] / 1048576, 2) : 0 }} Mo
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-3 text-success"><i class="bi bi-link-45deg"></i></div>
                    <div>
                        <div class="text-muted small">Liens generes</div>
                        <div class="fs-4 fw-bold">{{ $stats['links_total'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-3 text-success"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="text-muted small">Liens actifs</div>
                        <div class="fs-4 fw-bold">{{ $stats['links_active'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-3 text-danger"><i class="bi bi-x-circle"></i></div>
                    <div>
                        <div class="text-muted small">Liens expires</div>
                        <div class="fs-4 fw-bold">{{ $stats['links_expired'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-3 text-muted"><i class="bi bi-person-circle"></i></div>
                    <div>
                        <div class="text-muted small">Compte</div>
                        <div class="fs-6 fw-semibold text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history me-1"></i> Derniers fichiers</div>
        <div class="card-body">
            @if ($stats['recent_files']->isEmpty())
                <p class="text-muted mb-0">
                    Vous n'avez encore aucun fichier.
                    <a href="{{ route('files.upload') }}">Uploader mon premier fichier</a>.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Taille</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stats['recent_files'] as $file)
                                <tr>
                                    <td class="text-break">{{ $file->original_name }}</td>
                                    <td><span class="badge bg-secondary">{{ strtoupper($file->extension) }}</span></td>
                                    <td>{{ $file->human_size }}</td>
                                    <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <a href="{{ route('files.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tous mes fichiers <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
