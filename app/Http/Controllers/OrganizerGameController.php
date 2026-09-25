<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Services\OrganizerGameContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerGameController extends Controller
{
    public function __construct(private readonly OrganizerGameContext $gameContext)
    {
    }

    public function index(Request $request): View
    {
        /** @var User $organizer */
        $organizer = $request->user();

        $games = $organizer->createdGames()
            ->withCount(['questions', 'participants'])
            ->latest('id')
            ->paginate(10);

        return view('organizer.games.index', [
            'games' => $games,
            'selectedGame' => $this->gameContext->current($request),
        ]);
    }

    public function create(Request $request): View
    {
        return view('organizer.games.create', [
            'selectedGame' => $this->gameContext->current($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'Vul een naam voor het spel in.',
            'name.string' => 'Vul een geldige spelnaam in.',
            'name.max' => 'De spelnaam mag maximaal 150 tekens bevatten.',
        ]);

        /** @var User $organizer */
        $organizer = $request->user();

        $game = $organizer->createdGames()->create([
            'name' => $validated['name'],
            'status' => 'not_started',
        ]);

        $this->gameContext->select($request, $game);

        return redirect()->route('dashboard', ['game' => $game->id])
            ->with('success', 'Het nieuwe spel is aangemaakt.');
    }

    public function select(Request $request, Game $game): RedirectResponse
    {
        $this->gameContext->select($request, $game);

        return redirect()->route('dashboard', ['game' => $game->id]);
    }
}
