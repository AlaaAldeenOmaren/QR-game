@extends('layouts.organizer')

@section('title', 'Dashboard | QR-Game')

@section('organizer-content')
    <header class="dashboard-heading">
        <h1 class="page-title">Dashboard</h1>
        <p>Welkom, {{ auth()->user()->name }}.</p>
    </header>

    @if ($game)
        <section class="panel" aria-labelledby="game-title">
            <p class="eyebrow">SPELOVERZICHT</p>

            <h2 id="game-title">{{ $game->name }}</h2>

            <p>Spelstatus</p>

            <span class="game-status" data-status="{{ $game->status }}">
                {{ $statusLabel }}
            </span>

            <div class="game-actions">
                <form method="POST" action="{{ route('organizer.games.state', $game) }}">
                    @csrf

                    <input type="hidden" name="action" value="start">

                    <button type="submit" class="button" @disabled($game->status !== 'not_started' || $game->questions_count < 1)>
                        Spel starten
                    </button>
                </form>

                <form method="POST" action="{{ route('organizer.games.state', $game) }}">
                    @csrf

                    <input type="hidden" name="action" value="{{ $game->status === 'active' ? 'pause' : 'resume' }}">

                    <button type="submit" class="button" @disabled(!in_array($game->status, ['active', 'paused'], true))>
                        {{ $game->status === 'active' ? 'Pauzeren' : 'Hervatten' }}
                    </button>
                </form>

                @if (in_array($game->status, ['active', 'paused'], true))
                    <a href="{{ route('organizer.games.stop.confirm', $game) }}" class="button button-danger">
                        Stoppen
                    </a>
                @else
                    <button type="button" class="button button-danger" disabled>
                        Stoppen
                    </button>
                @endif
            </div>

            @if ($game->status === 'not_started' && $game->questions_count < 1)
                <p>Voeg eerst een vraag toe om het spel te starten.</p>
            @endif

            @if ($game->status === 'finished')
                <p>Het spel is afgelopen. Antwoorden en punten blijven bewaard.</p>
            @endif
        </section>

        <dl class="dashboard-stats">
            <div class="panel">
                <dt>Vragen</dt>
                <dd>{{ $game->questions_count }}</dd>
            </div>

            <div class="panel">
                <dt>Deelnemers</dt>
                <dd>{{ $game->participants_count }}</dd>
            </div>

            <div class="panel">
                <dt>Nog na te kijken</dt>
                <dd>{{ $game->pending_answers_count }}</dd>
            </div>
        </dl>
    @else
        <section class="panel">
            <h2>Geen spel gevonden</h2>
            <p>Er is nog geen spel aan jouw account gekoppeld.</p>
        </section>
    @endif
@endsection
