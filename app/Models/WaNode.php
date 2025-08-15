<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaNode extends Model
{
    use HasFactory;
      protected $fillable = [
        'flow_id','code','type','title','body','footer','options_json','next_map_json','sort'
    ];

    protected $casts = [
        'options_json' => 'array',
        'next_map_json' => 'array',
    ];

    public function flow() {
        return $this->belongsTo(WaFlow::class, 'flow_id');
    }
}
