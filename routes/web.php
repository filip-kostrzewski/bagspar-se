<?php

use App\Http\Controllers\PilotLeadController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/integritet', 'privacy')->name('privacy');
Route::post('/intresse', PilotLeadController::class)
    ->middleware('throttle:5,1')
    ->name('pilot-leads.store');
