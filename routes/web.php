<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\CampaignController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ContactController;
use App\Http\Controllers\User\ContactGroupController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\SmtpController;
use App\Http\Controllers\Admin\PaymentController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');

    Route::post('/campaigns/{campaign}/assign', [AdminCampaignController::class, 'assignSmtp'])->name('campaigns.assign');

    Route::get('/campaigns/{campaign}/toggle', [AdminCampaignController::class, 'toggleStatus'])->name('campaigns.toggle');

    Route::resource('packages', AdminPackageController::class);

    Route::resource('smtps', SmtpController::class);

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::post('/payments/override/{user}', [PaymentController::class, 'override'])->name('payments.override');
});

// User Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Campaigns
    Route::post('/campaigns/step-two', [CampaignController::class, 'stepTwo'])->name('campaigns.stepTwo');
    Route::post('/campaigns/step-three', [CampaignController::class, 'stepThree'])->name('campaigns.stepThree');
    Route::resource('campaigns', CampaignController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/toggle', [ReportController::class, 'toggleAutoReport'])->name('reports.toggle');
    Route::get('/reports/export/{campaign}', [ReportController::class, 'export'])->name('reports.export');

    // Placeholder Views
    Route::get('/leads', fn() => view('leads.index'))->name('leads.index');
    Route::get('/billing', fn() => view('user.billing.index'))->name('billing.index');

    
    // Contacts
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');


    Route::resource('groups', ContactGroupController::class);



    Route::get('/settings', fn() => view('settings.index'))->name('settings.index');
});

require __DIR__ . '/auth.php';
