<?php

use App\Http\Controllers\Api\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::middleware('bridge.hmac')->prefix('v1')->group(function () {
    Route::post('/waitlist/lip-eyeliner', [WaitlistController::class, 'lipEyeliner'])
        ->name('api.waitlist.lip-eyeliner');
});