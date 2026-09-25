<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameParticipant;
use App\Services\OrganizerGameContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizerResultsController extends Controller
{
    public function __construct(private readonly OrganizerGameContext $gameContext)
    {
    }

    public function index(Request $request): View
    {
        $game = $this->gameContext->current($request);

        $results = $game
            ? $this->resultsFor($game)
            : collect();

        return view('organizer.results.index', [
            'game' => $game,
            'results' => $results,
            'questionCount' => $game
                ? $game->questions()->count()
                : 0,
            'maxPoints' => $game
                ? (int) $game->questions()->sum('max_points')
                : 0,
            'pendingCount' => (int) $results->sum('pending_count'),
        ]);
    }

    public function export(Request $request, Game $game): StreamedResponse
    {
        abort_unless(
            (string) $game->created_by
                === (string) $request->user()->getAuthIdentifier(),
            404
        );

        $results = $this->resultsFor($game);

        $filename = 'resultaten-spel-'
            . $game->id
            . '-'
            . now()->format('Y-m-d-His')
            . '.csv';

        return response()->streamDownload(
            function () use ($results): void {
                $output = fopen('php://output', 'wb');

                if ($output === false) {
                    throw new \RuntimeException(
                        'Het CSV-bestand kon niet worden gemaakt.'
                    );
                }

                try {
                    fwrite($output, "\xEF\xBB\xBF");

                    fputcsv($output, [
                        'Positie',
                        'Studentnummer',
                        'Beantwoord',
                        'Nog na te kijken',
                        'Totaal punten',
                    ], ';', '"', '', "\r\n");

                    foreach ($results as $result) {
                        fputcsv($output, [
                            $result['rank'],
                            $result['student_number'],
                            $result['answered_count'],
                            $result['pending_count'],
                            $result['total_points'],
                        ], ';', '"', '', "\r\n");
                    }
                } finally {
                    fclose($output);
                }
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    private function resultsFor(Game $game): Collection
    {
        $results = $game->participants()
            ->with('student:id,student_number')
            ->withCount([
                'answers as answered_count',
                'answers as pending_count' => function (Builder $query): void {
                    $query->whereNull('graded_at');
                },
            ])
            ->withSum('answers as total_points', 'points_awarded')
            ->orderBy('id')
            ->get()
            ->map(function (GameParticipant $participant): array {
                return [
                    'student_number' => (string) $participant->student->student_number,
                    'answered_count' => (int) $participant->answered_count,
                    'pending_count' => (int) $participant->pending_count,
                    'total_points' => (int) ($participant->total_points ?? 0),
                ];
            })
            ->sortByDesc('total_points')
            ->values();

        $previousPoints = null;
        $rank = 0;

        return $results->map(
            function (array $result, int $index) use (&$previousPoints, &$rank): array {
                if ($previousPoints !== $result['total_points']) {
                    $rank = $index + 1;
                }

                $previousPoints = $result['total_points'];
                $result['rank'] = $rank;

                return $result;
            }
        );
    }
}
