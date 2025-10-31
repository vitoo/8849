<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TalentController;


Route::redirect('/', '/talents');


Route::middleware(['auth'])->group(function () {
    Route::resource('talents', TalentController::class)
        ->except(['show', 'create', 'edit']);
        
    Route::post('talents/{talent}/sync', [TalentController::class, 'sync'])
        ->name('talents.sync');
});