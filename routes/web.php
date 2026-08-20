<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyDocumentController;
use App\Http\Controllers\PropertyEndorsementController;
use App\Http\Controllers\PropertyReportController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests as Precognition;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware(Precognition::class);
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}/active', [UserController::class, 'updateActive'])->name('users.active.update');
    Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware(Precognition::class);

    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{audit}', [AuditController::class, 'show'])->name('audit.show');

    Route::prefix('people')->group(function () {
        Route::get('/', [PersonController::class, 'index'])->name('people.index');
        Route::get('/create', [PersonController::class, 'create'])->name('people.create');
        Route::post('/', [PersonController::class, 'store'])->name('people.store')->middleware(Precognition::class);
        Route::get('/{person}/edit', [PersonController::class, 'edit'])->name('people.edit');
        Route::match(['put', 'patch'], '/{person}', [PersonController::class, 'update'])->name('people.update')->middleware(Precognition::class);
        Route::delete('/{person}', [PersonController::class, 'destroy'])->name('people.destroy');
    });

    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('/create', [PropertyController::class, 'create'])->name('properties.create');
        Route::get('/report', [PropertyReportController::class, 'synthetic'])->name('properties.report.synthetic');
        Route::post('/', [PropertyController::class, 'store'])->name('properties.store')->middleware(Precognition::class);
        Route::get('/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::get('/{property}/report', [PropertyReportController::class, 'individual'])->name('properties.report.individual');
        Route::match(['put', 'patch'], '/{property}', [PropertyController::class, 'update'])->name('properties.update')->middleware(Precognition::class);
        Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
        Route::post('/{property}/endorsements', [PropertyEndorsementController::class, 'store'])
            ->name('properties.endorsements.store')
            ->middleware(Precognition::class);
        Route::post('/{property}/documents', [PropertyDocumentController::class, 'store'])->name('properties.documents.store');
        Route::get('/{property}/documents/{document}', [PropertyDocumentController::class, 'show'])->name('properties.documents.show');
        Route::get('/{property}/documents/{document}/download', [PropertyDocumentController::class, 'download'])->name('properties.documents.download');
        Route::delete('/{property}/documents/{document}', [PropertyDocumentController::class, 'destroy'])->name('properties.documents.destroy');
    });
});
