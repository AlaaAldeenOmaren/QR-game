@extends('layouts.app')

@section('content')
    @php
        $isPending = $answer->points_awarded === null;

        $title = match (true) {
            $isPending => 'Antwoord ontvangen',
            $question->type === 'open' => 'Antwoord beoordeeld',
            $answer->points_awarded === $question->max_points => 'Goed beantwoord!',
            default => 'Dat was niet het juiste antwoord',
        };
    @endphp

    <div class="player-shell">
        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            <h1 class="page-title">{{ $title }}</h1>

            <p>
                Studentnummer:
                <strong>{{ $participant->student->student_number }}</strong>
            </p>

            <h2>{{ $question->question_text }}</h2>

            <dl class="answer-details">
                <div>
                    <dt>Jouw antwoord</dt>
                    <dd class="answer-text">{{ $answer->selectedOption?->option_text ?? $answer->answer_text }}</dd>
                </div>

                @if ($question->type === 'multiple_choice')
                    <div>
                        <dt>Juiste antwoord</dt>
                        <dd>{{ $correctAnswer }}</dd>
                    </div>
                @endif

                <div>
                    <dt>Punten voor deze vraag</dt>
                    <dd>
                        @if ($isPending)
                            Wachten op beoordeling
                        @else
                            {{ $answer->points_awarded }}
                            van
                            {{ $question->max_points }}
                            punten
                        @endif
                    </dd>
                </div>

                @if (filled($answer->feedback))
                    <div>
                        <dt>Feedback van de organisator</dt>
                        <dd class="answer-text">{{ $answer->feedback }}</dd>
                    </div>
                @endif

                <div>
                    <dt>Totaal in dit spel</dt>
                    <dd>
                        <strong>{{ $totalPoints }} punten</strong>
                    </dd>
                </div>
            </dl>

            @if ($isPending)
                <p>
                    Je antwoord is opgeslagen. De organisator kijkt het
                    nog na en kent daarna punten toe.
                </p>
            @endif

            @if ($game->status === 'active')
                <p>Scan de volgende QR-code om verder te spelen.</p>
            @elseif ($game->status === 'paused')
                <p>Het spel is gepauzeerd. Je antwoord blijft bewaard.</p>
            @elseif ($game->status === 'finished')
                <p>Het spel is afgelopen. Je antwoord blijft bewaard.</p>
            @endif

            <div class="form-actions">
                @if ($isPending)
                    <a
                        href="{{ route('student.answers.show', [
                            'question' => $question->qr_token,
                        ]) }}"
                        class="button"
                    >
                        Beoordeling controleren
                    </a>
                @endif

                <a
                    href="{{ route('home') }}"
                    class="button button-secondary"
                >
                    Terug naar home
                </a>
            </div>
        </section>
    </div>
@endsection
