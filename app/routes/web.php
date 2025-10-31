<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\TalentController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';


Route::middleware(['auth'])->group(function () {
    Route::resource('talents', TalentController::class)
        ->except(['show', 'create', 'edit']);
    
    Route::post('talents/sync-all', [TalentController::class, 'syncAll'])
        ->name('talents.sync-all');
    
    Route::post('talents/{talent}/sync', [TalentController::class, 'sync'])
        ->name('talents.sync');
});