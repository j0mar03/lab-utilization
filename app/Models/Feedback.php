<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'transaction_id',
        'room_id',
        'tool_id',
        'capacity',
        'respondent_name',
        'respondent_email',
        'q1_environment',
        'q2_resources',
        'q3_safety',
        'q4_service',
        'q5_system_experience',
        'overall_score',
        'reported_issues',
        'suggestions',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'reported_issues'     => 'array',
        'q1_environment'      => 'integer',
        'q2_resources'        => 'integer',
        'q3_safety'           => 'integer',
        'q4_service'          => 'integer',
        'q5_system_experience'=> 'integer',
        'overall_score'       => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /**
     * Compute composite quality score (average of Q1-Q5).
     */
    public function getCompositeScoreAttribute(): float
    {
        $sum = $this->q1_environment + $this->q2_resources + $this->q3_safety + $this->q4_service + $this->q5_system_experience;
        return round($sum / 5, 2);
    }

    /**
     * Helper to get star badge color.
     */
    public function scoreBadgeColor(): string
    {
        if ($this->overall_score >= 4) {
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
        }
        if ($this->overall_score === 3) {
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
        }
        return 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300';
    }

    /**
     * Diagnostic issue labels.
     */
    public static function issueLabels(): array
    {
        return [
            'projector'           => 'Projector / TV / Display Issue',
            'pc_software'         => 'PC Hardware / Software Issue',
            'aircon_ventilation'  => 'AC / Ventilation Temperature',
            'cleanliness'         => 'Cleanliness / Whiteboard Marker',
            'missing_tools'       => 'Missing or Defective Tools/Accessories',
            'none'                => 'None (Everything smooth)',
        ];
    }
}
