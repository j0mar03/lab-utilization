<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('department')->nullable()->after('borrower_email');
            $table->index('department');
        });

        // Smart backfill based on tools, faculty, and subject codes
        $faculties = DB::table('faculties')->get();
        $subjects  = DB::table('subjects')->get();
        $tools     = DB::table('tools')->get();

        $transactions = DB::table('transactions')->get();

        foreach ($transactions as $tx) {
            $dept = null;

            // 1. Tool department
            if ($tx->tool_id) {
                $tool = $tools->firstWhere('id', $tx->tool_id);
                if ($tool && !empty($tool->department)) {
                    $dept = $tool->department;
                }
            }

            // 2. Transaction items tool department
            if (!$dept) {
                $item = DB::table('transaction_items')->where('transaction_id', $tx->id)->first();
                if ($item) {
                    $itemTool = $tools->firstWhere('id', $item->tool_id);
                    if ($itemTool && !empty($itemTool->department)) {
                        $dept = $itemTool->department;
                    }
                }
            }

            // 3. Faculty match
            if (!$dept && $tx->borrower_name) {
                $cleanBorrower = strtolower($tx->borrower_name);
                $cleanBorrower = str_replace(['engr.', 'prof.', 'dr.', 'mr.', 'ms.', 'mrs.'], '', $cleanBorrower);
                $cleanBorrower = trim($cleanBorrower);

                foreach ($faculties as $f) {
                    $cleanFaculty = strtolower($f->name);
                    if (str_contains($cleanBorrower, $cleanFaculty) || str_contains($cleanFaculty, $cleanBorrower)) {
                        $dept = $f->department;
                        break;
                    }
                }
            }

            // 4. Subject code match
            if (!$dept && $tx->subject) {
                $cleanSubj = strtolower($tx->subject);
                foreach ($subjects as $s) {
                    if (str_contains($cleanSubj, strtolower($s->code))) {
                        $dept = $s->department;
                        break;
                    }
                }
            }

            if ($dept) {
                DB::table('transactions')->where('id', $tx->id)->update(['department' => $dept]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropColumn('department');
        });
    }
};
