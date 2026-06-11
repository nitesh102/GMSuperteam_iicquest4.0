<?php


use App\Http\Controllers\CitizenController;
use App\Http\Controllers\ComplaintCategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoiceCommandController;
use App\Models\Complaint;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [ComplaintController::class, 'getGroupedByLocation'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/voice/interpret', [VoiceCommandController::class, 'interpret'])->name('voice.interpret');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('departments', DepartmentController::class)->except(['create', 'edit', 'show'])->middleware('role:Superadmin');
    Route::resource('complaint-categories', ComplaintCategoryController::class)->except(['create', 'edit', 'show'])->middleware('role:Superadmin');
});

Route::middleware(['auth', 'role:Superadmin'])->group(function () {
    Route::resource('complaints', ComplaintController::class)->except(['edit', 'show'])->middleware('throttle:30,1');
    Route::get('complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('complaints/check-duplicates', [ComplaintController::class, 'checkDuplicates'])->name('complaints.check-duplicates');
});

Route::middleware(['auth', 'role:Citizen'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', [CitizenController::class, 'dashboard'])->name('dashboard');
    Route::get('/complaints', [CitizenController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [CitizenController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [CitizenController::class, 'store'])->name('complaints.store')->middleware('throttle:10,1');
    Route::post('/complaints/check-duplicates', [CitizenController::class, 'checkDuplicates'])->name('complaints.check-duplicates');
    Route::get('/complaints/{complaint}', [CitizenController::class, 'show'])->name('complaints.show');
    Route::post('/voice/interpret', [VoiceCommandController::class, 'interpret'])->name('voice.interpret');
});

require __DIR__.'/auth.php';
