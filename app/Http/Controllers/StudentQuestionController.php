<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Game;
use App\Models\GameParticipant;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class StudentQuestionController extends Controller
{
    public function show(
        Request $request,
        Question $question
    ): View {
        $game = $question->game;
        $participant = $this->findParticipant($request, $game);

        if (
            $participant &&
            $participant->answers()
            ->where('question_id', $question->id)
            ->exists()
        ) {
            return view('student.questions.already-answered', [
                'game' => $game,
                'question' => $question,
                'participant' => $participant,
            ]);
        }

        $questionVersion = null;

        if ($game->status === 'active' && $participant) {
            $question->load('options');
            $questionVersion = $this->questionVersion($question);
        }

        return view('student.questions.show', [
            'game' => $game,
            'question' => $question,
            'participant' => $participant,
            'questionVersion' => $questionVersion,
        ]);
    }

    public function join(
        Request $request,
        Question $question
    ): RedirectResponse {
        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]+$/',
            ],
        ], [
            'student_number.required' => 'Vul je studentnummer in.',
            'student_number.string' => 'Vul een geldig studentnummer in.',
            'student_number.max' => 'Gebruik maximaal 20 cijfers.',
            'student_number.regex' => 'Gebruik alleen cijfers voor je studentnummer.',
        ]);

        $participant = DB::transaction(
            function () use ($question, $validated): ?GameParticipant {
                $game = Game::query()
                    ->whereKey($question->game_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($game->status !== 'active') {
                    return null;
                }

                $student = Student::query()->firstOrCreate([
                    'student_number' => $validated['student_number'],
                ]);

                return $game->participants()->firstOrCreate(
                    ['student_id' => $student->id],
                    ['joined_at' => now()]
                );
            }
        );

        if ($participant) {
            $request->session()->regenerate();

            $request->session()->put(
                'student_participants.' . $participant->game_id,
                $participant->id
            );
        }

        return redirect()->route('student.questions.show', [
            'question' => $question->qr_token,
        ]);
    }

    public function storeAnswer(
        Request $request,
        Question $question
    ): RedirectResponse|JsonResponse {
        $response = DB::transaction(
            function () use ($request, $question): RedirectResponse {
                $game = Game::query()
                    ->whereKey($question->game_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $participant = $this->findParticipant($request, $game);

                if (!$participant) {
                    throw ValidationException::withMessages([
                        'student' => 'Vul opnieuw je studentnummer in.',
                    ])->redirectTo(
                        route('student.questions.show', [
                            'question' => $question->qr_token,
                        ])
                    );
                }

                $alreadyAnswered = $participant->answers()
                    ->where('question_id', $question->id)
                    ->exists();

                if ($alreadyAnswered) {
                    return redirect()->route('student.questions.show', [
                        'question' => $question->qr_token,
                    ]);
                }

                if ($game->status !== 'active') {
                    throw ValidationException::withMessages([
                        'game' => 'Je antwoord is niet opgeslagen. Het spel is niet actief.',
                    ])->redirectTo(
                        route('student.questions.show', [
                            'question' => $question->qr_token,
                        ])
                    );
                }

                $currentQuestion = $game->questions()
                    ->whereKey($question->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentQuestion->load('options');

                $versionData = $request->validate([
                    'question_version' => ['required', 'string', 'size:64'],
                ], [
                    'question_version.required' => 'Vernieuw de pagina en probeer opnieuw.',
                    'question_version.string' => 'Vernieuw de pagina en probeer opnieuw.',
                    'question_version.size' => 'Vernieuw de pagina en probeer opnieuw.',
                ]);

                if (!hash_equals(
                    $this->questionVersion($currentQuestion),
                    $versionData['question_version']
                )) {
                    throw ValidationException::withMessages([
                        'question_version' => 'De vraag is gewijzigd. Controleer de vraag en verstuur je antwoord opnieuw.',
                    ]);
                }

                $isMultipleChoice = $currentQuestion->type === 'multiple_choice';

                $validated = $request->validate([
                    'selected_option_id' => $isMultipleChoice
                        ? [
                            'bail',
                            'required',
                            'integer',
                            Rule::in($currentQuestion->options->modelKeys()),
                        ]
                        : ['prohibited'],

                    'answer_text' => $isMultipleChoice
                        ? ['prohibited']
                        : ['bail', 'required', 'string', 'max:2000'],
                ], [
                    'selected_option_id.required' => 'Kies een antwoord.',
                    'selected_option_id.integer' => 'Kies een geldig antwoord.',
                    'selected_option_id.in' => 'Dit antwoord hoort niet bij deze vraag.',
                    'selected_option_id.prohibited' => 'Schrijf je antwoord in het tekstveld.',
                    'answer_text.required' => 'Schrijf je antwoord.',
                    'answer_text.string' => 'Vul een geldig antwoord in.',
                    'answer_text.max' => 'Gebruik maximaal 2000 tekens.',
                    'answer_text.prohibited' => 'Kies een van de antwoordopties.',
                ]);

                $answer = new Answer();
                $answer->game_id = $game->id;
                $answer->participant_id = $participant->id;
                $answer->question_id = $currentQuestion->id;
                $answer->selected_option_id = null;
                $answer->answer_text = null;
                $answer->points_awarded = null;
                $answer->graded_by = null;
                $answer->graded_at = null;
                $answer->submitted_at = now();

                if ($isMultipleChoice) {
                    $selectedOption = $currentQuestion->options->firstWhere(
                        'id',
                        (int) $validated['selected_option_id']
                    );

                    $answer->selected_option_id = $selectedOption->id;
                    $answer->points_awarded = $selectedOption->is_correct
                        ? $currentQuestion->max_points
                        : 0;
                    $answer->graded_at = now();
                } else {
                    $answer->answer_text = $validated['answer_text'];
                }

                $answer->save();

                return redirect()->route('student.answers.show', [
                    'question' => $currentQuestion->qr_token,
                ]);
            }
        );

        if ($request->expectsJson()) {
            return response()->json([
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        return $response;
    }

    public function result(
        Request $request,
        Question $question
    ): View|RedirectResponse {
        $game = $question->game;
        $participant = $this->findParticipant($request, $game);

        if (!$participant) {
            return redirect()->route('student.questions.show', [
                'question' => $question->qr_token,
            ]);
        }

        $answer = $participant->answers()
            ->where('question_id', $question->id)
            ->with('selectedOption')
            ->first();

        if (!$answer) {
            return redirect()->route('student.questions.show', [
                'question' => $question->qr_token,
            ]);
        }

        $correctAnswer = $question->type === 'multiple_choice'
            ? $question->options()
            ->where('is_correct', true)
            ->value('option_text')
            : null;

        $totalPoints = (int) $participant->answers()
            ->sum('points_awarded');

        return view('student.answers.show', [
            'game' => $game,
            'question' => $question,
            'participant' => $participant,
            'answer' => $answer,
            'correctAnswer' => $correctAnswer,
            'totalPoints' => $totalPoints,
        ]);
    }

    private function findParticipant(Request $request, Game $game): ?GameParticipant
    {
        $request->session()->put('student_current_game_id', $game->id);

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

    private function questionVersion(Question $question): string
    {
        $data = [
            $question->id,
            $question->game_id,
            $question->question_text,
            $question->type,
            $question->max_points,
            $question->options
                ->sortBy('id')
                ->map(fn($option): array => [
                    $option->id,
                    $option->label,
                    $option->option_text,
                    (bool) $option->is_correct,
                ])
                ->values()
                ->all(),
        ];

        return hash_hmac(
            'sha256',
            json_encode($data, JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }
}
