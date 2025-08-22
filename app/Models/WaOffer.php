<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'plumber_id',
        'request_id',
        'personal_message',
        'status', // pending, selected, rejected
        'eta_minutes',
        'distance_km',
        'rating'
    ];

    public function plumber()
    {
        return $this->belongsTo(User::class, 'plumber_id');
    }

    public function request()
    {
        return $this->belongsTo(WaRequest::class, 'request_id');
    }
}
