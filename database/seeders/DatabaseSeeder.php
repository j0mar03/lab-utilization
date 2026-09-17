<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Create the Lab Head account ────────────────────────────────────
        // ⚠️  Change this password immediately after first login!
        User::firstOrCreate(
            ['email' => 'labhead@pup.edu.ph'],
            [
                'name'     => 'Lab Head',
                'email'    => 'labhead@pup.edu.ph',
                'password' => Hash::make('ChangeMe123!'),
                'role'     => 'lab_head',
            ]
        );

        // ── Create a default student assistant account ─────────────────────
        User::firstOrCreate(
            ['email' => 'assistant@pup.edu.ph'],
            [
                'name'     => 'Student Assistant',
                'email'    => 'assistant@pup.edu.ph',
                'password' => Hash::make('ChangeMe123!'),
                'role'     => 'student_assistant',
            ]
        );

        // ── Seed rooms, tools, faculty members, and subjects ────────────
        $this->call([
            RoomSeeder::class,
            ToolSeeder::class,
            FacultySeeder::class,
            SubjectSeeder::class,
        ]);

        $this->command->info('');
        $this->command->warn('⚠️  Default accounts created with password: ChangeMe123!');
        $this->command->warn('   Change passwords immediately after first login.');
    }
}
