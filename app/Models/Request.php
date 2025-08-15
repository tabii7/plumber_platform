<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;
     protected $fillable = [
        'client_id',
        'postal_code',
        'service_id',
        'description',
        'status'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
