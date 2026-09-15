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

            <a class="button" href="#spelregels">
                Bekijk de spelregels
            </a>
        </div>

        <aside class="panel">
            <span class="badge">Zo begin je</span>

            <h2>Klaar om te spelen?</h2>

            <p>
                Open de camera van je telefoon en scan een QR-code
                van het spel. Tik op de link om verder te gaan.
            </p>

            <p class="note">
                Houd je studentnummer bij de hand.
            </p>
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
