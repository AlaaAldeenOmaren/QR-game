@extends('layouts.organizer')

@section('title', 'Nieuwe vraag | QR-Game')

@section('organizer-content')
    <header class="page-heading">
        <h1 class="page-title">Nieuwe vraag</h1>
    </header>

    <section class="panel">
        <p>Spel: <strong>{{ $game->name }}</strong></p>

        <form
            method="POST"
            action="{{ route('organizer.questions.store', $game) }}"
        >
            @csrf

            <div class="form-field">
                <label for="question_text">Vraag</label>

                <textarea
                    id="question_text"
                    name="question_text"
                    rows="3"
                    maxlength="2000"
                    required
                >{{ old('question_text') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="question_type">Type</label>

                    <select id="question_type" name="type" required>
                        <option
                            value="multiple_choice"
                            @selected(old('type', 'multiple_choice') === 'multiple_choice')
                        >
                            Meerkeuze
                        </option>

                        <option value="open" @selected(old('type') === 'open')>
                            Open
                        </option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="max_points">Maximale punten</label>

                    <input
                        type="number"
                        id="max_points"
                        name="max_points"
                        value="{{ old('max_points', 10) }}"
                        min="1"
                        max="65535"
                        step="1"
                        required
                    >
                </div>
            </div>

            <fieldset
                id="choice-fields"
                class="choice-fields"
                @disabled(old('type') === 'open')
                @if (old('type') === 'open') hidden @endif
            >
                <legend>Antwoordopties</legend>

                <div class="option-grid">
                    @foreach (['A', 'B', 'C'] as $label)
                        <div class="form-field">
                            <label for="option-{{ $label }}">
                                Antwoord {{ $label }}
                            </label>

                            <input
                                type="text"
                                id="option-{{ $label }}"
                                name="options[{{ $label }}]"
                                value="{{ old('options.' . $label) }}"
                                maxlength="255"
                                required
                            >
                        </div>
                    @endforeach
                </div>

                <div class="form-field">
                    <label for="correct_option">Juiste antwoord</label>

                    <select id="correct_option" name="correct_option" required>
                        <option value="">Kies het juiste antwoord</option>

                        @foreach (['A', 'B', 'C'] as $label)
                            <option
                                value="{{ $label }}"
                                @selected(old('correct_option') === $label)
                            >
                                Antwoord {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </fieldset>

            <p
                id="open-question-note"
                @if (old('type') !== 'open') hidden @endif
            >
                De organisator beoordeelt open antwoorden
                handmatig en kent punten toe.
            </p>

            <div class="form-actions">
                <button type="submit" class="button">
                    Vraag opslaan
                </button>

                <a
                    href="{{ route('organizer.questions.index') }}"
                    class="button button-secondary"
                >
                    Annuleren
                </a>
            </div>
        </form>
    </section>

    <script src="{{ asset('js/question-form.js') }}" defer></script>
@endsection
