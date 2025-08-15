<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'Postcode', 
        'Plaatsnaam_NL', 
        'Plaatsnaam_FR', 
        'Plaatsnaam_EN', 
        'Deelgemeente', 
        'Provincie', 
        'Latitude', 
        'Longitude', 
        'Hoofdgemeente'
    ];
}
