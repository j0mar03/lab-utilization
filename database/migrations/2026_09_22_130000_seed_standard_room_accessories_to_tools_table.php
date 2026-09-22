<?php

use App\Models\Tool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add standard room accessories commonly borrowed by faculty when using rooms
        $accessories = [
            [
                'name'           => 'HDMI Cable (High-Speed)',
                'category'       => 'Cables & Adapters',
                'department'     => null,
                'description'    => 'High-speed HDMI display cable for connecting instructor laptops to projectors and interactive displays.',
                'total_quantity' => 12,
                'is_active'      => true,
            ],
            [
                'name'           => 'Projector Remote Control',
                'category'       => 'Remotes & Controllers',
                'department'     => null,
                'description'    => 'Universal / Epson ceiling-mounted projector remote control.',
                'total_quantity' => 8,
                'is_active'      => true,
            ],
            [
                'name'           => 'Wireless Presenter / Clicker',
                'category'       => 'Presentation Tools',
                'department'     => null,
                'description'    => 'USB wireless laser pointer and slide clicker for classroom presentations.',
                'total_quantity' => 6,
                'is_active'      => true,
            ],
            [
                'name'           => 'Power Extension Cord (Heavy Duty)',
                'category'       => 'Electrical & Power',
                'department'     => null,
                'description'    => 'Heavy-duty 5m power extension cord with surge protection.',
                'total_quantity' => 10,
                'is_active'      => true,
            ],
            [
                'name'           => 'Type-C to HDMI Display Adapter',
                'category'       => 'Cables & Adapters',
                'department'     => null,
                'description'    => 'USB-C to HDMI adapter dongle for modern laptops without built-in HDMI ports.',
                'total_quantity' => 6,
                'is_active'      => true,
            ],
            [
                'name'           => 'Laboratory Master Key Set',
                'category'       => 'Keys & Access',
                'department'     => null,
                'description'    => 'Designated laboratory door and equipment cabinet key set.',
                'total_quantity' => 5,
                'is_active'      => true,
            ],
        ];

        foreach ($accessories as $item) {
            Tool::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down: do not delete inventory items that might have transaction history
    }
};
