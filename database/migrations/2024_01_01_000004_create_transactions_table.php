<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the transactions table — the core of the system.
 *
 * IMMUTABILITY CONTRACT:
 * Records are append-only. Corrections = new records, not edits.
 * Only `returned_at`, `status`, and `quantity` can be updated after creation
 * (when a return is processed). This preserves a clean audit trail for
 * reporting and future utilization research papers.
 *
 * A transaction covers:
 *   (a) a room checkout/return, OR
 *   (b) a tool checkout/return (with quantity), OR
 *   (c) both at the same time.
 *
 * Google Form fields (Phase 1): First Name, Room to use, Subject.
 * Tool borrowing (Phase 1): Handled via the admin dashboard or a separate form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // The user account that logged this transaction
            // (typically the student assistant's account)
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // The room being checked out (null = tool-only transaction)
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete();

            // The tool being checked out (null = room-only transaction)
            $table->foreignId('tool_id')
                ->nullable()
                ->constrained('tools')
                ->nullOnDelete();

            // How many units of the tool are being borrowed (default 1)
            // Only relevant when tool_id is set.
            $table->unsignedSmallInteger('quantity')->default(1);

            // ── Borrower info (from Google Form) ─────────────────────────────

            // Faculty first name — as typed in the Google Form
            $table->string('borrower_name');

            // Faculty email — optional, for looking up existing user records
            $table->string('borrower_email')->nullable();

            // Subject / class the room is being used for (from Google Form)
            // e.g. "CC 102 - Programming Fundamentals", "IT 301 - Networking"
            $table->string('subject')->nullable();

            // ── Timing ───────────────────────────────────────────────────────

            // When the checkout was recorded
            $table->timestamp('checked_out_at');

            // When the borrower is expected to return (not always known upfront)
            $table->timestamp('expected_return_at')->nullable();

            // When the item was actually returned (null = still checked out)
            $table->timestamp('returned_at')->nullable();

            // ── Status ───────────────────────────────────────────────────────

            // Updated only when a return is processed, or by the overdue-check command
            $table->enum('status', ['open', 'returned', 'overdue'])->default('open');

            // Free-text notes from the student assistant
            $table->text('notes')->nullable();

            // Where this record originated
            // Values: 'google_form', 'dashboard', 'qr_scan' (Phase 2)
            $table->string('source')->default('google_form');

            $table->timestamps();

            // Indexes for common dashboard and reporting queries
            $table->index('status');
            $table->index('checked_out_at');
            $table->index(['room_id', 'status']);
            $table->index(['tool_id', 'status']);
            $table->index('borrower_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
