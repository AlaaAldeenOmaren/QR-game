<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Question;
use App\Services\OrganizerGameContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class OrganizerQuestionController extends Controller
{
    public function __construct(private readonly OrganizerGameContext $gameContext)
    {
    }

    public function index(Request $request): View
    {
        $game = $this->gameContext->current($request);

        $questions = $game
            ? $game->questions()->withCount('answers')->orderBy('id')->get()
            : collect();

        return view('organizer.questions.index', [
            'game' => $game,
            'questions' => $questions,
        ]);
    }

    public function create(Request $request, Game $game): View|RedirectResponse
    {
        $this->ensureOwner($request, $game);

        if (! in_array($game->status, ['not_started', 'paused'], true)) {
            return redirect()
                ->route('organizer.questions.index', ['game' => $game->id])
                ->withErrors([
                    'game' => 'Je kunt alleen vragen toevoegen voordat het spel '
                        . 'start of wanneer het is gepauzeerd.',
                ]);
        }

        return view('organizer.questions.create', [
            'game' => $game,
        ]);
    }

    public function store(Request $request, Game $game): RedirectResponse
    {
        $this->ensureOwner($request, $game);

        $validated = $this->validateQuestion($request);

        $question = DB::transaction(
            function () use ($request, $game, $validated): Question {
                $currentGame = Game::query()
                    ->whereKey($game->getKey())
                    ->where(
                        'created_by',
                        $request->user()->getAuthIdentifier()
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array(
                    $currentGame->status,
                    ['not_started', 'paused'],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'game' => 'De spelstatus is gewijzigd. Je kunt nu geen vraag toevoegen.',
                    ]);
                }

                $question = $currentGame->questions()->create([
                    'question_text' => $validated['question_text'],
                    'type' => $validated['type'],
                    'max_points' => $validated['max_points'],
                ]);

                if ($validated['type'] === 'multiple_choice') {
                    foreach (['A', 'B', 'C'] as $label) {
                        $question->options()->create([
                            'label' => $label,
                            'option_text' => $validated['options'][$label],
                            'is_correct' => $validated['correct_option'] === $label,
                        ]);
                    }
                }

                return $question;
            }
        );

        return redirect()
            ->route('organizer.questions.edit', [
                'game' => $game,
                'question' => $question,
            ])
            ->with('success', 'De vraag is opgeslagen.');
    }

    public function edit(
        Request $request,
        Game $game,
        Question $question
    ): View {
        $this->ensureOwner($request, $game);

        $question->load('options');

        $canEdit = in_array(
            $game->status,
            ['not_started', 'paused'],
            true
        ) && !$question->answers()->exists();

        $questionUrl = route('student.questions.show', [
            'question' => $question->qr_token,
        ]);

        $qrCode = new QrCode(
            data: $questionUrl,
            size: 300,
            margin: 40
        );

        $qrDataUri = (new SvgWriter())
            ->write($qrCode)
            ->getDataUri();

        return view('organizer.questions.edit', [
            'game' => $game,
            'question' => $question,
            'canEdit' => $canEdit,
            'questionUrl' => $questionUrl,
            'qrDataUri' => $qrDataUri,
        ]);
    }

    public function update(
        Request $request,
        Game $game,
        Question $question
    ): RedirectResponse {
        $this->ensureOwner($request, $game);

        $validated = $this->validateQuestion($request);

        DB::transaction(function () use ($request, $game, $question, $validated): void {
            $currentGame = Game::query()
                ->whereKey($game->getKey())
                ->where('created_by', $request->user()->getAuthIdentifier())
                ->lockForUpdate()
                ->firstOrFail();

            $currentQuestion = $currentGame->questions()
                ->whereKey($question->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($currentGame->status, ['not_started', 'paused'], true)) {
                throw ValidationException::withMessages([
                    'game' => 'Vragen kunnen alleen vóór de start of tijdens '
                        . 'een pauze worden gewijzigd.',
                ]);
            }

            if ($currentQuestion->answers()->exists()) {
                throw ValidationException::withMessages([
                    'question' => 'Deze vraag heeft al antwoorden en kan '
                        . 'niet meer worden gewijzigd.',
                ]);
            }

            $currentQuestion->update([
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'max_points' => $validated['max_points'],
            ]);

            if ($validated['type'] === 'multiple_choice') {
                foreach (['A', 'B', 'C'] as $label) {
                    $currentQuestion->options()->updateOrCreate(
                        ['label' => $label],
                        [
                            'option_text' => $validated['options'][$label],
                            'is_correct' => $validated['correct_option'] === $label,
                        ]
                    );
                }
            } else {
                $currentQuestion->options()->delete();
            }
        });

        return redirect()
            ->route('organizer.questions.edit', [
                'game' => $game,
                'question' => $question,
            ])
            ->with('success', 'De wijzigingen zijn opgeslagen.');
    }

    private function validateQuestion(Request $request): array
    {
        $choiceRule = 'exclude_unless:type,multiple_choice';

        return $request->validate([
            'question_text' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', 'in:multiple_choice,open'],
            'max_points' => ['required', 'integer', 'between:1,65535'],

            'options' => [
                $choiceRule,
                'required',
                'array:A,B,C',
                'required_array_keys:A,B,C',
            ],

            'options.*' => [
                $choiceRule,
                'bail',
                'required',
                'string',
                'max:255',
                'distinct:ignore_case',
            ],

            'correct_option' => [
                $choiceRule,
                'required',
                'string',
                'in:A,B,C',
            ],
        ], [
            'required' => 'Vul het veld :attribute in.',
            'string' => 'Het veld :attribute moet tekst bevatten.',
            'in' => 'Kies een geldige waarde voor :attribute.',
            'integer' => 'Het veld :attribute moet een heel getal zijn.',
            'between' => 'Het veld :attribute moet tussen :min en :max liggen.',
            'max' => 'Het veld :attribute mag maximaal :max tekens bevatten.',
            'array' => 'Gebruik alleen de antwoordopties A, B en C.',
            'required_array_keys' => 'Vul antwoord A, B en C in.',
            'distinct' => 'De antwoordopties moeten verschillend zijn.',
        ], [
            'question_text' => 'vraag',
            'type' => 'type',
            'max_points' => 'maximale punten',
            'options' => 'antwoordopties',
            'options.A' => 'antwoord A',
            'options.B' => 'antwoord B',
            'options.C' => 'antwoord C',
            'correct_option' => 'juiste antwoord',
        ]);
    }

    private function ensureOwner(Request $request, Game $game): void
    {
        abort_unless(
            (string) $game->created_by ===
                (string) $request->user()->getAuthIdentifier(),
            404
        );

        $this->gameContext->select($request, $game);
    }
}
