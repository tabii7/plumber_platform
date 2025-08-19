<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $fillable = [
    'full_name',
    'phone',
    'whatsapp_number',
    'email',
    'password',
    'address',
    'number',
    'postal_code',
    'city',
    'country',
    'role',
    'btw_number',
    'werk_radius',
    'conversation_state',
    'address_json'
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function postalCode()
{
    return $this->belongsTo(PostalCode::class, 'postal_code_id');
}

public function coverages()
{
    return $this->hasMany(\App\Models\PlumberCoverage::class, 'plumber_id');
}
public function categories()
{
    return $this->belongsToMany(\App\Models\Category::class);
}

 public function plumber()
    {
        return $this->hasOne(Plumber::class);
    }


}
