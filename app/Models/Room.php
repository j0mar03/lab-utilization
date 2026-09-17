<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
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
}
