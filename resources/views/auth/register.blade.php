@extends('layouts.guest')

@section('content')
    <h3 class="text-center mb-4">Creer un compte</h3>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label">Prenom</label>
                <input type="text"
                       class="form-control @error('first_name') is-invalid @enderror"
                       id="first_name" name="first_name" value="{{ old('first_name') }}"
                       required autofocus>
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text"
                       class="form-control @error('last_name') is-invalid @enderror"
                       id="last_name" name="last_name" value="{{ old('last_name') }}"
                       required>
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}"
                   required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password" name="password"
                       required autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input type="password"
                       class="form-control"
                       id="password_confirmation" name="password_confirmation"
                       required autocomplete="new-password">
            </div>
        </div>

        <div class="form-text mb-3">
            Le mot de passe doit contenir au moins 8 caracteres.
        </div>

        <button type="submit" class="btn btn-primary w-100">Creer mon compte</button>

        <p class="text-center small text-muted mt-4 mb-0">
            Vous avez deja un compte ?
            <a href="{{ route('login') }}">Se connecter</a>
        </p>
    </form>
@endsection
