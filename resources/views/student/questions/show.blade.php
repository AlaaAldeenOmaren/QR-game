@extends('layouts.app')

@section('content')
    <div class="player-shell">
        @if ($errors->any())
            <div class="notice notice-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            @if ($game->status !== 'active')
                @php
                    $stateMessage = match ($game->status) {
                        'not_started' => [
                            'title' => 'Nog niet gestart',
                            'description' => 'De organisator heeft het spel nog niet gestart.',
                        ],
                        'paused' => [
                            'title' => 'Spel gepauzeerd',
                            'description' => 'Het spel is tijdelijk gepauzeerd. Je voortgang blijft bewaard.',
                        ],
                        'finished' => [
                            'title' => 'Spel afgelopen',
                            'description' => 'Het spel is gestopt. Je kunt geen nieuwe antwoorden meer insturen.',
                        ],
                        default => [
                            'title' => 'Spel niet beschikbaar',
                            'description' => 'Dit spel is momenteel niet beschikbaar.',
                        ],
                    };
                @endphp

                <h1 class="page-title">
                    {{ $stateMessage['title'] }}
                </h1>

                <p>{{ $stateMessage['description'] }}</p>

                <div class="form-actions">
                    @if ($game->status !== 'finished')
                        <a
                            href="{{ route('student.questions.show', [
                                'question' => $question->qr_token,
                            ]) }}"
                            class="button"
                        >
                            Opnieuw controleren
                        </a>
                    @endif

                    <a
                        href="{{ route('home') }}"
                        class="button button-secondary"
                    >
                        Terug naar home
                    </a>
                </div>
            @elseif (!$participant)
                <h1 class="page-title">Doe mee aan QR-Game</h1>

                <p>Vul je studentnummer in om verder te gaan.</p>

                <form
                    method="POST"
                    action="{{ route('student.join.store', [
                        'question' => $question->qr_token,
                    ]) }}"
                >
                    @csrf

                    <div class="form-field">
                        <label for="student_number">
                            Studentnummer
                        </label>

                        <input
                            id="student_number"
                            name="student_number"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]+"
                            maxlength="20"
                            value="{{ old('student_number') }}"
                            required
                        >
                    </div>

                    <button type="submit" class="button button-wide">
                        Verder
                    </button>
                </form>
            @else
                <p>
                    Studentnummer:
                    <strong>{{ $participant->student->student_number }}</strong>
                </p>

                <h1 class="page-title">
                    {{ $question->question_text }}
                </h1>

                <p>Maximaal {{ $question->max_points }} punten</p>

                <form
                    method="POST"
                    action="{{ route('student.answers.store', [
                        'question' => $question->qr_token,
                    ]) }}"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="question_version"
                        value="{{ $questionVersion }}"
                    >

                    @if ($question->type === 'multiple_choice')
                        <fieldset class="player-options">
                            <legend>Kies je antwoord</legend>

                            @foreach ($question->options as $option)
                                <label class="player-option">
                                    <input
                                        type="radio"
                                        name="selected_option_id"
                                        value="{{ $option->id }}"
                                        @checked((string) old('selected_option_id') === (string) $option->id)
                                        required
                                    >

                                    <span>
                                        <strong>{{ $option->label }}.</strong>
                                        {{ $option->option_text }}
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                    @else
                        <div class="form-field">
                            <label for="answer_text">Jouw antwoord</label>

                            <textarea
                                id="answer_text"
                                name="answer_text"
                                rows="5"
                                maxlength="2000"
                                required
                            >{{ old('answer_text') }}</textarea>
                        </div>
                    @endif

                    <button type="submit" class="button button-wide">
                        Antwoord versturen
                    </button>
                </form>
            @endif
        </section>
    </div>
@endsection
