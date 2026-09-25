@extends('layouts.organizer')

@section('title', 'Mijn spellen | QR-Game')

@section('organizer-content')
    <header class="dashboard-heading">
        <h1 class="page-title">Mijn spellen</h1>
        <p>Hier vind je de spellen die je zelf hebt aangemaakt.</p>

        <div class="form-actions">
            <a href="{{ route('organizer.games.create') }}" class="button">
                Nieuw spel
            </a>
        </div>
    </header>

    <section class="panel" aria-label="Mijn spellen">
        @forelse ($games as $game)
            @php
                $statusLabel = match ($game->status) {
                    'not_started' => 'Niet gestart',
                    'active' => 'Actief',
                    'paused' => 'Gepauzeerd',
                    'finished' => 'Gestopt',
                    default => 'Onbekend',
                };
            @endphp

            <article aria-labelledby="game-{{ $game->id }}">
                <h2 id="game-{{ $game->id }}">{{ $game->name }}</h2>

                <p>
                    Spelstatus:
                    <span class="game-status" data-status="{{ $game->status }}">
                        {{ $statusLabel }}
                    </span>
                </p>

                <p>
                    Vragen: <strong>{{ $game->questions_count }}</strong>
                    · Deelnemers: <strong>{{ $game->participants_count }}</strong>
                </p>
            </article>

            @unless ($loop->last)
                <hr>
            @endunless
        @empty
            <h2>Nog geen spellen</h2>
            <p>Klik op ‘Nieuw spel’ om je eerste spel aan te maken.</p>
        @endforelse
    </section>

    @if ($games->hasPages())
        <nav class="form-actions" aria-label="Pagina's met spellen">
            @if ($games->previousPageUrl())
                <a href="{{ $games->previousPageUrl() }}" class="button button-secondary" rel="prev">
                    Vorige
                </a>
            @endif

            <p>Pagina {{ $games->currentPage() }} van {{ $games->lastPage() }}</p>

            @if ($games->nextPageUrl())
                <a href="{{ $games->nextPageUrl() }}" class="button button-secondary" rel="next">
                    Volgende
                </a>
            @endif
        </nav>
    @endif
@endsection
