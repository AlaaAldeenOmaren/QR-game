<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameParticipant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentLeaderboardController extends Controller
{
    public function show(Request $request, Game $game): View
    {
        $participantId = $request->session()->get(
            'student_participants.' . $game->id
        );

        $participant = $participantId
            ? $game->participants()->find($participantId)
            : null;

        $rows = $game->participants()
            ->select([
                'game_participants.id',
                'game_participants.game_id',
            ])
            ->withSum('answers as total_points', 'points_awarded')
            ->withCount([
                'answers as pending_count' => function (Builder $query): void {
                    $query->whereNull('points_awarded');
                },
            ])
            ->orderBy('id')
            ->get()
            ->map(function (GameParticipant $entry) use ($participant): array {
                return [
                    'player_label' => 'Speler ' . $entry->id,
                    'total_points' => (int) ($entry->total_points ?? 0),
                    'pending_count' => (int) $entry->pending_count,
                    'is_current' => $participant !== null
                        && (int) $entry->id === (int) $participant->id,
                ];
            })
            ->sortByDesc('total_points')
            ->values();

        $previousPoints = null;
        $rank = 0;

        $rows = $rows->map(function (array $row, int $index) use (
            &$previousPoints,
            &$rank
        ): array {
            if ($previousPoints !== $row['total_points']) {
                $rank = $index + 1;
            }

            $row['rank'] = $rank;
            $previousPoints = $row['total_points'];

            return $row;
        });

        $pendingCount = (int) $rows->sum('pending_count');

        return view('student.leaderboard.show', [
            'game' => $game,
            'participant' => $participant,
            'rows' => $rows,
            'currentRow' => $rows->firstWhere('is_current', true),
            'pendingCount' => $pendingCount,
            'isFinal' => $game->status === 'finished' && $pendingCount === 0,
        ]);
    }
}
