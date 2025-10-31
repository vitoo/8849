<?php

use App\Http\Controllers\TalentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/talents')->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('talents', TalentController::class)
        ->except(['show', 'create', 'edit']);

    Route::post('talents/{talent}/sync', [TalentController::class, 'sync'])
        ->name('talents.sync');
});

require __DIR__.'/settings.php';
