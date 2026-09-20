<?php

use App\Http\Controllers\OrganizerAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizerDashboardController;
use App\Http\Controllers\GameStateController;
use App\Http\Controllers\OrganizerQuestionController;
use App\Http\Controllers\StudentQuestionController;
use App\Http\Controllers\OrganizerGradingController;
use App\Http\Controllers\OrganizerResultsController;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/beheer/inloggen', [
        OrganizerAuthController::class,
        'create',
    ])->name('login');

    Route::post('/beheer/inloggen', [
        OrganizerAuthController::class,
        'store',
    ])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/beheer', [
        OrganizerDashboardController::class,
        'index',
    ])->name('dashboard');

    Route::get('/beheer/spellen/{game}/stoppen', [
        GameStateController::class,
        'confirmStop',
    ])->name('organizer.games.stop.confirm');

    Route::post('/beheer/spellen/{game}/status', [
        GameStateController::class,
        'update',
    ])->name('organizer.games.state');

    Route::get('/beheer/vragen', [
        OrganizerQuestionController::class,
        'index',
    ])->name('organizer.questions.index');

    Route::get('/beheer/spellen/{game}/vragen/nieuw', [
        OrganizerQuestionController::class,
        'create',
    ])->name('organizer.questions.create');

    Route::post('/beheer/spellen/{game}/vragen', [
        OrganizerQuestionController::class,
        'store',
    ])->name('organizer.questions.store');

    Route::get('/beheer/spellen/{game}/vragen/{question}/bewerken', [
        OrganizerQuestionController::class,
        'edit',
    ])->scopeBindings()->name('organizer.questions.edit');

    Route::put('/beheer/spellen/{game}/vragen/{question}', [
        OrganizerQuestionController::class,
        'update',
    ])->scopeBindings()->name('organizer.questions.update');

    Route::get('/beheer/nakijken', [
        OrganizerGradingController::class,
        'index',
    ])->name('organizer.grading.index');

    Route::get('/beheer/spellen/{game}/antwoorden/{answer}/beoordelen', [
        OrganizerGradingController::class,
        'edit',
    ])->scopeBindings()->name('organizer.grading.edit');

    Route::put('/beheer/spellen/{game}/antwoorden/{answer}/beoordelen', [
        OrganizerGradingController::class,
        'update',
    ])->scopeBindings()->name('organizer.grading.update');

    Route::post('/beheer/uitloggen', [
        OrganizerAuthController::class,
        'destroy',
    ])->name('logout');

    Route::get('/beheer/resultaten', [
        OrganizerResultsController::class,
        'index',
    ])->name('organizer.results.index');

    Route::get('/beheer/spellen/{game}/resultaten/export', [
        OrganizerResultsController::class,
        'export',
    ])->name('organizer.results.export');
});

Route::get('/spelen/vragen/{question:qr_token}', [
    StudentQuestionController::class,
    'show',
])->name('student.questions.show');

Route::post('/spelen/vragen/{question:qr_token}/deelnemen', [
    StudentQuestionController::class,
    'join',
])->middleware('throttle:10,1')->name('student.join.store');

Route::post('/spelen/vragen/{question:qr_token}/antwoord', [
    StudentQuestionController::class,
    'storeAnswer',
])->middleware('throttle:20,1')->name('student.answers.store');

Route::get('/spelen/vragen/{question:qr_token}/antwoord', [
    StudentQuestionController::class,
    'result',
])->name('student.answers.show');
