<?php

use App\Http\Controllers\Auth\AcceptWorkshopInvitationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ClientStatusController;
use App\Http\Controllers\DemoSyncController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PilotLeadController;
use App\Http\Controllers\SignupAdminController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Workshop\CustomerController;
use App\Http\Controllers\Workshop\DashboardController;
use App\Http\Controllers\Workshop\LabelPrintController;
use App\Http\Controllers\Workshop\ProjectClientStatusController;
use App\Http\Controllers\Workshop\ProjectController;
use App\Http\Controllers\Workshop\ProjectMaterialController;
use App\Http\Controllers\Workshop\SashMaterialController;
use App\Http\Controllers\Workshop\SashPhotoController;
use App\Http\Controllers\Workshop\SashQrCodeController;
use App\Http\Controllers\Workshop\SashTimeEntryController;
use App\Http\Controllers\Workshop\WindowSashController;
use App\Http\Controllers\Workshop\WorkshopExportController;
use App\Http\Controllers\Workshop\WorkshopInvitationController;
use App\Http\Controllers\Workshop\WorkshopSettingsController;
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
Route::view('/guider', 'guides.index')->name('guides.index');
Route::view('/guider/dokumentera-fonsterrenovering', 'guides.document-renovation')->name('guides.document-renovation');
Route::redirect('/integritet', '/integritetspolicy', 301);
Route::view('/integritetspolicy', 'privacy')->name('privacy');
Route::view('/villkor', 'terms')->name('terms');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/manifest.webmanifest', ManifestController::class)->name('manifest');
Route::middleware(['admin', 'throttle:30,1'])->group(function (): void {
    Route::get('/admin/anmalningar', [SignupAdminController::class, 'index'])->name('admin.signups');
    Route::get('/admin/anmalningar.csv', [SignupAdminController::class, 'export'])->name('admin.signups.export');
});
Route::post('/intresse', PilotLeadController::class)
    ->middleware('throttle:5,1')
    ->name('pilot-leads.store');

Route::permanentRedirect('/login', '/logga-in');

