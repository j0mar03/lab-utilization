<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    public const DEPARTMENTS = Tool::DEPARTMENTS;

    protected $fillable = [
        'name',
        'department',
        'location',
        'capacity',
        'has_wifi',
        'wifi_notes',
        'manual_url',
    ];

    protected function casts(): array
    {
        return [
            'has_wifi' => 'boolean',
            'capacity' => 'integer',
        ];
    }

    public function departmentShort(): string
    {
        return match($this->department) {
            'Department of Office Management and Information Technology' => 'DOMIT',
            'Department of Computer and Electronics Engineering Technology' => 'DECET',
            'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
            'DEMET & DOMIT', 'DEMET / DOMIT', 'DEMET and DOMIT' => 'DEMET & DOMIT',
            'Department of Civil and Railway Engineering Technology' => 'DCRET',
            'College of Science' => 'CS',
            default => $this->department ? substr($this->department, 0, 15) : 'General / Shared',
        };
    }

    public function isOffice(): bool
    {
        return str_contains(strtoupper($this->name), 'OFFICE');
    }

    public function isLab(): bool
    {
        return str_starts_with($this->name, 'LAB') && !$this->isOffice();
    }

    public function isLecture(): bool
    {
        return str_starts_with($this->name, 'LEC');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tools that are homed to this room (e.g. room keys).
     */
    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    /**
     * All checkout transactions for this room.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Rooms that currently have an open (active) checkout.
     */
    public function scopeOccupied($query)
    {
        return $query->whereHas('transactions', function ($q) {
            $q->where('status', 'open');
        });
    }

    /**
     * Rooms with no open checkout — available right now.
     */
    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('transactions', function ($q) {
            $q->where('status', 'open');
        });
    }

    public function scopeDepartment($query, ?string $dept)
    {
        if ($dept) {
            return $query->where('department', $dept);
        }
        return $query;
    }

    public function scopeOffices($query)
    {
        return $query->where('name', 'LIKE', '%Office%');
    }

    public function scopeLaboratories($query)
    {
        return $query->where('name', 'LIKE', 'LAB%')->where('name', 'NOT LIKE', '%Office%');
    }

    public function scopeLectures($query)
    {
        return $query->where('name', 'LIKE', 'LEC%');
    }
}
