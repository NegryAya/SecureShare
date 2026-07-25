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
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Nombre de fichiers</div>
                    <div class="fs-3 fw-bold">{{ $stats['files_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Taille totale</div>
                    <div class="fs-3 fw-bold">
                        {{ $stats['total_size'] ? number_format($stats['total_size'] / 1048576, 2) : 0 }} Mo
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Fichiers partages</div>
                    <div class="fs-3 fw-bold">{{ $stats['shared_count'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Compte</div>
                    <div class="fs-6 fw-semibold text-truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Derniers fichiers</div>
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
                                    <td>{{ $file->original_name }}</td>
                                    <td>{{ strtoupper($file->extension) }}</td>
                                    <td>{{ number_format($file->size / 1024, 1) }} Ko</td>
                                    <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
