<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameParticipant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentProgressController extends Controller
{
    public function show(Request $request, Game $game): View
    {
        $participant = $this->findParticipant($request, $game);

        if (!$participant) {
            return view('student.progress.resume', [
                'game' => $game,
            ]);
        }

        $answers = $participant->answers()
            ->with(['question', 'selectedOption'])
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->get();

        return view('student.progress.show', [
            'game' => $game,
            'participant' => $participant,
            'answers' => $answers,
            'questionCount' => $game->questions()->count(),
            'totalPoints' => (int) $answers->sum('points_awarded'),
            'pendingCount' => $answers->whereNull('points_awarded')->count(),
        ]);
    }

    public function play(Request $request, Game $game): View|RedirectResponse
    {
        $participant = $this->findParticipant($request, $game);

        if (!$participant) {
            return redirect()->route('student.progress.show', [
                'game' => $game,
            ]);
        }

        return view('student.play', [
            'game' => $game,
            'participant' => $participant,
            'totalPoints' => (int) $participant->answers()->sum('points_awarded'),
        ]);
    }

    public function resume(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:20',
                'regex:/\A[0-9]+\z/',
            ],
        ], [
            'student_number.required' => 'Vul je studentnummer in.',
            'student_number.string' => 'Vul een geldig studentnummer in.',
            'student_number.max' => 'Gebruik maximaal 20 cijfers.',
            'student_number.regex' => 'Gebruik alleen cijfers.',
        ]);

        $participant = $game->participants()
            ->whereHas('student', function (Builder $query) use ($validated): void {
                $query->where(
                    'student_number',
                    $validated['student_number']
                );
            })
            ->first();

        if (!$participant) {
            throw ValidationException::withMessages([
                'student_number' => 'Geen deelname gevonden voor dit spel. Open eerst een QR-code van het spel om mee te doen.',
            ]);
        }

        $request->session()->regenerate();

        $request->session()->put(
            'student_participants.' . $game->id,
            $participant->id
        );

        return redirect()->route('student.progress.show', [
            'game' => $game,
        ]);
    }

    private function findParticipant(
        Request $request,
        Game $game
    ): ?GameParticipant {
        $participantId = $request->session()->get(
            'student_participants.' . $game->id
        );

        if (!$participantId) {
            return null;
        }

        return $game->participants()
            ->with('student')
            ->find($participantId);
    }
}
