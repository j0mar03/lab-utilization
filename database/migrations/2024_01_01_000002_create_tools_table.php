<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the tools table.
 *
 * Tools are managed dynamically through the admin panel — the Lab Head can
 * add tools, set quantities, and deactivate them without touching the database.
 *
 * Quantity Tracking:
 *   - total_quantity: how many physical units of this tool exist in the lab
 *   - available quantity is computed at runtime:
 *     available = total_quantity − SUM(open transactions.quantity for this tool)
 *
 * Examples:
 *   - "HDMI Cable" with total_quantity=5 → if 2 are borrowed, 3 are available
 *   - "Lab Key 104" with total_quantity=1 → either available or borrowed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();

            // Display name shown in admin panel and Telegram notifications
            $table->string('name');

            // Category groups tools for filtering and reports
            // e.g. "Keys", "Cables", "Peripherals", "Projectors"
            $table->string('category');

            // Optional description / notes for the Lab Head
            $table->text('description')->nullable();

            // Total number of physical units available in the lab
            $table->unsignedSmallInteger('total_quantity')->default(1);

            // Optional: which room this tool is homed to (e.g. a room-specific key)
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete();

            // Lab Head can deactivate a tool (e.g. missing/broken) without deleting it.
            // Deactivated tools will not appear in checkout options.
            $table->boolean('is_active')->default(true);

            // Soft deletes — retire a tool without losing its transaction history
            $table->softDeletes();

            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
