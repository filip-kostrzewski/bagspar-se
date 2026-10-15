<?php

use App\Http\Controllers\DemoSyncController;
use App\Http\Controllers\PilotLeadController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/demo', 'demo')->name('demo');
Route::get('/demo/synk', [DemoSyncController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('demo.sync.show');
Route::post('/demo/synk', [DemoSyncController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('demo.sync.store');
Route::redirect('/integritet', '/integritetspolicy', 301);
Route::view('/integritetspolicy', 'privacy')->name('privacy');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::post('/intresse', PilotLeadController::class)
    ->middleware('throttle:5,1')
    ->name('pilot-leads.store');
