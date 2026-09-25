<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\OrganizerGameContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameStateController extends Controller
{
    public function __construct(private readonly OrganizerGameContext $gameContext)
    {
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $this->gameContext->select($request, $game);

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:start,pause,resume,stop'],
        ], [
            'action.required' => 'Kies een actie.',
            'action.string' => 'Ongeldige actie.',
            'action.in' => 'Ongeldige actie.',
        ]);

        $action = $validated['action'];

        DB::transaction(function () use ($request, $game, $action): void {
            $currentGame = Game::query()
                ->whereKey($game->getKey())
                ->where('created_by', $request->user()->getAuthIdentifier())
                ->lockForUpdate()
                ->firstOrFail();

            $allowedStatuses = match ($action) {
                'start' => ['not_started'],
                'pause' => ['active'],
                'resume' => ['paused'],
                'stop' => ['active', 'paused'],
            };

            if (! in_array($currentGame->status, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'game' => 'Deze actie is niet mogelijk bij de huidige spelstatus.',
                ]);
            }

            if ($action === 'start' && ! $currentGame->questions()->exists()) {
                throw ValidationException::withMessages([
                    'game' => 'Voeg eerst minimaal één vraag toe aan het spel.',
                ]);
            }

            $currentGame->status = match ($action) {
                'start', 'resume' => 'active',
                'pause' => 'paused',
                'stop' => 'finished',
            };

            if ($action === 'start') {
                $currentGame->started_at = now();
                $currentGame->ended_at = null;
            }

            if ($action === 'stop') {
                $currentGame->ended_at = now();
            }

            $currentGame->save();
        });

        $message = match ($action) {
            'start' => 'Het spel is gestart.',
            'pause' => 'Het spel is gepauzeerd.',
            'resume' => 'Het spel is hervat.',
            'stop' => 'Het spel is gestopt. Antwoorden en punten blijven bewaard.',
        };

        return redirect()
            ->route('dashboard', ['game' => $game->id])
            ->with('success', $message);
    }

    public function confirmStop(Request $request, Game $game): View|RedirectResponse
    {
        $this->gameContext->select($request, $game);

        abort_unless(
            (string) $game->created_by ===
            (string) $request->user()->getAuthIdentifier(),
            404
        );

        if (! in_array($game->status, ['active', 'paused'], true)) {
            return redirect()
                ->route('dashboard', ['game' => $game->id])
                ->withErrors([
                    'game' => 'Dit spel kan vanuit deze status niet worden gestopt.',
                ]);
        }

        return view('organizer.games.confirm-stop', [
            'game' => $game,
        ]);
    }
}
