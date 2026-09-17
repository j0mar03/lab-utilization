<?php

use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Lab Utilization System
|--------------------------------------------------------------------------
|
| Phase 1: Google Form submission endpoint.
|
| Authentication: shared API key in X-Api-Key header.
| No Laravel Sanctum token needed for this route because it's called by
| Google Apps Script (not a human browser session). The API key approach
| is simpler and sufficient for an internal institutional tool.
|
| Phase 2 will add QR scan endpoints here.
| Phase 3 will add data export / reporting endpoints.
|
*/

// ── Phase 1: Google Form → Laravel ingestion ──────────────────────────────

Route::post('/submission', [SubmissionController::class, 'store'])
    ->name('api.submission.store');

// ── Health check (useful for confirming the endpoint is reachable) ─────────

Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'service' => config('app.name'),
        'time'    => now()->toIso8601String(),
    ]);
})->name('api.health');
