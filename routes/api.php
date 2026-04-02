<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\WorksheetController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SyncController;

// Public — login/logout
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Books — worker sees only own books
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);

    // Worksheets
    Route::get('/jobs/{job}/worksheet', [WorksheetController::class, 'show']);
    Route::post('/jobs/{job}/worksheet/draft', [WorksheetController::class, 'saveDraft']);
    Route::post('/jobs/{job}/worksheet/submit', [WorksheetController::class, 'submit']);
    Route::post('/jobs/{job}/worksheet/signature', [WorksheetController::class, 'saveSignature']);
    Route::post('/jobs/{job}/worksheet/waive-signature', [WorksheetController::class, 'waiveSignature']);

    // Teams — TL only (verified inside controller by job.team_leader_id)
    Route::post('/jobs/{job}/teams', [TeamController::class, 'store']);
    Route::put('/jobs/{job}/teams/{team}', [TeamController::class, 'update']);

    // Payments — worker sees only own
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/current-week', [PaymentController::class, 'currentWeek']);

    // Offline sync — bulk operations queue
    Route::post('/sync', [SyncController::class, 'sync']);
});
