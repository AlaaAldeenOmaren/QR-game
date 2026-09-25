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
        @forelse ($games as $listedGame)
            @php
                $statusLabel = match ($listedGame->status) {
                    'not_started' => 'Niet gestart',
                    'active' => 'Actief',
                    'paused' => 'Gepauzeerd',
                    'finished' => 'Gestopt',
                    default => 'Onbekend',
                };
            @endphp

            <article aria-labelledby="game-{{ $listedGame->id }}">
                <h2 id="game-{{ $listedGame->id }}">{{ $listedGame->name }}</h2>

                @if ((string) $selectedGame?->id === (string) $listedGame->id)
                    <p><strong>Geopend in beheer</strong></p>
                @endif

                <p>
                    Spelstatus:
                    <span class="game-status" data-status="{{ $listedGame->status }}">
                        {{ $statusLabel }}
                    </span>
                </p>

                <p>
                    Vragen: <strong>{{ $listedGame->questions_count }}</strong>
                    · Deelnemers: <strong>{{ $listedGame->participants_count }}</strong>
                </p>

                <form method="POST" action="{{ route('organizer.games.select', ['game' => $listedGame]) }}">
                    @csrf

                    <div class="form-actions">
                        <button type="submit" class="button" aria-label="Beheer {{ $listedGame->name }}">
                            Beheren
                        </button>
                    </div>
                </form>
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
