@extends('layouts.organizer')

@section('organizer-content')
    <div class="page-heading">
        <h1 class="page-title">Resultaten</h1>

        @if ($game && $results->isNotEmpty())
            <a
                href="{{ route('organizer.results.export', [
                    'game' => $game,
                ]) }}"
                class="button"
            >
                Download CSV
            </a>
        @endif
    </div>

    @if ($game)
        <section class="panel">
            <p class="eyebrow">SPEELRESULTATEN</p>

            <h2>{{ $game->name }}</h2>

            <p>
                Deelnemers:
                <strong>{{ $results->count() }}</strong>
                · Vragen:
                <strong>{{ $questionCount }}</strong>
                · Maximaal:
                <strong>{{ $maxPoints }} punten</strong>
            </p>

            <p>
                Nog na te kijken:
                <strong>{{ $pendingCount }}</strong>
            </p>

            @if ($game->status !== 'finished' || $pendingCount > 0)
                <p>
                    Dit is een tussenstand. Punten en posities kunnen
                    nog veranderen door nieuwe antwoorden of beoordelingen.
                </p>
            @else
                <p>
                    Het spel is afgelopen.
                    Alle ingediende antwoorden zijn beoordeeld.
                </p>
            @endif

            <div
                class="table-scroll"
                role="region"
                aria-label="Resultaten van de deelnemers"
                tabindex="0"
            >
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Positie</th>
                            <th scope="col">Studentnummer</th>
                            <th scope="col">Beantwoord</th>
                            <th scope="col">Nog na te kijken</th>
                            <th scope="col">Totaal punten</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($results as $result)
                            <tr>
                                <td>
                                    {{ $result['rank'] }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $result['student_number'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $result['answered_count'] }}
                                    / {{ $questionCount }}
                                </td>

                                <td>
                                    {{ $result['pending_count'] }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $result['total_points'] }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    Er zijn nog geen deelnemers aan dit spel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($results->isNotEmpty())
                <p>
                    Bij gelijke punten delen deelnemers dezelfde positie.
                    Open antwoorden tellen mee voor de punten zodra
                    ze zijn beoordeeld.
                </p>
            @endif
        </section>
    @else
        <section class="panel">
            <h2>Nog geen resultaten</h2>

            <p>Er is nog geen spel beschikbaar.</p>
        </section>
    @endif
@endsection
