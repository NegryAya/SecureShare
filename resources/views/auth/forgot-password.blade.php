@extends('layouts.guest')

@section('content')
    <h3 class="text-center mb-3">Mot de passe oublie</h3>
    <p class="text-muted small text-center mb-4">
        Indiquez votre adresse email : nous vous enverrons un lien pour
        reinitialiser votre mot de passe.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}"
                   required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Envoyer le lien de reinitialisation</button>

        <p class="text-center small text-muted mt-4 mb-0">
            <a href="{{ route('login') }}">&larr; Retour a la connexion</a>
        </p>
    </form>
@endsection
