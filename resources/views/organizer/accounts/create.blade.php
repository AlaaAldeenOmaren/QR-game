@extends('layouts.organizer')

@section('title', 'Nieuw account | QR-Game')

@section('organizer-content')
    <header class="dashboard-heading">
        <h1 class="page-title">Nieuw organisatoraccount</h1>
        <p>Maak een account aan voor een organisator die eigen spellen gaat beheren.</p>
    </header>

    <section class="panel" aria-label="Organisatoraccount aanmaken">
        <form method="POST" action="{{ route('organizer.accounts.store') }}">
            @csrf

            <div class="form-field">
                <label for="name">Naam</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                    maxlength="100" autocomplete="off" required
                    @error('name') aria-invalid="true" @enderror>
            </div>

            <div class="form-field">
                <label for="email">E-mailadres</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    maxlength="255" autocomplete="off" spellcheck="false" required
                    @error('email') aria-invalid="true" @enderror>
            </div>

            <div class="form-field">
                <label for="password">Wachtwoord</label>
                <input id="password" name="password" type="password" minlength="12" maxlength="72"
                    autocomplete="new-password" aria-describedby="password-help" required
                    @error('password') aria-invalid="true" @enderror>
                <p id="password-help">Gebruik een wachtwoord van minimaal 12 tekens.</p>
            </div>

            <div class="form-field">
                <label for="password_confirmation">Herhaal het wachtwoord</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    minlength="12" maxlength="72" autocomplete="new-password" required>
            </div>

            <p>Dit account krijgt toegang tot eigen spellen. Het kan geen andere accounts aanmaken.</p>

            <div class="form-actions">
                <button type="submit" class="button">Account aanmaken</button>
                <a href="{{ route('organizer.accounts.index') }}" class="button button-secondary">Annuleren</a>
            </div>
        </form>
    </section>
@endsection
