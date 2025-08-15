<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaFlow extends Model
{
    use HasFactory;

      protected $fillable = ['code','name','entry_keyword','target_role','is_active'];

    public function nodes() {
        return $this->hasMany(WaNode::class, 'flow_id')->orderBy('sort');
    }
}
