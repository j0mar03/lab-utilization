<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * RoomSeeder — Seeds all PUP-ITECH lab and lecture rooms organized by Department.
 *
 * Department Mapping:
 * - Department Of Computer And Electronics Engineering Technology (DCEET):
 *     Lecture rooms: 200, 201 | Lab rooms: 204, 205
 * - Department Of Office Management and Information Technology (DOMIT):
 *     Lecture rooms: 303, 304, 305 | Lab rooms: 104, 105, 203
 * - Department Of Electrical And Mechanical Engineering Technology (DEMET):
 *     Lecture rooms: 209, 210, 211, 213 | Lab rooms: 109B, 109C, 109, 208
 * - Shared / General:
 *     Lecture rooms: 212, 301, 306
 */
class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $dceet = 'Department of Computer and Electronics Engineering Technology';
        $domit = 'Department of Office Management and Information Technology';
        $demet = 'Department of Electrical and Mechanical Engineering Technology';

        $rooms = [
            // ── DOMIT (Office Management & IT) ─────────────────────────────
            [
                'name'       => 'LAB 104',
                'department' => $domit,
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LAB 105',
                'department' => $domit,
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LAB 203',
                'department' => $domit,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LEC 303',
                'department' => $domit,
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 304',
                'department' => $domit,
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 305',
                'department' => $domit,
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],

            // ── DCEET (Computer & Electronics Engineering Tech) ────────────
            [
                'name'       => 'LAB 204',
                'department' => $dceet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LAB 205',
                'department' => $dceet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LEC 200',
                'department' => $dceet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 201',
                'department' => $dceet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],

            // ── DEMET (Electrical & Mechanical Engineering Tech) ───────────
            [
                'name'       => 'LAB 109',
                'department' => $demet,
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LAB 109B',
                'department' => $demet,
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LAB 109C',
                'department' => $demet,
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LAB 208',
                'department' => $demet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'LEC 209',
                'department' => $demet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 210',
                'department' => $demet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 211',
                'department' => $demet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 213',
                'department' => $demet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],

            // ── Laboratory Offices (Tool Storage / Custodian) ─────────────
            [
                'name'       => 'Laboratory Office 109A',
                'department' => 'DEMET & DOMIT',
                'location'   => '1st Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],
            [
                'name'       => 'Laboratory Office 203',
                'department' => $dceet,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => true,
            ],

            // ── General / Shared Rooms ─────────────────────────────────────
            [
                'name'       => 'LEC 212',
                'department' => null,
                'location'   => '2nd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 301',
                'department' => null,
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
            [
                'name'       => 'LEC 306',
                'department' => null,
                'location'   => '3rd Floor',
                'capacity'   => null,
                'has_wifi'   => false,
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['name' => $room['name']], $room);
        }

        $this->command->info('✅ ' . count($rooms) . ' rooms seeded with departmental associations.');
    }
}
