<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'problem',
        'urgency',
        'description',
        'status',
        'selected_plumber_id',
        'completed_at',
        'rating',
        'rating_comment'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function selectedPlumber()
    {
        return $this->belongsTo(User::class, 'selected_plumber_id');
    }

    public function offers()
    {
        return $this->hasMany(WaOffer::class, 'request_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            'broadcasting' => 'Broadcasting to plumbers',
            'active' => 'Plumber assigned',
            'in_progress' => 'Work in progress',
            'completed' => 'Job completed',
            'cancelled' => 'Request cancelled'
        ];
    }
}
