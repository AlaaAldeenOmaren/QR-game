@extends('layouts.app')

@section('content')
    @php
        $state = match ($game->status) {
            'active' => [
                'title' => 'Klaar voor de volgende vraag?',
                'description' =>
                    'Open de camera van je telefoon en scan een QR-code van het spel. Tik op de link om de vraag te openen.',
            ],
            'not_started' => [
                'title' => 'Nog niet gestart',
                'description' => 'De organisator heeft het spel nog niet gestart.',
            ],
            'paused' => [
                'title' => 'Spel gepauzeerd',
                'description' => 'Het spel is tijdelijk gepauzeerd. Je antwoorden en punten blijven bewaard.',
            ],
            'finished' => [
                'title' => 'Spel afgelopen',
                'description' => 'Het spel is gestopt. Je kunt je antwoorden en beoordelingen nog bekijken.',
            ],
            default => [
                'title' => 'Spel niet beschikbaar',
                'description' => 'Dit spel is momenteel niet beschikbaar.',
            ],
        };
    @endphp

    <div class="player-shell">
        @include('student.partials.nav')

        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            <h1 class="page-title">{{ $state['title'] }}</h1>

            <dl class="answer-details">
                <div>
                    <dt>Studentnummer</dt>
                    <dd>{{ $participant->student->student_number }}</dd>
                </div>

                <div>
                    <dt>Mijn punten</dt>
                    <dd><strong>{{ $totalPoints }} punten</strong></dd>
                </div>
            </dl>

            <p>{{ $state['description'] }}</p>

            <div class="form-actions">
                @if ($game->status === 'finished')
                    <a href="{{ route('student.leaderboard.show', ['game' => $game]) }}" class="button">
                        Bekijk ranglijst
                    </a>
                @endif

                <a href="{{ route('student.progress.show', ['game' => $game]) }}"
                    class="button{{ $game->status === 'finished' ? ' button-secondary' : '' }}">
                    Mijn voortgang
                </a>

                @if (in_array($game->status, ['not_started', 'paused'], true))
                    <a href="{{ route('student.games.show', ['game' => $game]) }}" class="button button-secondary">
                        Opnieuw controleren
                    </a>
                @endif
            </div>
        </section>
    </div>
@endsection
