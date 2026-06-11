<?php


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

    Route::resource('departments', DepartmentController::class)->except(['create', 'edit', 'show']);
    Route::resource('complaint-categories', ComplaintCategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('complaints', ComplaintController::class)->except(['edit', 'show']);
    Route::get('complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');

});

require __DIR__.'/auth.php';
