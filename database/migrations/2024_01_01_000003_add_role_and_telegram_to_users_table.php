<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the default Breeze/Laravel users table with:
 *  - role:              which role the user has in the system
 *  - telegram_chat_id:  optional, for sending direct Telegram DMs to a user
 *
 * Run this AFTER running Breeze's default migrations (which create the
 * base `users` table with name, email, password, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role-based access control. Three roles:
            //   student_assistant — can log checkouts/returns via the form
            //   faculty           — room/tool user; receives email notifications
            //   lab_head          — full admin access to dashboard and all data
            $table->enum('role', ['student_assistant', 'faculty', 'lab_head'])
                ->default('faculty')
                ->after('email');

            // Optional: personal Telegram chat ID for direct DM notifications
            // Leave null for users who don't use Telegram personally.
            $table->string('telegram_chat_id')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'telegram_chat_id']);
        });
    }
};
