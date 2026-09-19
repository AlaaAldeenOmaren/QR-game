@extends('layouts.organizer')

@section('title', 'Vraag bewerken | QR-Game')

@section('organizer-content')
    @php
        $selectedType = old('type', $question->type);

        $correctOption = old('correct_option', $question->options->firstWhere('is_correct', true)?->label);
    @endphp

    <header class="page-heading">
        <h1 class="page-title">
            {{ $canEdit ? 'Vraag bewerken' : 'Vraag bekijken' }}
        </h1>
    </header>

    <section class="panel">
        <p>Spel: <strong>{{ $game->name }}</strong></p>

        <div class="qr-preview">
            <img src="{{ $qrDataUri }}" alt="QR-code voor deze vraag" width="380" height="380">

            <div class="qr-actions">
                <a href="{{ $questionUrl }}" class="button" target="_blank" rel="noopener">
                    Open vraag
                </a>

                <a href="{{ $qrDataUri }}" class="button button-secondary" download="vraag-{{ $question->id }}.svg">
                    Download QR
                </a>
            </div>
        </div>

        @if (!$canEdit)
            <p class="note">
                Je kunt deze vraag bekijken.
                Bewerken kan alleen vóór de start of tijdens een pauze,
                zolang er nog geen antwoorden op deze vraag zijn.
            </p>
        @endif

        <form method="POST"
            action="{{ route('organizer.questions.update', [
                'game' => $game,
                'question' => $question,
            ]) }}">
            @csrf
            @method('PUT')

            <fieldset class="form-fields" aria-label="Vraaggegevens" @disabled(!$canEdit)>
                <div class="form-field">
                    <label for="question_text">Vraag</label>

                    <textarea id="question_text" name="question_text" rows="3" maxlength="2000" required>{{ old('question_text', $question->question_text) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label for="question_type">Type</label>

                        <select id="question_type" name="type" required>
                            <option value="multiple_choice" @selected($selectedType === 'multiple_choice')>
                                Meerkeuze
                            </option>

                            <option value="open" @selected($selectedType === 'open')>
                                Open
                            </option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="max_points">Maximale punten</label>

                        <input type="number" id="max_points" name="max_points"
                            value="{{ old('max_points', $question->max_points) }}" min="1" max="65535"
                            step="1" required>
                    </div>
                </div>

                <fieldset id="choice-fields" class="choice-fields" @disabled($selectedType === 'open')
                    @if ($selectedType === 'open') hidden @endif>
                    <legend>Antwoordopties</legend>

                    <div class="option-grid">
                        @foreach (['A', 'B', 'C'] as $label)
                            <div class="form-field">
                                <label for="option-{{ $label }}">
                                    Antwoord {{ $label }}
                                </label>

                                <input type="text" id="option-{{ $label }}" name="options[{{ $label }}]"
                                    value="{{ old('options.' . $label, $question->options->firstWhere('label', $label)?->option_text) }}"
                                    maxlength="255" required>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-field">
                        <label for="correct_option">Juiste antwoord</label>

                        <select id="correct_option" name="correct_option" required>
                            <option value="">Kies het juiste antwoord</option>

                            @foreach (['A', 'B', 'C'] as $label)
                                <option value="{{ $label }}" @selected($correctOption === $label)>
                                    Antwoord {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                <p id="open-question-note" @if ($selectedType !== 'open') hidden @endif>
                    De organisator beoordeelt open antwoorden
                    handmatig en kent punten toe.
                </p>
            </fieldset>

            <div class="form-actions">
                @if ($canEdit)
                    <button type="submit" class="button">
                        Wijzigingen opslaan
                    </button>
                @endif

                <a href="{{ route('organizer.questions.index') }}" class="button button-secondary">
                    Terug naar vragen
                </a>
            </div>
        </form>
    </section>

    <script src="{{ asset('js/question-form.js') }}" defer></script>
@endsection
