<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PropertyController;
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
        Route::post('/', [PropertyController::class, 'store'])->name('properties.store')->middleware(Precognition::class);
        Route::get('/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::match(['put', 'patch'], '/{property}', [PropertyController::class, 'update'])->name('properties.update')->middleware(Precognition::class);
        Route::patch('/{property}/status', [PropertyController::class, 'updateStatus'])->name('properties.status.update');
        Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    });
});
