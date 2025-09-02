<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlumberSchedule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlumberScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:plumber');
    }

    /**
     * Show the schedule management page
     */
    public function index()
    {
        $user = Auth::user();
        $schedule = PlumberSchedule::where('user_id', $user->id)->first();
        
        if (!$schedule) {
            $schedule = PlumberSchedule::create([
                'user_id' => $user->id,
                'timezone' => 'Europe/Brussels',
                'schedule_data' => PlumberSchedule::getDefaultSchedule(),
                'holidays' => [],
                'vacations' => [],
                'last_updated' => now()
            ]);
        }
        
        return view('plumber.schedule.index', compact('schedule'));
    }

    /**
     * Update the schedule
     */
    public function update(Request $request)
    {
        $request->validate([
            'schedule_data' => 'required|array',
            'holidays' => 'nullable|array',
            'holidays.*' => 'date',
            'vacations' => 'nullable|array',
            'vacations.*.from' => 'required_with:vacations.*.to|date',
            'vacations.*.to' => 'required_with:vacations.*.from|date',
            'vacations.*.note' => 'nullable|string'
        ]);

        $user = Auth::user();
        $schedule = PlumberSchedule::where('user_id', $user->id)->first();
        
        if (!$schedule) {
            $schedule = new PlumberSchedule(['user_id' => $user->id]);
        }
        
        $schedule->fill([
            'schedule_data' => $request->schedule_data,
            'holidays' => $request->holidays ?? [],
            'vacations' => $request->vacations ?? [],
            'last_updated' => now()
        ]);
        
        $schedule->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
            'schedule' => $schedule
        ]);
    }

    /**
     * Get schedule data for API
     */
    public function getSchedule()
    {
        $user = Auth::user();
        $schedule = PlumberSchedule::where('user_id', $user->id)->first();
        
        if (!$schedule) {
            $schedule = PlumberSchedule::create([
                'user_id' => $user->id,
                'timezone' => 'Europe/Brussels',
                'schedule_data' => PlumberSchedule::getDefaultSchedule(),
                'holidays' => [],
                'vacations' => [],
                'last_updated' => now()
            ]);
        }
        
        return response()->json($schedule);
    }

    /**
     * Check availability at specific time
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'datetime' => 'required|date'
        ]);
        
        $user = Auth::user();
        $schedule = PlumberSchedule::where('user_id', $user->id)->first();
        
        if (!$schedule) {
            return response()->json(['available' => false, 'reason' => 'No schedule set']);
        }
        
        $dateTime = Carbon::parse($request->datetime);
        $isAvailable = $schedule->isAvailableAt($dateTime);
        
        return response()->json([
            'available' => $isAvailable,
            'datetime' => $dateTime->toISOString(),
            'next_available' => $schedule->getNextAvailableTime($dateTime)?->toISOString()
        ]);
    }
}