Route::get('/inbjudan/{token}', [AcceptWorkshopInvitationController::class, 'create'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:30,1')
    ->name('invitations.accept');
Route::post('/inbjudan/{token}', [AcceptWorkshopInvitationController::class, 'store'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:10,1')
    ->name('invitations.accept.store');

Route::get('/status/{token}', [ClientStatusController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40,80}')
    ->middleware('throttle:60,1')
    ->name('client-status.show');
Route::get('/status/{token}/logotyp', [ClientStatusController::class, 'logo'])
    ->where('token', '[A-Za-z0-9]{40,80}')
    ->middleware('throttle:60,1')
    ->name('client-status.logo');
Route::get('/status/{token}/bilder/{photo}', [ClientStatusController::class, 'photo'])
    ->where('token', '[A-Za-z0-9]{40,80}')
    ->whereNumber('photo')
    ->middleware('throttle:60,1')
    ->name('client-status.photos');

Route::middleware('guest')->group(function (): void {
    Route::get('/registrera', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registrera', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1')->name('auth.register.store');

    Route::get('/logga-in', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/logga-in', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1')->name('auth.login.store');

    Route::get('/losenord/glomt', [PasswordResetLinkController::class, 'create'])->name('auth.password.request');
    Route::post('/losenord/glomt', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('auth.password.email');
    Route::get('/losenord/aterstall/{token}', [NewPasswordController::class, 'create'])->name('auth.password.reset');
    Route::post('/losenord/aterstall', [NewPasswordController::class, 'store'])->middleware('throttle:10,1')->name('auth.password.update');
});

Route::get('/email/verifiera/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function (): void {
    Route::post('/logga-ut', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'auth.session', 'verified.production'])->prefix('app')->name('app.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/export.csv', WorkshopExportController::class)->name('export');
    Route::get('/kunder', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/kunder/ny', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/kunder', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/kunder/{customer}/export.json', [CustomerController::class, 'exportJson'])->whereNumber('customer')->name('customers.export.json');
    Route::get('/kunder/{customer}/export.csv', [CustomerController::class, 'exportCsv'])->whereNumber('customer')->name('customers.export.csv');
    Route::delete('/kunder/{customer}', [CustomerController::class, 'destroy'])->whereNumber('customer')->name('customers.destroy');
    Route::get('/kunder/{customer}', [CustomerController::class, 'edit'])->whereNumber('customer')->name('customers.edit');
    Route::patch('/kunder/{customer}', [CustomerController::class, 'update'])->whereNumber('customer')->name('customers.update');
    Route::get('/installningar', [WorkshopSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/installningar', [WorkshopSettingsController::class, 'update'])->name('settings.update');
    Route::get('/installningar/logotyp', [WorkshopSettingsController::class, 'logo'])->name('settings.logo');
    Route::post('/installningar/inbjudningar', [WorkshopInvitationController::class, 'store'])->middleware('throttle:10,1')->name('invitations.store');
    Route::delete('/installningar/inbjudningar/{invitation}', [WorkshopInvitationController::class, 'destroy'])->whereNumber('invitation')->name('invitations.destroy');
    Route::delete('/installningar/medarbetare/{member}', [WorkshopInvitationController::class, 'removeMember'])->whereNumber('member')->name('members.destroy');
    Route::get('/projekt/nytt', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projekt', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projekt/{project}/rapport', [ProjectController::class, 'report'])->whereNumber('project')->name('projects.report');
    Route::get('/projekt/{project}/material', [ProjectMaterialController::class, 'show'])->whereNumber('project')->name('projects.materials');
    Route::post('/projekt/{project}/arkiv', [ProjectController::class, 'archive'])->whereNumber('project')->name('projects.archive');
    Route::get('/projekt/{project}/ta-bort', [ProjectController::class, 'confirmDestroy'])->whereNumber('project')->name('projects.confirm-destroy');
    Route::delete('/projekt/{project}', [ProjectController::class, 'destroy'])->whereNumber('project')->name('projects.destroy');
    Route::post('/projekt/{project}/statuslank', [ProjectClientStatusController::class, 'update'])->whereNumber('project')->name('projects.client-status.update');
    Route::get('/projekt/{project}/etiketter', [LabelPrintController::class, 'sheet'])->whereNumber('project')->name('projects.labels');
    Route::get('/projekt/{project}', [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
    Route::patch('/projekt/{project}', [ProjectController::class, 'update'])->whereNumber('project')->name('projects.update');
    Route::get('/projekt/{project}/bagar/nytt', [WindowSashController::class, 'create'])->whereNumber('project')->name('sashes.create');
    Route::post('/projekt/{project}/bagar', [WindowSashController::class, 'store'])->whereNumber('project')->name('sashes.store');
    Route::get('/projekt/{project}/bagar/{sash}/qr', SashQrCodeController::class)->whereNumber(['project', 'sash'])->name('sashes.qr');
    Route::get('/projekt/{project}/bagar/{sash}/etikett', [LabelPrintController::class, 'show'])->whereNumber(['project', 'sash'])->name('sashes.label');
    Route::get('/projekt/{project}/bagar/{sash}', [WindowSashController::class, 'show'])->whereNumber(['project', 'sash'])->name('sashes.show');
    Route::patch('/projekt/{project}/bagar/{sash}', [WindowSashController::class, 'update'])->whereNumber(['project', 'sash'])->name('sashes.update');
    Route::delete('/projekt/{project}/bagar/{sash}', [WindowSashController::class, 'destroy'])->whereNumber(['project', 'sash'])->name('sashes.destroy');
    Route::post('/projekt/{project}/bagar/{sash}/tid', [SashTimeEntryController::class, 'store'])->whereNumber(['project', 'sash'])->name('sashes.time.store');
    Route::delete('/projekt/{project}/bagar/{sash}/tid/{entry}', [SashTimeEntryController::class, 'destroy'])->whereNumber(['project', 'sash', 'entry'])->name('sashes.time.destroy');
    Route::post('/projekt/{project}/bagar/{sash}/material', [SashMaterialController::class, 'store'])->whereNumber(['project', 'sash'])->name('sashes.materials.store');
    Route::delete('/projekt/{project}/bagar/{sash}/material/{material}', [SashMaterialController::class, 'destroy'])->whereNumber(['project', 'sash', 'material'])->name('sashes.materials.destroy');
    Route::post('/projekt/{project}/bagar/{sash}/bilder', [SashPhotoController::class, 'store'])->middleware('throttle:10,1')->whereNumber(['project', 'sash'])->name('sashes.photos.store');
    Route::patch('/projekt/{project}/bagar/{sash}/bilder/{photo}', [SashPhotoController::class, 'update'])->whereNumber(['project', 'sash', 'photo'])->name('sashes.photos.update');
    Route::delete('/projekt/{project}/bagar/{sash}/bilder/{photo}', [SashPhotoController::class, 'destroy'])->whereNumber(['project', 'sash', 'photo'])->name('sashes.photos.destroy');
    Route::get('/projekt/{project}/bagar/{sash}/bilder/{photo}', [SashPhotoController::class, 'show'])->whereNumber(['project', 'sash', 'photo'])->name('sashes.photos.show');
});
