<?php

use App\Http\Controllers\DemoSyncController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PilotLeadController;
use App\Http\Controllers\SignupAdminController;
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
Route::view('/fonsterrenovering-dokumentation', 'guides.documentation')->name('guides.documentation');
Route::view('/arbetskort-fonsterbagar', 'guides.work-card')->name('guides.work-card');
Route::view('/markning-av-fonsterbagar', 'guides.marking')->name('guides.marking');
Route::redirect('/integritet', '/integritetspolicy', 301);
Route::view('/integritetspolicy', 'privacy')->name('privacy');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/manifest.webmanifest', ManifestController::class)->name('manifest');
Route::middleware(['admin', 'throttle:30,1'])->group(function (): void {
    Route::get('/admin/anmalningar', [SignupAdminController::class, 'index'])->name('admin.signups');
    Route::get('/admin/anmalningar.csv', [SignupAdminController::class, 'export'])->name('admin.signups.export');
});
Route::post('/intresse', PilotLeadController::class)
    ->middleware('throttle:5,1')
    ->name('pilot-leads.store');
