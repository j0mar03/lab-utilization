<?php

use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\QrController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ToolController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Lab Utilization System
|--------------------------------------------------------------------------
|
| Phase 1: Auth (Breeze) + Admin Tool Management
| Phase 2: QR scan checkout/return (public, rate-limited)
|          + Admin printable QR code sheets
| Phase 3: Full dashboard, transaction history, reports & charts
|
*/

// ── Home ──────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ═══════════════════════════════════════════════════════════════════════════
// PHASE 2 — Public QR Scan Routes (no login, rate-limited)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['throttle:30,1'])->prefix('scan')->name('scan.')->group(function () {

    // Room checkout flow
    Route::get('/room/{room}',         [ScanController::class, 'room'])->name('room');
    Route::post('/room/{room}',        [ScanController::class, 'checkoutRoom'])->name('room.checkout');
    Route::post('/room/{room}/return', [ScanController::class, 'returnRoom'])->name('room.return');

    // Tool checkout flow
    Route::get('/tool/{tool}',  [ScanController::class, 'tool'])->name('tool');
    Route::post('/tool/{tool}', [ScanController::class, 'checkoutTool'])->name('tool.checkout');

    // Return flow
    Route::get('/return/{transaction}',  [ScanController::class, 'showReturn'])->name('return');
    Route::post('/return/{transaction}', [ScanController::class, 'processReturn'])->name('return.process');

    // Success pages
    Route::get('/success/{transaction}',        [ScanController::class, 'success'])->name('success');
    Route::get('/return-success/{transaction}', [ScanController::class, 'returnSuccess'])->name('return-success');
});

// ═══════════════════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware(['auth'])->group(function () {

    // ── Phase 3: Main Dashboard ───────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profile routes (Breeze) ───────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Phase 3: Transaction History (Lab Head + Student Assistant) ───────
    Route::middleware(['role:lab_head|student_assistant'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Transaction creation, return, history, detail, and CSV export
            Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
            Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
            Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
            Route::post('/transactions/vacate-stale', [TransactionController::class, 'vacateStaleSessions'])->name('transactions.vacate-stale');
            Route::post('/transactions/{transaction}/return', [TransactionController::class, 'markReturned'])->name('transactions.return');
            Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
            Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
            Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
            Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
            Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        });

    // ── Lab Head only routes ──────────────────────────────────────────────
    Route::middleware(['role:lab_head'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Phase 3: Reports, Charts & Audit Matrices
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/audit', [ReportController::class, 'audit'])->name('reports.audit');

            // Phase 2: Printable QR code sheets
            Route::prefix('qr')->name('qr.')->group(function () {
                Route::get('/rooms',       [QrController::class, 'rooms'])->name('rooms');
                Route::get('/tools',       [QrController::class, 'tools'])->name('tools');
                Route::get('/room/{room}', [QrController::class, 'singleRoom'])->name('room');
                Route::get('/tool/{tool}', [QrController::class, 'singleTool'])->name('tool');
            });

            // Phase 1: Tool inventory management
            Route::resource('tools', ToolController::class);

            // Faculty & Subject Master Management
            Route::resource('faculties', FacultyController::class);
            Route::resource('subjects', SubjectController::class);

            // Room management
            Route::resource('rooms', RoomController::class);

            // User management
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        });

    // ── Student Assistant & Lab Head checkout entry ───────────────────────
    Route::middleware(['role:student_assistant|lab_head'])->group(function () {
        Route::get('/checkout', [TransactionController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [TransactionController::class, 'store'])->name('checkout.store');
    });

});

require __DIR__.'/auth.php';
