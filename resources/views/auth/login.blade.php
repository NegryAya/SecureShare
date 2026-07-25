@extends('layouts.guest')

@section('content')
    <h3 class="text-center mb-4">Connexion</h3>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password"
                   required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Se souvenir de moi</label>
            </div>
            <a href="{{ route('password.request') }}" class="small">Mot de passe oublie ?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Se connecter</button>

        <p class="text-center small text-muted mt-4 mb-0">
            Pas encore de compte ?
            <a href="{{ route('register') }}">Creer un compte</a>
        </p>
    </form>
@endsection
