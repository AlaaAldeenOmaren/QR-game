<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $organizer */
        $organizer = $request->user();

        $game = $organizer->createdGames()
            ->withCount([
                'questions',
                'participants',
                'answers as pending_answers_count' => function (
                    Builder $query
                ): void {
                    $query->whereNull('graded_at')
                        ->whereHas('question', function (
                            Builder $questionQuery
                        ): void {
                            $questionQuery->where('type', 'open');
                        });
                },
            ])
            ->latest('id')
            ->first();

        $statusLabel = match ($game?->status) {
            'not_started' => 'Niet gestart',
            'active' => 'Actief',
            'paused' => 'Gepauzeerd',
            'finished' => 'Gestopt',
            default => 'Onbekend',
        };

        return view('organizer.dashboard', [
            'game' => $game,
            'statusLabel' => $statusLabel,
        ]);
    }
}
