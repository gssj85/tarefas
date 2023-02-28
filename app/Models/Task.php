<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'expected_start_date',
        'expected_completion_date',
        'status',
        'user_id',
        'user_id_assigned_to'
    ];

    protected $casts = [
        'expected_start_date' => 'datetime',
        'expected_completion_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userAssignedTo()
    {
        return $this->belongsTo(User::class, 'user_id_assigned_to');
    }
}
