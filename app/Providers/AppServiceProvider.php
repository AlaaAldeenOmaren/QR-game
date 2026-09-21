<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer(
            ['errors::404', 'errors.404'],
            function (\Illuminate\View\View $view): void {
                $request = request();

                $isQuestionLink = $request->is('spelen/vragen/*');
                $game = null;
                $participant = null;

                if ($isQuestionLink && $request->hasSession()) {
                    $gameId = $request->session()->get('student_current_game_id');

                    if ($gameId) {
                        $game = \App\Models\Game::find($gameId);

                        if ($game) {
                            $participantId = $request->session()->get(
                                'student_participants.' . $game->id
                            );

                            if ($participantId) {
                                $participant = $game->participants()
                                    ->with('student')
                                    ->find($participantId);
                            }
                        }
                    }
                }

                $view->with([
                    'isQuestionLink' => $isQuestionLink,
                    'game' => $game,
                    'participant' => $participant,
                ]);
            }
        );
    }
}
