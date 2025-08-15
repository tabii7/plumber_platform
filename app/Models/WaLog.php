<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

     protected $fillable = ['wa_number','direction','payload_json','status'];

    protected $casts = [
        'payload_json' => 'array',
    ];
}
