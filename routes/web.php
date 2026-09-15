<?php

use App\Http\Controllers\OrganizerAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizerDashboardController;
use App\Http\Controllers\GameStateController;
use App\Http\Controllers\OrganizerQuestionController;
use App\Http\Controllers\StudentQuestionController;
use App\Http\Controllers\OrganizerGradingController;

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

