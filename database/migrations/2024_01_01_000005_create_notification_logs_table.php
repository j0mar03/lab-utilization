<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the notification_logs table.
 *
 * Every Telegram or email notification sent by Laravel is logged here.
 * This serves two purposes:
 *   1. Debugging — if a faculty member says "I didn't get a notification", we can check.
 *   2. Reporting — track notification volume and delivery over time.
 *
 * NOTE: The `payload` column (JSON) stores the full message sent, which may contain
 * personal data (names, room info). Ensure this aligns with your data privacy policy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();

            // Which transaction triggered this notification
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            // The channel used to send the notification
            $table->enum('channel', ['telegram', 'email']);

            // The recipient (Telegram chat_id or email address)
            $table->string('recipient')->nullable();

            // When the notification was sent (stored explicitly, not just created_at)
            $table->timestamp('sent_at');

            // Was delivery successful? Null means not yet confirmed.
            $table->boolean('success')->nullable();

            // The full payload/message sent — stored as JSON for flexibility.
            // For Telegram: the message text. For email: subject + body snippet.
            $table->json('payload')->nullable();

            // Error message if delivery failed
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['transaction_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
