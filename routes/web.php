<?php

use App\Http\Controllers\HockeyController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PicksController;
use App\Http\Controllers\PaypalAdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\ResultsMonthlyController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TennisController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckSubcriptionStatus;
use Illuminate\Foundation\Application;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(CheckSubcriptionStatus::class)->group(function () {
    // Route::get('/baseball', [PicksController::class, 'create'])->name('baseball');
    Route::get('/baseball', [SubscriptionController::class, 'picks'])->name('picks');
    Route::get('/hockey', [HockeyController::class, 'create'])->name('hockey');
    Route::get('/tennis', [TennisController::class, 'create'])->name('tennis');
    Route::get('/results/daily', [ResultsController::class, 'create'])->name('results.daily');
    Route::get('/results/monthly', [ResultsMonthlyController::class, 'create'])->name('results.monthly');
});


Route::post('paypal', [PaypalController::class, 'paypal'])->name('paypal');
Route::get('success', [PaypalController::class, 'success'])->name('success');
Route::get('cancel', [PaypalController::class, 'cancel'])->name('cancel');

// Admin for subscription handling and creating plans
Route::middleware([CheckAdmin::class, 'auth'])->prefix('admin')->group(function () {
    Route::get('/main', [PaypalAdminController::class, 'main'])->name('main');
    Route::get('/show_plans', [PaypalAdminController::class, 'showPlans'])->name('show_plans');
    Route::post('/create_plans', [PaypalAdminController::class, 'createPlan'])->name('create_plans');
    Route::post('/webhook', [PaypalAdminController::class, 'webhook'])->withoutMiddleware([CheckAdmin::class, 'auth'])->name('webhook');
});

// Route::post('admin/webhook', [PaypalAdminController::class, 'webhook'])->name('webhook');
Route::get('/agreement', [PaypalController::class, 'agreement'])->name('agreement');
Route::get('/get-agreement', [PaypalController::class, 'getAgreement'])->name('get.agreement');

Route::middleware('auth')->group(function () {
    Route::post('/subscribe', [PaypalController::class, 'subscribe'])->name('subscribe');
});

require __DIR__ . '/auth.php';
