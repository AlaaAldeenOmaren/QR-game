@extends('layouts.app')

@section('title', 'Welkom | QR-Game')

@section('content')
    <section class="hero" aria-labelledby="welcome-title">
        <div>
            <p class="eyebrow">ONTDEK JE SCHOOL</p>

            <h1 id="welcome-title">Welkom bij QR-Game!</h1>

            <p class="intro">
                Scan een QR-code, beantwoord de vraag en verzamel punten.
                Hoeveel weet jij?
            </p>

            <div class="form-actions">
                @if ($game && $participant)
                    @if ($game->status === 'finished')
                        <a class="button" href="{{ route('student.progress.show', ['game' => $game]) }}">
                            Mijn voortgang
                        </a>
                    @else
                        <a class="button" href="{{ route('student.games.show', ['game' => $game]) }}">
                            Verder spelen
                        </a>
                    @endif
                @else
                    <a class="button" href="#deelnemen">
                        Hoe doe ik mee?
                    </a>
                @endif

                @if ($game)
                    <a class="button button-secondary" href="{{ route('student.leaderboard.show', ['game' => $game]) }}">
                        Ranglijst
                    </a>
                @endif

                <a class="button button-secondary" href="#spelregels">
                    Spelregels
                </a>
            </div>

            <p>
                @auth
                    <a href="{{ route('dashboard') }}">Naar het dashboard</a>
                @else
                    <a href="{{ route('login') }}">Inloggen als organisator</a>
                @endauth
            </p>
        </div>

        <aside class="panel" id="deelnemen">
            <span class="badge">
                {{ $game ? 'Laatst geopende spel' : 'Zo begin je' }}
            </span>

            <h2>{{ $game?->name ?? 'Klaar om te spelen?' }}</h2>

            @if ($game && $game->status === 'finished')
                <p>
                    Dit spel is afgelopen. Je kunt de ranglijst en
                    je opgeslagen voortgang nog bekijken.
                </p>
            @else
                <p>
                    Open de camera van je telefoon en scan een QR-code
                    van het spel. Tik op de link om verder te gaan.
                </p>

                <p class="note">
                    Houd je studentnummer bij de hand.
                </p>
            @endif

            @if ($game && $participant)
                @if ($game->status !== 'finished')
                    <a class="button button-secondary" href="{{ route('student.progress.show', ['game' => $game]) }}">
                        Mijn voortgang
                    </a>
                @endif
            @elseif ($game)
                <p>
                    Al meegedaan? Gebruik hetzelfde studentnummer
                    om je opgeslagen voortgang te bekijken.
                </p>

                <a class="button button-secondary" href="{{ route('student.progress.show', ['game' => $game]) }}">
                    Deelname hervatten
                </a>
            @endif
        </aside>
    </section>

    <section class="rules" id="spelregels" aria-labelledby="rules-title">
        <p class="eyebrow">IN DRIE STAPPEN</p>
        <h2 id="rules-title">Hoe werkt het?</h2>

        <ol class="steps">
            <li class="panel">
                <span class="step-number" aria-hidden="true">01</span>
                <h3>Scan een QR-code</h3>
                <p>Elke QR-code brengt je naar een vraag van het spel.</p>
            </li>

            <li class="panel">
                <span class="step-number" aria-hidden="true">02</span>
                <h3>Vul je studentnummer in</h3>
                <p>Bij je eerste deelname vul je jouw studentnummer in.</p>
            </li>

            <li class="panel">
                <span class="step-number" aria-hidden="true">03</span>
                <h3>Beantwoord de vraag</h3>
                <p>Kies een antwoord of schrijf je antwoord en verstuur het.</p>
            </li>
        </ol>

        <p class="rules-note">
            Je kunt antwoorden wanneer het spel actief is.
            Meerkeuzevragen worden automatisch nagekeken.
            Open antwoorden worden door de organisator beoordeeld.
        </p>
    </section>
@endsection
