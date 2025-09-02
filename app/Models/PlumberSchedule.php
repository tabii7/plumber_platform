<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PlumberSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timezone',
        'schedule_data',
        'holidays',
        'vacations',
        'last_updated'
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'holidays' => 'array',
        'vacations' => 'array',
        'last_updated' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default schedule structure
     */
    public static function getDefaultSchedule(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $schedule = [];
        
        foreach ($days as $day) {
            $schedule[$day] = [
                'mode' => 'split', // closed | open24 | split | fullday
                'split' => ['o1' => '09:00', 'c1' => '12:00', 'o2' => '13:30', 'c2' => '19:00'],
                'full' => ['o' => '09:00', 'c' => '19:00'],
            ];
        }
        
        return $schedule;
    }

    /**
     * Check if plumber is available at given time
     */
    public function isAvailableAt(Carbon $dateTime): bool
    {
        $day = strtolower($dateTime->format('l'));
        $time = $dateTime->format('H:i');
        
        // Check if it's a holiday
        if ($this->holidays && in_array($dateTime->format('Y-m-d'), $this->holidays)) {
            return false;
        }
        
        // Check if it's during vacation
        if ($this->vacations) {
            foreach ($this->vacations as $vacation) {
                $from = Carbon::parse($vacation['from']);
                $to = Carbon::parse($vacation['to']);
                if ($dateTime->between($from, $to)) {
                    return false;
                }
            }
        }
        
        $daySchedule = $this->schedule_data[$day] ?? null;
        if (!$daySchedule) return false;
        
        $mode = $daySchedule['mode'] ?? 'closed';
        
        switch ($mode) {
            case 'closed':
                return false;
            case 'open24':
                return true;
            case 'split':
                $split = $daySchedule['split'] ?? [];
                return ($time >= $split['o1'] && $time <= $split['c1']) || 
                       ($time >= $split['o2'] && $time <= $split['c2']);
            case 'fullday':
                $full = $daySchedule['full'] ?? [];
                return $time >= $full['o'] && $time <= $full['c'];
            default:
                return false;
        }
    }

    /**
     * Get next available time
     */
    public function getNextAvailableTime(Carbon $from = null): ?Carbon
    {
        $from = $from ?? Carbon::now();
        $current = $from->copy();
        
        // Check next 7 days
        for ($i = 0; $i < 7; $i++) {
            $checkDate = $current->copy()->addDays($i);
            $day = strtolower($checkDate->format('l'));
            $daySchedule = $this->schedule_data[$day] ?? null;
            
            if (!$daySchedule) continue;
            
            $mode = $daySchedule['mode'] ?? 'closed';
            if ($mode === 'closed') continue;
            
            if ($mode === 'open24') {
                return $checkDate->setTime(0, 0);
            }
            
            if ($mode === 'split') {
                $split = $daySchedule['split'] ?? [];
                $openTime = Carbon::parse($split['o1']);
                return $checkDate->setTime($openTime->hour, $openTime->minute);
            }
            
            if ($mode === 'fullday') {
                $full = $daySchedule['full'] ?? [];
                $openTime = Carbon::parse($full['o']);
                return $checkDate->setTime($openTime->hour, $openTime->minute);
            }
        }
        
        return null;
    }
}
