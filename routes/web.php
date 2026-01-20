<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', fn() => view('campaigns.index'))->name('index');
        Route::get('/create', fn() => view('campaigns.create'))->name('create');
    });

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', fn() => view('contacts.index'))->name('index');
    });

    Route::get('/leads', fn() => view('leads.index'))->name('leads.index');
    Route::get('/billing', fn() => view('billing.index'))->name('billing.index');
    Route::get('/reports', fn() => view('reports.index'))->name('reports.index');
    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
});

require __DIR__ . '/auth.php';
