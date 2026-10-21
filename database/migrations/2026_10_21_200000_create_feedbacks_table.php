<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Academic Feedback Survey Instrument based on EduQUAL & TAM Frameworks:
     * - Q1: Environmental Quality (cleanliness, ventilation, seating)
     * - Q2: Resource & Tech Availability (TV, Projector, PC terminals, tools)
     * - Q3: Safety Protocols & Workstation Precautions
     * - Q4: Laboratory Staff Service & SA Counter Efficiency
     * - Q5: Digital System Experience (QR scan, checkout convenience)
     * - Overall Satisfaction Score
     * - Reported Diagnostics Issues (JSON multi-select)
     * - Suggestions for Qualitative Thematic Analysis
     */
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            // Linkages (nullable to support standalone room door scans and historical transactions)
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();

            // Respondent demographics
            $table->enum('capacity', ['Student', 'Faculty', 'Guest'])->default('Student');
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();

            // Core Likert Constructs (1 to 5)
            $table->unsignedTinyInteger('q1_environment')->comment('Cleanliness, lighting, ventilation & seating');
            $table->unsignedTinyInteger('q2_resources')->comment('TV, Projector, PCs, power & equipment availability');
            $table->unsignedTinyInteger('q3_safety')->comment('Safety rules, workstation precautions, emergency preparedness');
            $table->unsignedTinyInteger('q4_service')->comment('Lab assistant courtesy, responsiveness & counter assistance');
            $table->unsignedTinyInteger('q5_system_experience')->comment('QR scan convenience, automated guidelines, reservation speed');
            $table->unsignedTinyInteger('overall_score')->comment('Overall satisfaction with facility experience');

            // Quick Diagnostic Issue Flags (Multi-select saved as JSON)
            // Options: 'projector', 'pc_software', 'aircon_ventilation', 'cleanliness', 'missing_tools', 'none'
            $table->json('reported_issues')->nullable();

            // Qualitative feedback / suggestion
            $table->text('suggestions')->nullable();

            // IP & User Agent for fraud prevention / audit trail in research
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Research indexing
            $table->index(['room_id', 'created_at']);
            $table->index(['capacity', 'created_at']);
            $table->index('overall_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
