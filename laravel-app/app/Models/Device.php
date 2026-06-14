<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = ['name', 'room_id', 'topic', 'status'];

    /**
     * Get the room this device belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Check if device is active.
     */
    public function isActive(): bool
    {
        return in_array(strtoupper($this->status), ['ON', 'OPEN', 'UNLOCKED']);
    }
}
