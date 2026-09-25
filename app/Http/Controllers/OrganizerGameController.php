<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerGameController extends Controller
{
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
        ]);
    }

    public function create(): View
    {
        return view('organizer.games.create');
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

        $organizer->createdGames()->create([
            'name' => $validated['name'],
            'status' => 'not_started',
        ]);

        return redirect()->route('organizer.games.index')
            ->with('success', 'Het nieuwe spel is aangemaakt.');
    }
}
