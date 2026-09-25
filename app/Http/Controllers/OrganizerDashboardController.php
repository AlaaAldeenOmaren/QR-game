<?php

namespace App\Http\Controllers;

use App\Services\OrganizerGameContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function __construct(private readonly OrganizerGameContext $gameContext)
    {
    }

    public function index(Request $request): View
    {
        $game = $this->gameContext->current($request);

        $game?->loadCount([
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
        ]);

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
