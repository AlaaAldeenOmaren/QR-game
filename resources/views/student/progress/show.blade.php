@extends('layouts.app')

@section('content')
    <div class="player-shell">
        @include('student.partials.nav')

        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            <h1 class="page-title">Mijn voortgang</h1>

            <p>
                Studentnummer:
                <strong>{{ $participant->student->student_number }}</strong>
            </p>

            <dl class="progress-stats">
                <div>
                    <dt>Mijn punten</dt>
                    <dd>{{ $totalPoints }}</dd>
                </div>

                <div>
                    <dt>Beantwoord</dt>
                    <dd>{{ $answers->count() }} / {{ $questionCount }}</dd>
                </div>

                <div>
                    <dt>In afwachting</dt>
                    <dd>{{ $pendingCount }}</dd>
                </div>
            </dl>

            @if ($game->status === 'paused')
                <p>
                    Het spel is gepauzeerd.
                    Je antwoorden en punten blijven bewaard.
                </p>
            @elseif ($game->status === 'finished')
                <p>
                    Het spel is afgelopen.
                    Je kunt je antwoorden en beoordelingen hier bekijken.
                </p>
            @elseif ($game->status === 'not_started')
                <p>De organisator heeft het spel nog niet gestart.</p>
            @endif

            @if ($pendingCount > 0)
                <p>
                    Je hebt {{ $pendingCount }}
                    {{ $pendingCount === 1 ? 'antwoord' : 'antwoorden' }}
                    waarvoor de organisator nog punten moet toekennen.
                </p>
            @endif

            <h2>Mijn antwoorden</h2>

            <ul class="progress-list">
                @forelse ($answers as $answer)
                    @php
                        $status = match (true) {
                            $answer->points_awarded === null => 'pending',
                            $answer->question->type === 'open' => 'graded',
                            $answer->points_awarded === $answer->question->max_points => 'correct',
                            default => 'incorrect',
                        };

                        $statusLabel = [
                            'pending' => 'Wacht op beoordeling',
                            'graded' => 'Beoordeeld',
                            'correct' => 'Goed',
                            'incorrect' => 'Fout',
                        ][$status];
                    @endphp

                    <li class="progress-item">
                        <h3>
                            <a
                                href="{{ route('student.answers.show', [
                                    'question' => $answer->question->qr_token,
                                ]) }}"
                            >
                                {{ $answer->question->question_text }}
                            </a>
                        </h3>

                        <div class="progress-meta">
                            <span
                                class="progress-status"
                                data-status="{{ $status }}"
                            >
                                {{ $statusLabel }}
                            </span>

                            <strong>
                                @if ($answer->points_awarded === null)
                                    Nog geen punten
                                @else
                                    {{ $answer->points_awarded }}
                                    / {{ $answer->question->max_points }} punten
                                @endif
                            </strong>
                        </div>

                        @if (filled($answer->feedback))
                            <p class="answer-text"><strong>Feedback:</strong> {{ $answer->feedback }}</p>
                        @endif
                    </li>
                @empty
                    <li class="progress-item">
                        Je hebt nog geen antwoorden ingestuurd.
                        Open een QR-code van het spel om een vraag te beantwoorden.
                    </li>
                @endforelse
            </ul>

            <div class="form-actions">
                <a
                    href="{{ route('student.games.show', ['game' => $game]) }}"
                    class="button"
                >
                    @if ($game->status === 'active' && $answers->count() < $questionCount)
                        Volgende QR scannen
                    @else
                        Naar het spel
                    @endif
                </a>

                <a
                    href="{{ route('student.progress.show', ['game' => $game]) }}"
                    class="button button-secondary"
                >
                    Vernieuwen
                </a>
            </div>
        </section>
    </div>
@endsection
