<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->string('department')->nullable()->after('category');
            $table->index('department');
        });

        // Smart backfill based on existing categories and names
        DB::table('tools')
            ->where('category', 'like', '%DOMIT%')
            ->orWhere('name', 'like', '%STENOGRAPH%')
            ->orWhere('name', 'like', '%SHORTHAND%')
            ->update(['department' => 'Department of Office Management and Information Technology']);

        DB::table('tools')
            ->where('category', 'like', '%DEMET%')
            ->orWhere('name', 'like', '%WELDING%')
            ->orWhere('name', 'like', '%TORCH%')
            ->orWhere('name', 'like', '%CONDUIT%')
            ->orWhere('name', 'like', '%GRINDER%')
            ->update(['department' => 'Department of Electrical and Mechanical Engineering Technology']);

        DB::table('tools')
            ->whereNull('department')
            ->where(function ($q) {
                $q->where('category', 'like', '%MEASURING%')
                  ->orWhere('name', 'like', '%MULTITESTER%')
                  ->orWhere('name', 'like', '%SOLDERING%')
                  ->orWhere('name', 'like', '%DESOLDERING%')
                  ->orWhere('name', 'like', '%REWORK%')
                  ->orWhere('name', 'like', '%ARDUINO%');
            })
            ->update(['department' => 'Department of Computer and Electronics Engineering Technology']);
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropColumn('department');
        });
    }
};
