<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Extend transactions.status to allow 'partially_returned'
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status', 30)->default('open')->change();
        });

        // 2. Create transaction_items for multi-tool checkout & partial return tracking
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            $table->foreignId('tool_id')
                ->constrained('tools')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('quantity_borrowed')->default(1);
            $table->unsignedSmallInteger('quantity_returned')->default(0);

            // Item status: 'borrowed', 'partially_returned', 'returned'
            $table->string('status', 30)->default('borrowed');

            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'tool_id']);
            $table->index(['tool_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['open', 'returned', 'overdue'])->default('open')->change();
        });
    }
};
