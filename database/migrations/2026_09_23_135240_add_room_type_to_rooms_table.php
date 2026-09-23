<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('room_type', 50)->default('general')->after('department');
        });

        // Backfill existing rooms
        DB::table('rooms')->whereIn('name', ['LAB 104', 'LAB 105', 'LAB 109C', 'LAB 203', 'LAB 204', 'LAB 205'])
            ->update(['room_type' => 'computer_lab']);

        DB::table('rooms')->whereIn('name', ['LAB 109', 'LAB 109B', 'LAB 208'])
            ->update(['room_type' => 'engineering_lab']);

        DB::table('rooms')->where('name', 'like', 'LEC%')
            ->update(['room_type' => 'lecture']);

        DB::table('rooms')->where('name', 'like', '%OFFICE%')
            ->update(['room_type' => 'office']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('room_type');
        });
    }
};
