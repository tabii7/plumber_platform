<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlumberCoverage extends Model
{
    use HasFactory;

      protected $fillable = ['plumber_id', 'hoofdgemeente', 'city', 'coverage_type'];

    public function plumber()
    {
        return $this->belongsTo(User::class, 'plumber_id');
    }
}
