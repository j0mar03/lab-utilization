<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'gender',
        'department',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departmentShort(): string
    {
        return match($this->department) {
            'Department of Office Management and Information Technology' => 'DOMIT',
            'Department of Computer and Electronics Engineering Technology' => 'DECET',
            'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
            'Department of Civil and Railway Engineering Technology' => 'DCRET',
            'College of Science' => 'CS',
            default => $this->department ? substr($this->department, 0, 15) : 'General',
        };
    }
}
