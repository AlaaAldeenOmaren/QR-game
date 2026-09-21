@extends('layouts.app')

@section('content')
    <div class="player-shell">
        @include('student.partials.nav')

        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            <h1 class="page-title">Ranglijst</h1>

            @if ($isFinal)
                <p>
                    Dit is de eindstand. Het spel is afgelopen en alle
                    ingestuurde antwoorden zijn beoordeeld.
                </p>
            @else
                <p>
                    Dit is een tussenstand. Nieuwe antwoorden en
                    beoordelingen kunnen de ranglijst veranderen.
                </p>
            @endif

            @if ($pendingCount > 0)
                <p>
                    Nog te beoordelen antwoorden:
                    <strong>{{ $pendingCount }}</strong>.
                </p>
            @endif

            <div class="table-scroll">
                <table
                    class="data-table leaderboard-table"
                    aria-label="Ranglijst van het spel"
                >
                    <thead>
                        <tr>
                            <th scope="col">Positie</th>
                            <th scope="col">Speler</th>
                            <th scope="col">Punten</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="{{ $row['is_current'] ? 'is-current' : '' }}">
                                <td>{{ $row['rank'] }}</td>

                                <td>
                                    {{ $row['player_label'] }}

                                    @if ($row['is_current'])
                                        <span class="leaderboard-you">
                                            (jij)
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <strong>
                                        {{ $row['total_points'] }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    Er zijn nog geen deelnemers.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($currentRow)
                <p class="leaderboard-position">
                    Jouw positie:
                    <strong>{{ $currentRow['rank'] }}</strong>
                    ·
                    Jouw punten:
                    <strong>{{ $currentRow['total_points'] }}</strong>
                </p>
            @endif

            <p>
                Bij gelijke punten delen spelers dezelfde positie.
                Open antwoorden tellen mee zodra ze zijn beoordeeld.
            </p>

            <div class="form-actions">
                <a
                    href="{{ route('student.progress.show', ['game' => $game]) }}"
                    class="button"
                >
                    Mijn voortgang
                </a>

                <a
                    href="{{ route('student.leaderboard.show', ['game' => $game]) }}"
                    class="button button-secondary"
                >
                    Vernieuwen
                </a>
            </div>
        </section>
    </div>
@endsection
