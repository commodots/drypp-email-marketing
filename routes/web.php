<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/logout', function (Request $request) {
    Auth::logout();
 
    $request->session()->invalidate();
    $request->session()->regenerateToken();
 
    return redirect('/');
})->name('logout');

    Route::resource('campaigns', CampaignController::class)->only(['index', 'create', 'store']);
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', fn() => view('contacts.index'))->name('index');
    });

    Route::get('/leads', fn() => view('leads.index'))->name('leads.index');
    Route::get('/billing', fn() => view('billing.index'))->name('billing.index');
    Route::get('/reports', fn() => view('reports.index'))->name('reports.index');
    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
});

require __DIR__ . '/auth.php';
