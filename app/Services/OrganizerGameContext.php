<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizerGameContext
{
    public function current(Request $request): ?Game
    {
        /** @var User $organizer */
        $organizer = $request->user();

        // An explicit link keeps navigation on the game shown on that page.
        if ($request->query->has('game')) {
            $gameId = $request->query('game');

            abort_unless(is_string($gameId) && ctype_digit($gameId), 404);

            $game = $organizer->createdGames()->whereKey($gameId)->firstOrFail();
            $this->select($request, $game);

            return $game;
        }

        $sessionKey = $this->sessionKey($request);
        $gameId = $request->session()->get($sessionKey);

        if ($gameId !== null) {
            $game = $organizer->createdGames()->whereKey($gameId)->first();

            if ($game !== null) {
                return $game;
            }

            $request->session()->forget($sessionKey);
        }

        // Only choose the newest owned game when there is no valid selection.
        $game = $organizer->createdGames()->latest('id')->first();

        if ($game !== null) {
            $this->select($request, $game);
        }

        return $game;
    }

    public function select(Request $request, Game $game): void
    {
        abort_unless(
            (string) $game->created_by === (string) $request->user()->getAuthIdentifier(),
            404
        );

        $request->session()->put($this->sessionKey($request), $game->getKey());
    }

    private function sessionKey(Request $request): string
    {
        return 'organizer_current_games.' . $request->user()->getAuthIdentifier();
    }
}
