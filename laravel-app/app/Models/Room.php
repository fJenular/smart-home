<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['name', 'icon'];

    /**
     * Get all devices in this room.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
