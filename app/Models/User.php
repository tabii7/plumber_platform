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
    'company_name',
    'subscription_plan',
    'subscription_status',
    'subscription_ends_at',
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
    'address_json',
    'current_request_id'
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
        'subscription_ends_at' => 'datetime',
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

    public function currentRequest()
    {
        return $this->belongsTo(WaRequest::class, 'current_request_id');
    }

    public function requests()
    {
        return $this->hasMany(WaRequest::class, 'customer_id');
    }

    public function offers()
    {
        return $this->hasMany(WaOffer::class, 'plumber_id');
    }

    public function getAvailabilityStatusAttribute()
    {
        if ($this->role !== 'plumber') {
            return null;
        }
        
        return $this->plumber?->availability_status ?? 'available';
    }

    public function setAvailabilityStatusAttribute($value)
    {
        if ($this->role === 'plumber' && $this->plumber) {
            $this->plumber->update(['availability_status' => $value]);
        }
    }

    /**
     * Automatically add user's municipality to coverage areas
     */
    public function addDefaultMunicipalityCoverage()
    {
        if ($this->role !== 'plumber' || !$this->city) {
            return false;
        }

        // Find the user's municipality based on their city
        $userMunicipality = \DB::table('postal_codes')
            ->where('Plaatsnaam_NL', $this->city)
            ->whereNotNull('Hoofdgemeente')
            ->value('Hoofdgemeente');

        if (!$userMunicipality) {
            return false;
        }

        // Check if municipality is already in coverage
        $existing = \App\Models\PlumberCoverage::where('plumber_id', $this->id)
            ->where('hoofdgemeente', $userMunicipality)
            ->exists();

        if (!$existing) {
            \App\Models\PlumberCoverage::create([
                'plumber_id' => $this->id,
                'hoofdgemeente' => $userMunicipality,
                'coverage_type' => 'municipality'
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check if the user has an active subscription
     */
    public function hasActiveSubscription()
    {
        return $this->subscription_status === 'active' && 
               $this->subscription_ends_at && 
               $this->subscription_ends_at->isFuture();
    }

    /**
     * Get subscription days remaining
     */
    public function getSubscriptionDaysRemaining()
    {
        if (!$this->hasActiveSubscription()) {
            return 0;
        }

        return now()->diffInDays($this->subscription_ends_at, false);
    }

    /**
     * Get subscription status with days remaining
     */
    public function getSubscriptionStatusWithDays()
    {
        if (!$this->hasActiveSubscription()) {
            return 'No active subscription';
        }

        $days = $this->getSubscriptionDaysRemaining();
        
        if ($days <= 0) {
            return 'Expired';
        } elseif ($days == 1) {
            return 'Expires today';
        } elseif ($days <= 7) {
            return "Expires in {$days} days";
        } elseif ($days <= 30) {
            return "Expires in {$days} days";
        } else {
            return "Valid for {$days} more days";
        }
    }
}
