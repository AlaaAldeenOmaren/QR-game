@extends('layouts.organizer')

@section('title', 'Nieuw spel | QR-Game')

@section('organizer-content')
    <header class="dashboard-heading">
        <h1 class="page-title">Nieuw spel</h1>
        <p>Geef je spel een naam. Daarna kun je vragen toevoegen.</p>
    </header>

    <section class="panel" aria-label="Nieuw spel aanmaken">
        <form method="POST" action="{{ route('organizer.games.store') }}">
            @csrf

            <div class="form-field">
                <label for="name">Spelnaam</label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    maxlength="150"
                    aria-describedby="name-help"
                    @error('name') aria-invalid="true" @enderror
                    required
                >

                <p id="name-help">Bijvoorbeeld: Kennismaking eerstejaars.</p>
            </div>

            <p>Het spel krijgt de status ‘Niet gestart’. Je start het later via het dashboard.</p>

            <div class="form-actions">
                <button type="submit" class="button">Spel aanmaken</button>

                <a href="{{ route('organizer.games.index') }}" class="button button-secondary">
                    Annuleren
                </a>
            </div>
        </form>
    </section>
@endsection
