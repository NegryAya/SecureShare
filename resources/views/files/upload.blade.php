@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Uploader un fichier</h2>
        <a href="{{ route('files.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Mes fichiers</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="file" class="form-label">Choisir un fichier</label>
                    <input type="file" class="form-control" id="file" name="file" required>
                    <div class="form-text">
                        Formats acceptes : PDF, DOCX, XLSX, JPG, PNG, ZIP — taille maximale : 20 Mo.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Uploader</button>
            </form>
        </div>
    </div>
@endsection
