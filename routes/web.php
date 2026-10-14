<?php

use App\Http\Controllers\PilotLeadController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/integritet', 'privacy')->name('privacy');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::post('/intresse', PilotLeadController::class)
    ->middleware('throttle:5,1')
    ->name('pilot-leads.store');
