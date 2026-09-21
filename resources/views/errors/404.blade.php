@extends('layouts.app')

@section('content')
    <div class="player-shell">
        @if ($isQuestionLink && $game && $participant)
            @include('student.partials.nav')
        @endif

        <section class="panel">
            <p class="eyebrow">
                {{ $game?->name ?? 'QR-GAME' }}
            </p>

            @if ($isQuestionLink)
                <h1 class="page-title">Vraag niet gevonden</h1>

                <p>
                    Deze QR-code verwijst niet naar een beschikbare vraag.
                </p>

                <p>
                    Scan een andere QR-code van het spel.
                    Vraag de organisator om hulp als het probleem blijft.
                </p>
            @else
                <h1 class="page-title">Pagina niet gevonden</h1>

                <p>
                    De pagina die je zoekt bestaat niet of is niet meer
                    beschikbaar.
                </p>
            @endif

            <div class="form-actions">
                @if ($isQuestionLink && $game)
                    <a
                        href="{{ route('student.games.show', ['game' => $game]) }}"
                        class="button"
                    >
                        Terug naar spel
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="button button-secondary"
                    >
                        Terug naar home
                    </a>
                @else
                    <a href="{{ route('home') }}" class="button">
                        Terug naar home
                    </a>
                @endif
            </div>
        </section>
    </div>
@endsection
