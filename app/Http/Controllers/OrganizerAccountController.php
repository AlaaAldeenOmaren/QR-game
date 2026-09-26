<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OrganizerGameContext;
use App\Support\AccountRules;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizerAccountController extends Controller
{
    public function __construct(private readonly OrganizerGameContext $gameContext)
    {
    }

    public function index(Request $request): View
    {
        return view('organizer.accounts.index', [
            'accounts' => User::query()->withCount('createdGames')->orderBy('name')->orderBy('id')->paginate(10),
            'selectedGame' => $this->gameContext->current($request),
        ]);
    }

    public function create(Request $request): View
    {
        return view('organizer.accounts.create', [
            'selectedGame' => $this->gameContext->current($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $validated = $request->validate(AccountRules::rules(), AccountRules::messages());

        $account = new User();
        $account->name = $validated['name'];
        $account->email = $validated['email'];
        $account->password = Hash::make($validated['password']);
        // Web-created accounts are always organizers, regardless of posted fields.
        $account->is_admin = false;

        try {
            $account->save();
        } catch (UniqueConstraintViolationException $exception) {
            // Handle a second request creating the same email after validation.
            throw ValidationException::withMessages([
                'email' => 'Er bestaat al een account met dit e-mailadres.',
            ]);
        }

        return redirect()->route('organizer.accounts.index')
            ->with('success', 'Het organisatoraccount is aangemaakt. De organisator kan nu inloggen.');
    }
}
