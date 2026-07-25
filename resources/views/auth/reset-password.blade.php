@extends('layouts.guest')

@section('content')
    <h3 class="text-center mb-4">Reinitialiser le mot de passe</h3>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email', $email) }}"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <input type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password"
                   required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input type="password"
                   class="form-control"
                   id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Reinitialiser le mot de passe</button>
    </form>
@endsection
