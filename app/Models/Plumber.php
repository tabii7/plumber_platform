<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plumber extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function postalCodes()
{
    return $this->belongsToMany(PostalCode::class, 'plumber_postal_code');
}

public function user()
    {
        return $this->belongsTo(User::class);
    }

}
