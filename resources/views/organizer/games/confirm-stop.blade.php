@extends('layouts.organizer')

@section('title', 'Spel stoppen | QR-Game')

@section('organizer-content')
    <section class="panel confirmation-panel" aria-labelledby="stop-title">
        <p class="eyebrow">BEHEER</p>

        <h1 id="stop-title" class="page-title">Spel stoppen?</h1>

        <p>
            Je staat op het punt om
            <strong>{{ $game->name }}</strong>
            te stoppen.
        </p>

        <p>
            Studenten kunnen daarna geen antwoorden meer versturen.
            De antwoorden en punten blijven bewaard.
        </p>

        <p>
            Een gestopt spel kan niet worden hervat.
            Wil je tijdelijk onderbreken? Kies dan voor pauzeren.
        </p>

        <div class="confirmation-actions">
            <form
                method="POST"
                action="{{ route('organizer.games.state', $game) }}"
            >
                @csrf

                <input type="hidden" name="action" value="stop">

                <button
                    type="submit"
                    class="button button-danger button-wide"
                >
                    Stoppen bevestigen
                </button>
            </form>

            <a
                href="{{ route('dashboard') }}"
                class="button button-secondary button-wide"
            >
                Annuleren
            </a>
        </div>
    </section>
@endsection
