@extends('layouts.organizer')

@section('organizer-content')
    <div class="page-heading">
        <h1 class="page-title">Open antwoorden nakijken</h1>
    </div>

    @if ($game)
        <section class="panel">
            <p class="eyebrow">NAKIJKEN</p>

            <h2>{{ $game->name }}</h2>

            <p>
                Nog na te kijken:
                <strong>{{ $pendingCount }}</strong>
            </p>

            <div
                class="table-scroll"
                role="region"
                aria-label="Ingediende open antwoorden"
                tabindex="0"
            >
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Student</th>
                            <th scope="col">Vraag</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actie</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($answers as $answer)
                            <tr>
                                <th scope="row">
                                    {{ $answer->participant->student->student_number }}
                                </th>

                                <td>
                                    {{ $answer->question->question_text }}
                                </td>

                                <td>
                                    @if ($answer->graded_at === null)
                                        Wachten op beoordeling
                                    @else
                                        Beoordeeld:
                                        {{ $answer->points_awarded }}
                                        /
                                        {{ $answer->question->max_points }}
                                        punten
                                    @endif
                                </td>

                                <td>
                                    <a
                                        href="{{ route('organizer.grading.edit', [
                                            'game' => $game,
                                            'answer' => $answer,
                                        ]) }}"
                                        class="button button-secondary button-compact"
                                    >
                                        {{ $answer->graded_at === null ? 'Beoordelen' : 'Bewerken' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    Er zijn nog geen open antwoorden ingediend.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="panel">
            <p>Er is nog geen spel beschikbaar.</p>
        </section>
    @endif
@endsection
