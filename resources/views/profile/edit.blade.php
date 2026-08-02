@extends('layouts.app')

@section('content')
    <h2 class="mb-4">Mon profil</h2>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Informations personnelles</div>
                <div class="card-body">
                    @if ($errors->any() && old('first_name') !== null)
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Prenom</label>
                            <input type="text" name="first_name" class="form-control"
                                   value="{{ old('first_name', $user->first_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="last_name" class="form-control"
                                   value="{{ old('last_name', $user->last_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Changer le mot de passe</div>
                <div class="card-body">
                    @if ($errors->any() && old('first_name') === null)
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
