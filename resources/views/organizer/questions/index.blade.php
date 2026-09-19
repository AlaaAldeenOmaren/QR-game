@extends('layouts.organizer')

@section('title', 'Vragenoverzicht | QR-Game')

@section('organizer-content')
    <header class="page-heading">
        <h1 class="page-title">Vragenoverzicht</h1>

        @if ($game && in_array($game->status, ['not_started', 'paused'], true))
            <a href="{{ route('organizer.questions.create', $game) }}" class="button">
                Nieuwe vraag
            </a>
        @else
            <button type="button" class="button" disabled>
                Nieuwe vraag
            </button>
        @endif
    </header>

    @if ($game)
        <section class="panel" aria-labelledby="questions-title">
            <p class="eyebrow">VRAGEN</p>

            <h2 id="questions-title">{{ $game->name }}</h2>

            <p>Aantal vragen: {{ $questions->count() }}</p>

            @if ($game->status === 'finished')
                <p>
                    Dit spel is gestopt.
                    Je kunt de vragen nog bekijken.
                </p>
            @endif

            <div class="table-scroll" role="region" aria-labelledby="questions-title" tabindex="0">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Vraag</th>
                            <th scope="col">Type</th>
                            <th scope="col">Punten</th>
                            <th scope="col">Actie</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($questions as $question)
                            <tr>
                                <th scope="row">
                                    {{ $question->question_text }}
                                </th>

                                <td>
                                    {{ $question->type === 'multiple_choice' ? 'Meerkeuze' : 'Open' }}
                                </td>

                                <td>{{ $question->max_points }}</td>

                                <td>
                                    @php
                                        $canEdit =
                                            in_array($game->status, ['not_started', 'paused'], true) &&
                                            (int) $question->answers_count === 0;
                                    @endphp

                                    <a href="{{ route('organizer.questions.edit', [
                                        'game' => $game,
                                        'question' => $question,
                                    ]) }}"
                                        class="button button-compact">
                                        {{ $canEdit ? 'Bewerken' : 'Bekijken' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="table-empty">
                                    Er zijn nog geen vragen voor dit spel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="panel">
            <h2>Geen spel gevonden</h2>

            <p>Er is nog geen spel aan jouw account gekoppeld.</p>
        </section>
    @endif
@endsection
