<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $gameId = $request->session()->get('student_current_game_id');
        $game = $gameId ? Game::find($gameId) : null;
        $participant = null;

        if ($game) {
            $participantId = $request->session()->get(
                'student_participants.' . $game->id
            );

            if ($participantId) {
                $participant = $game->participants()->find($participantId);
            }
        }

        return view('home', [
            'game' => $game,
            'participant' => $participant,
        ]);
    }
}
