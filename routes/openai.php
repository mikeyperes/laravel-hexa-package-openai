<?php

use Illuminate\Support\Facades\Route;
use hexa_package_openai\Http\Controllers\OpenaiSettingController;
use hexa_package_openai\Http\Controllers\TranscriptionController;

Route::middleware(['web', 'auth', 'locked', 'system_lock', 'two_factor', 'role'])->group(function () {

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/openai', [OpenaiSettingController::class, 'index'])->name('openai');
        Route::post('/openai', [OpenaiSettingController::class, 'save'])->name('openai.save');
        Route::post('/openai/test', [OpenaiSettingController::class, 'test'])->name('openai.test');
    });

    // Transcription
    Route::post('/api/transcribe', [TranscriptionController::class, 'transcribe'])->name('openai.transcribe');

});
