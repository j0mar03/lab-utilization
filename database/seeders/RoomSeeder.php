<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * RoomSeeder — Seeds all PUP-ITECH lab rooms.
 *
 * Two room types:
 *   LAB  — Computer laboratory rooms (hands-on classes, equipment-heavy)
 *   LEC  — Lecture rooms (regular classes, presentations)
 *
 * Room names are used as Google Form dropdown values — if you rename a room
 * here, update the Google Form dropdown choices to match exactly.
 */
class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [

            // ── Laboratory Rooms (LAB) ─────────────────────────────────────
            [
                'name'       => 'LAB 104',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 105',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 109B',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 109C',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 203',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 204',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 205',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LAB 208',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
                'wifi_notes' => null,
                'manual_url' => null,
            ],

            // ── Lecture Rooms (LEC) ────────────────────────────────────────
            [
                'name'       => 'LEC 200',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 201',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 209',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 210',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 211',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 212',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 213',
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 301',
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 303',
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 304',
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 305',
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
            [
                'name'       => 'LEC 306',
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
                'wifi_notes' => null,
                'manual_url' => null,
            ],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(['name' => $room['name']], $room);
        }

        $this->command->info('✅ ' . count($rooms) . ' rooms seeded (8 LAB + 12 LEC).');
        $this->command->line('   💡 Update capacity, wifi_notes, and manual_url via the admin panel.');
    }
}
