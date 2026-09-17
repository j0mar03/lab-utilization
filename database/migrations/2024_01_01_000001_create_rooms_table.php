<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the rooms table.
 *
 * Each row represents one physical room (computer lab, server room, etc.)
 * that can be checked out by faculty/students.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            // Human-readable room name, e.g. "Computer Lab 1"
            $table->string('name');

            // Physical location within the building, e.g. "2nd Floor, Wing B"
            $table->string('location')->nullable();

            // Maximum number of people the room can accommodate
            $table->unsignedSmallInteger('capacity')->nullable();

            // Whether the room has Wi-Fi available
            $table->boolean('has_wifi')->default(false);

            // Wi-Fi credentials or notes (e.g. SSID, password) — mark as sensitive
            $table->text('wifi_notes')->nullable();

            // URL to the room's lab manual / info document (Google Drive link, etc.)
            $table->string('manual_url')->nullable();

            // Soft-deletes allow decommissioning a room without losing history
            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
