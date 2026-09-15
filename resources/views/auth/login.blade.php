@extends('layouts.app')

@section('title', 'Inloggen | QR-Game')

@section('content')
    <section class="auth-page" aria-labelledby="login-title">
        <div class="panel">
            <p class="eyebrow">BEHEER</p>

            <h1 class="page-title" id="login-title">
                Inloggen als organisator
            </h1>

            <p>Log in met je beheerdersaccount.</p>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="form-field">
                    <label for="email">E-mailadres</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        maxlength="255"
                        required
                        @error('email')
                            aria-invalid="true"
                            aria-describedby="email-error"
                        @enderror
                    >

                    @error('email')
                        <p class="form-error" id="email-error" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password">Wachtwoord</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        @error('password')
                            aria-invalid="true"
                            aria-describedby="password-error"
                        @enderror
                    >

                    @error('password')
                        <p class="form-error" id="password-error" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button class="button button-wide" type="submit">
                    Inloggen
                </button>
            </form>

            <p>
                <a href="{{ route('home') }}">Terug naar home</a>
            </p>
        </div>
    </section>
@endsection
