<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('ITECH Subject for Diploma Program.md');

        if (!File::exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $currentDept = '';
        $currentYear = '';
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Department header
            if (str_starts_with($line, '## ')) {
                $rawDept = trim(substr($line, 3));
                if (stripos($rawDept, 'Office Management') !== false) {
                    $currentDept = 'Department of Office Management and Information Technology';
                } elseif (stripos($rawDept, 'Computer And Electronics') !== false) {
                    $currentDept = 'Department of Computer and Electronics Engineering Technology';
                } elseif (stripos($rawDept, 'Electrical And Mechanical') !== false) {
                    $currentDept = 'Department of Electrical and Mechanical Engineering Technology';
                } elseif (stripos($rawDept, 'Civil') !== false) {
                    $currentDept = 'Department of Civil and Railway Engineering Technology';
                } else {
                    $currentDept = $rawDept;
                }
                $currentYear = '';
                continue;
            }

            // Year level header: e.g. "#### 1st year", "2nd Year", "3rd year"
            if (preg_match('/^#+\s*([1234][a-z]{2}\s*year)/i', $line, $ym) ||
                preg_match('/^([1234][a-z]{2}\s*year)/i', $line, $ym)) {
                $currentYear = ucwords(strtolower(trim($ym[1])));
                continue;
            }

            // Subject entry: code and title
            if (preg_match('/^([A-Z]{2,6}(?:\s+\d+|\d+|PC\d+))\s{2,}(.+)$/i', $line, $m) ||
                preg_match('/^([A-Z]{2,6}\s*\d+)\s+(.+)$/i', $line, $m)) {
                $code = trim(preg_replace('/\s+/', ' ', $m[1]));
                $name = trim($m[2]);

                Subject::updateOrCreate(
                    [
                        'code'       => $code,
                        'department' => $currentDept,
                    ],
                    [
                        'name'       => $name,
                        'year_level' => $currentYear ?: null,
                        'is_active'  => true,
                    ]
                );

                $count++;
            }
        }

        $this->command->info("Seeded {$count} subjects successfully from ITECH Subject for Diploma Program.md.");
    }
}
