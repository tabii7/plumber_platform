<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaSession extends Model
{
    use HasFactory;
      protected $fillable = [
        'wa_number','user_id','flow_code','node_code','context_json','last_message_at'
    ];

    protected $casts = [
        'context_json' => 'array',
        'last_message_at' => 'datetime',
    ];
}
