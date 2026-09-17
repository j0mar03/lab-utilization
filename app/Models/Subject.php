<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'department',
        'year_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the formatted display string: "CODE - Name".
     */
    public function getFormattedTitleAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
