<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
| Base URL: /api/v1/
|
*/

Route::prefix('v1')->group(function (): void {
    // Health check
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'message' => 'SIPDOK API is running',
        'data' => [
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ],
    ]));

    // Auth routes will be added in Phase 3
    // Project routes will be added in Phase 3
    // Review routes will be added in Phase 3
    // Dashboard routes will be added in Phase 3
    // Export routes will be added in Phase 3
});
