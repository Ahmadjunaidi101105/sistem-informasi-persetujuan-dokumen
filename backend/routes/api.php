<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\DocumentCategoryController;
use App\Http\Controllers\Api\V1\ExportController;

Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/user', [AuthController::class, 'user']);

        // Dashboard
        Route::get('dashboard/pemohon', [DashboardController::class, 'pemohon']);
        Route::get('dashboard/penilai', [DashboardController::class, 'penilai']);

        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::post('projects/{project}/submit', [ProjectController::class, 'submit']);
        Route::post('projects/{project}/take-review', [ProjectController::class, 'takeReview']);
        Route::post('projects/{project}/approve', [ProjectController::class, 'approve']);
        Route::post('projects/{project}/revise', [ProjectController::class, 'revise']);
        Route::post('projects/{project}/reject', [ProjectController::class, 'reject']);

        // Documents
        Route::post('projects/{project}/documents', [DocumentController::class, 'store']);
        Route::get('projects/{project}/documents', [DocumentController::class, 'index']);
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);
        Route::delete('documents/{document}', [DocumentController::class, 'destroy']);

        // Reviews
        Route::get('projects/{project}/reviews', [ReviewController::class, 'projectReviews']);
        Route::get('reviews', [ReviewController::class, 'index']);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Master Data
        Route::get('document-categories', [DocumentCategoryController::class, 'index']);

        // Export
        Route::get('export/projects', [ExportController::class, 'exportExcel'])
            ->middleware('throttle:5,1');
        Route::get('export/projects/{project}/pdf', [ExportController::class, 'exportPdf'])
            ->middleware('throttle:5,1');
    });
});
