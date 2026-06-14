<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    protected $fillable = ['name', 'condition', 'action', 'status'];

    protected $casts = [
        'condition' => 'array',
        'action' => 'array',
        'status' => 'boolean',
    ];
}
