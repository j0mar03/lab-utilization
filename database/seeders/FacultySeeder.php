<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('Faculty List.md');

        if (!File::exists($filePath)) {
            $this->command->error("Faculty List.md not found at {$filePath}");
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;

        $departmentMap = [
            'College of Science' => 'College of Science',
            'Department Of Civil And Railway Engineering Technology' => 'Department of Civil and Railway Engineering Technology',
            'Department of Civil And Railway Engineering Technology' => 'Department of Civil and Railway Engineering Technology',
            'Department Of Computer And Electronics Engineering Technology' => 'Department of Computer and Electronics Engineering Technology',
            'Department of Computer And Electronics Engineering Technology' => 'Department of Computer and Electronics Engineering Technology',
            'Department Of Electrical And Mechanical Engineering Technology' => 'Department of Electrical and Mechanical Engineering Technology',
            'Department of Electrical And Mechanical Engineering Technology' => 'Department of Electrical and Mechanical Engineering Technology',
            'Department Of Office Management and Information Technology' => 'Department of Office Management and Information Technology',
            'Department of Office Management and Information Technology' => 'Department of Office Management and Information Technology',
        ];

        // Skip header lines (| FullName | ... and |---|...)
        foreach (array_slice($lines, 2) as $line) {
            $cols = array_map('trim', explode('|', $line));
            // Expecting: ['', FullName, Gender, Department, Institutional Email, '']
            if (count($cols) < 5) {
                continue;
            }

            $name = trim(preg_replace('/<br\s*\/?>/i', '', $cols[1]));
            $gender = trim(preg_replace('/<br\s*\/?>/i', '', $cols[2]));
            $rawDept = trim(preg_replace('/<br\s*\/?>/i', '', $cols[3]));
            $email = trim(preg_replace('/<br\s*\/?>/i', '', $cols[4]));

            if (empty($name) || empty($email)) {
                continue;
            }

            $dept = $departmentMap[$rawDept] ?? $rawDept;

            Faculty::updateOrCreate(
                ['email' => $email],
                [
                    'name'       => $name,
                    'gender'     => !empty($gender) ? strtoupper($gender) : null,
                    'department' => $dept,
                    'is_active'  => true,
                ]
            );

            $count++;
        }

        $this->command->info("Seeded {$count} faculty members successfully from Faculty List.md.");
    }
}
