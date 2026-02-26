<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\MfaController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\EventController as PublicEventController;
use App\Http\Controllers\Public\PhotoReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicEventController::class, 'index'])->name('public.events.index');

Route::get('/evento/{year}/{month}/{slug}', [PublicEventController::class, 'show'])
    ->where(['year' => '\d{4}', 'month' => '\d{2}'])
    ->name('public.events.show');

Route::post('/evento/{year}/{month}/{slug}/foto/{photoUpload}/segnala', [PhotoReportController::class, 'store'])
    ->where(['year' => '\d{4}', 'month' => '\d{2}'])
    ->middleware('throttle:5,60')
    ->name('public.photo-report.store');

Route::get('/evento/{year}/{month}/{slug}/foto/{photoUpload}/scarica', [PublicEventController::class, 'download'])
    ->where(['year' => '\d{4}', 'month' => '\d{2}'])
    ->name('public.events.photos.download');

/*
|--------------------------------------------------------------------------
| Authenticated user routes (Breeze)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', fn () => redirect('/admin'))
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin — MFA challenge (auth only, no mfa.verified needed)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('mfa/verify',  [MfaController::class, 'show'])->name('mfa.verify');
    Route::post('mfa/verify', [MfaController::class, 'verify'])->name('mfa.verify.submit')->middleware('throttle:10,1');
    Route::post('mfa/resend', [MfaController::class, 'resend'])->name('mfa.resend')->middleware('throttle:3,5');
});

/*
|--------------------------------------------------------------------------
| Admin — Protected panel (auth + mfa.verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'mfa.verified'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('events', AdminEventController::class)->except(['show']);

    // Photo management
    Route::get('events/{event}/photos',                       [PhotoController::class, 'show'])->name('events.photos');
    Route::post('events/{event}/photos',                      [PhotoController::class, 'upload'])->name('events.photos.upload');
    Route::delete('events/{event}/photos',                    [PhotoController::class, 'revert'])->name('events.photos.revert');
    Route::get('events/{event}/photos/status',                [PhotoController::class, 'status'])->name('events.photos.status');
    Route::post('events/{event}/photos/reorder',              [PhotoController::class, 'reorder'])->name('events.photos.reorder');
    Route::post('events/{event}/photos/bulk-destroy',         [PhotoController::class, 'bulkDestroy'])->name('events.photos.bulk-destroy');
    Route::post('events/{event}/photos/{photoUpload}/cover',  [PhotoController::class, 'setCover'])->name('events.photos.cover');
    Route::delete('events/{event}/photos/{photoUpload}',      [PhotoController::class, 'destroy'])->name('events.photos.destroy');

    Route::get('reports',                   [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/{report}/hide',    [ReportController::class, 'hidePhoto'])->name('reports.hide');
    Route::post('reports/{report}/ignore',  [ReportController::class, 'ignore'])->name('reports.ignore');
    Route::delete('reports/{report}',       [ReportController::class, 'destroy'])->name('reports.destroy');

    Route::get('settings',   [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__ . '/auth.php';
