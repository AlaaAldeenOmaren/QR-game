@extends('layouts.organizer')

@section('organizer-content')
    <div class="page-heading">
        <h1 class="page-title">Antwoord beoordelen</h1>
    </div>

    <section class="panel">
        <p>
            Spel:
            <strong>{{ $game->name }}</strong>
        </p>

        <p>
            Studentnummer:
            <strong>{{ $answer->participant->student->student_number }}</strong>
        </p>

        <h2>{{ $answer->question->question_text }}</h2>

        <p>
            Maximaal {{ $answer->question->max_points }} punten
        </p>

        <h3>Ingediend antwoord</h3>

        <div class="submitted-answer">{{ $answer->answer_text }}</div>

        @if ($answer->graded_at !== null)
            <p>
                Dit antwoord is al beoordeeld.
                Je kunt de beoordeling aanpassen.
            </p>
        @endif

        <form
            method="POST"
            action="{{ route('organizer.grading.update', [
                'game' => $game,
                'answer' => $answer,
            ]) }}"
        >
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-field">
                    <label for="points_awarded">
                        Punten (0 t/m {{ $answer->question->max_points }})
                    </label>

                    <input
                        id="points_awarded"
                        name="points_awarded"
                        type="number"
                        min="0"
                        max="{{ $answer->question->max_points }}"
                        step="1"
                        value="{{ old('points_awarded', $answer->points_awarded) }}"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="feedback">
                        Feedback voor de student (optioneel)
                    </label>

                    <textarea
                        id="feedback"
                        name="feedback"
                        rows="4"
                        maxlength="2000"
                        placeholder="Geef een korte uitleg bij je beoordeling."
                    >{{ old('feedback', $answer->feedback) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button">
                    Beoordeling opslaan
                </button>

                <a
                    href="{{ route('organizer.grading.index') }}"
                    class="button button-secondary"
                >
                    Annuleren
                </a>
            </div>
        </form>
    </section>
@endsection
