<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $offices = [
            [
                'name'       => 'Laboratory Office 109A',
                'department' => 'DEMET & DOMIT',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Laboratory Office 203',
                'department' => 'Department of Computer and Electronics Engineering Technology',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($offices as $office) {
            $exists = DB::table('rooms')->where('name', $office['name'])->first();
            if (!$exists) {
                DB::table('rooms')->insert($office);
            }
        }
    }

    public function down(): void
    {
        DB::table('rooms')
            ->whereIn('name', ['Laboratory Office 109A', 'Laboratory Office 203'])
            ->delete();
    }
};
