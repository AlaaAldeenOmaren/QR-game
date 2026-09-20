<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganizerGradingController extends Controller
{
    public function index(Request $request): View
    {
        $game = Game::query()
            ->where('created_by', $request->user()->getAuthIdentifier())
            ->latest('id')
            ->first();

        $answers = $game
            ? $game->answers()
                ->whereHas('question', function (Builder $query): void {
                    $query->where('type', 'open');
                })
                ->with(['question', 'participant.student'])
                ->orderBy('graded_at')
                ->orderBy('submitted_at')
                ->get()
            : collect();

        $pendingCount = $answers
            ->filter(fn (Answer $answer): bool => $answer->graded_at === null)
            ->count();

        return view('organizer.grading.index', [
            'game' => $game,
            'answers' => $answers,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function edit(
        Request $request,
        Game $game,
        Answer $answer
    ): View {
        $this->ensureOwner($request, $game);

        $answer->load(['question', 'participant.student']);

        abort_unless($answer->question->type === 'open', 404);

        return view('organizer.grading.edit', [
            'game' => $game,
            'answer' => $answer,
        ]);
    }

    public function update(
        Request $request,
        Game $game,
        Answer $answer
    ): RedirectResponse {
        $this->ensureOwner($request, $game);

        DB::transaction(function () use ($request, $game, $answer): void {
            $currentGame = Game::query()
                ->whereKey($game->id)
                ->where(
                    'created_by',
                    $request->user()->getAuthIdentifier()
                )
                ->lockForUpdate()
                ->firstOrFail();

            $currentAnswer = $currentGame->answers()
                ->whereKey($answer->id)
                ->with('question')
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($currentAnswer->question->type === 'open', 404);

            $maxPoints = (int) $currentAnswer->question->max_points;

            $validated = $request->validate([
                'points_awarded' => [
                    'bail',
                    'required',
                    'integer',
                    'between:0,' . $maxPoints,
                ],
                'feedback' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ], [
                'points_awarded.required' => 'Vul het aantal punten in.',
                'points_awarded.integer' => 'Gebruik een geheel getal.',
                'points_awarded.between' => "Geef een score tussen 0 en {$maxPoints}.",
                'feedback.string' => 'Vul geldige feedback in.',
                'feedback.max' => 'Gebruik maximaal 2000 tekens voor de feedback.',
            ]);

            $currentAnswer->points_awarded = (int) $validated['points_awarded'];
            $currentAnswer->feedback = $validated['feedback'] ?? null;
            $currentAnswer->graded_by = $request->user()->getAuthIdentifier();
            $currentAnswer->graded_at = now();

            $currentAnswer->save();
        });

        return redirect()
            ->route('organizer.grading.index')
            ->with('success', 'De beoordeling is opgeslagen.');
    }

    private function ensureOwner(Request $request, Game $game): void
    {
        abort_unless(
            (string) $game->created_by ===
            (string) $request->user()->getAuthIdentifier(),
            404
        );
    }
}
