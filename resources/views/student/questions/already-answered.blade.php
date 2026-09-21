@extends('layouts.app')

@section('content')
    <div class="player-shell">
        @include('student.partials.nav')

        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            <h1 class="page-title">Al beantwoord</h1>

            <p>Je hebt deze vraag al beantwoord.</p>

            <p>
                Je bestaande antwoord en punten blijven staan.
            </p>

            <div class="form-actions">
                <a
                    href="{{ route('student.answers.show', [
                        'question' => $question->qr_token,
                    ]) }}"
                    class="button"
                >
                    Bekijk mijn antwoord
                </a>

                <a
                    href="{{ route('student.games.show', [
                        'game' => $game,
                    ]) }}"
                    class="button button-secondary"
                >
                    Terug naar spel
                </a>
            </div>
        </section>
    </div>
@endsection
