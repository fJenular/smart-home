<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    // Define table explicitly
    protected $table = 'sensor_data';

    // Disable updated_at column since it is historical sensor log
    public $timestamps = false;

    protected $fillable = ['sensor_type', 'value', 'topic', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Scope for a specific sensor type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('sensor_type', $type);
    }
}
