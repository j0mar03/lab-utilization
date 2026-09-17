<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('department')->nullable()->after('name');
            $table->index('department');
        });

        // Ensure LAB 109 exists if not already present
        $lab109 = DB::table('rooms')->where('name', 'LAB 109')->first();
        if (!$lab109) {
            DB::table('rooms')->insert([
                'name'       => 'LAB 109',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'department' => 'Department of Electrical and Mechanical Engineering Technology',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── 1. Department of Computer and Electronics Engineering Technology (DCEET) ──
        // Lecture rooms: 200, 201 | Lab rooms: 204, 205
        $dceet = 'Department of Computer and Electronics Engineering Technology';
        DB::table('rooms')->whereIn('name', ['LEC 200', 'LEC 201', 'LAB 204', 'LAB 205'])
            ->update(['department' => $dceet]);

        // ── 2. Department of Office Management and Information Technology (DOMIT) ──
        // Lecture rooms: 303, 304, 305 | Lab rooms: 104, 105, 203
        $domit = 'Department of Office Management and Information Technology';
        DB::table('rooms')->whereIn('name', ['LEC 303', 'LEC 304', 'LEC 305', 'LAB 104', 'LAB 105', 'LAB 203'])
            ->update(['department' => $domit]);

        // ── 3. Department of Electrical and Mechanical Engineering Technology (DEMET) ──
        // Lecture rooms: 209, 210, 211, 213 | Lab rooms: 109B, 109C, 109, 208
        $demet = 'Department of Electrical and Mechanical Engineering Technology';
        DB::table('rooms')->whereIn('name', ['LEC 209', 'LEC 210', 'LEC 211', 'LEC 213', 'LAB 109B', 'LAB 109C', 'LAB 109', 'LAB 208'])
            ->update(['department' => $demet]);

        // Sync room transactions to match their room's department
        $rooms = DB::table('rooms')->whereNotNull('department')->get();
        foreach ($rooms as $room) {
            DB::table('transactions')
                ->where('room_id', $room->id)
                ->where(function ($q) {
                    $q->whereNull('department')->orWhere('department', '');
                })
                ->update(['department' => $room->department]);
        }
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropColumn('department');
        });
    }
};
