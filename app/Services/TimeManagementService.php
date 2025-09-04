<?php

namespace App\Services;

use App\Models\PlumberSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TimeManagementService
{
    /**
     * Get or create plumber schedule
     */
    public function getPlumberSchedule(User $plumber): PlumberSchedule
    {
        return PlumberSchedule::firstOrCreate(
            ['user_id' => $plumber->id],
            [
                'timezone' => 'Europe/Brussels',
                'schedule_data' => $this->getDefaultSchedule(),
                'holidays' => [],
                'vacations' => []
            ]
        );
    }

    /**
     * Update plumber availability
     */
    public function updateAvailability(User $plumber, string $status): bool
    {
        try {
            if ($plumber->role !== 'plumber') {
                return false;
            }

            $plumber->availability_status = $status;
            $plumber->save();

            // Update plumber record
            if ($plumber->plumber) {
                $plumber->plumber->update(['availability_status' => $status]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update plumber availability', [
                'plumber_id' => $plumber->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if plumber is available at specific time
     */
    public function isAvailableAt(User $plumber, Carbon $dateTime): bool
    {
        if ($plumber->role !== 'plumber') {
            return false;
        }

        $schedule = $this->getPlumberSchedule($plumber);
        return $schedule->isAvailableAt($dateTime);
    }

    /**
     * Get next available time for plumber
     */
    public function getNextAvailableTime(User $plumber, Carbon $from = null): ?Carbon
    {
        if ($plumber->role !== 'plumber') {
            return null;
        }

        $schedule = $this->getPlumberSchedule($plumber);
        return $schedule->getNextAvailableTime($from);
    }

    /**
     * Export schedule as JSON (similar to time.php)
     */
    public function exportScheduleAsJson(User $plumber): array
    {
        if ($plumber->role !== 'plumber') {
            return [];
        }

        $schedule = $this->getPlumberSchedule($plumber);
        
        return [
            'plumber_id' => $plumber->id,
            'plumber_name' => $plumber->full_name,
            'timezone' => $schedule->timezone,
            'current_status' => $plumber->availability_status,
            'schedule' => $schedule->schedule_data,
            'holidays' => $schedule->holidays,
            'vacations' => $schedule->vacations,
            'exported_at' => now()->toISOString(),
            'next_available' => $this->getNextAvailableTime($plumber)?->toISOString()
        ];
    }

    /**
     * Get default weekly schedule
     */
    protected function getDefaultSchedule(): array
    {
        return [
            'monday' => [
                'available' => true,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ],
            'tuesday' => [
                'available' => true,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ],
            'wednesday' => [
                'available' => true,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ],
            'thursday' => [
                'available' => true,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ],
            'friday' => [
                'available' => true,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ],
            'saturday' => [
                'available' => true,
                'start_time' => '09:00',
                'end_time' => '16:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ],
            'sunday' => [
                'available' => false,
                'start_time' => null,
                'end_time' => null,
                'breaks' => []
            ]
        ];
    }

    /**
     * Check if plumber can handle additional requests
     */
    public function canHandleMoreRequests(User $plumber): bool
    {
        if ($plumber->role !== 'plumber') {
            return false;
        }

        // Check current workload
        $currentRequests = $plumber->currentRequest;
        
        // For now, allow multiple requests (can be adjusted based on business logic)
        return true;
    }

    /**
     * Assign request to plumber
     */
    public function assignRequestToPlumber(User $plumber, $requestId): bool
    {
        try {
            if ($plumber->role !== 'plumber') {
                return false;
            }

            $plumber->update(['current_request_id' => $requestId]);
            
            // Update availability status
            $this->updateAvailability($plumber, 'busy');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to assign request to plumber', [
                'plumber_id' => $plumber->id,
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Release plumber from request
     */
    public function releasePlumberFromRequest(User $plumber): bool
    {
        try {
            if ($plumber->role !== 'plumber') {
                return false;
            }

            $plumber->update(['current_request_id' => null]);
            
            // Update availability status
            $this->updateAvailability($plumber, 'available');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to release plumber from request', [
                'plumber_id' => $plumber->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

