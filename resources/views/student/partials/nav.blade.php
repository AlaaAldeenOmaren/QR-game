@if (isset($participant) && $participant)
    <nav class="player-nav" aria-label="Spelmenu">
        <a
            href="{{ route('student.games.show', ['game' => $game]) }}"
            @if (request()->routeIs(
                'student.games.*',
                'student.questions.*',
                'student.answers.*'
            )) aria-current="page" @endif
        >
            Spel
        </a>

        <a
            href="{{ route('student.progress.show', ['game' => $game]) }}"
            @if (request()->routeIs('student.progress.*'))
                aria-current="page"
            @endif
        >
            Voortgang
        </a>

        @if (\Illuminate\Support\Facades\Route::has('student.leaderboard.show'))
            <a
                href="{{ route('student.leaderboard.show', ['game' => $game]) }}"
                @if (request()->routeIs('student.leaderboard.*'))
                    aria-current="page"
                @endif
            >
                Ranglijst
            </a>
        @endif
    </nav>
@endif
