<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WaRequest;
use App\Models\Plumber;
use App\Models\PlumberCoverage;

class AdminController extends Controller
{
    public function dashboard()
    {
        // User statistics
        $totalUsers = User::count();
        $totalPlumbers = User::where('role', 'plumber')->count();
        $totalClients = User::where('role', 'client')->count();
        
        // Subscription statistics
        $activeSubscriptions = User::where('subscription_status', 'active')->count();
        
        // Service statistics
        $totalRequests = WaRequest::count();
        $completedJobs = WaRequest::where('status', 'completed')->count();
        
        // Rating statistics
        $averageRating = 4.5; // This would come from actual rating data
        
        // Coverage statistics
        $totalCoverageAreas = PlumberCoverage::count();
        
        // Recent activity (placeholder - you can implement actual activity tracking)
        $recentActivity = collect([
            (object) [
                'description' => 'New plumber registered',
                'created_at' => now()->subHours(2)
            ],
            (object) [
                'description' => 'Service request completed',
                'created_at' => now()->subHours(4)
            ],
            (object) [
                'description' => 'New subscription activated',
                'created_at' => now()->subHours(6)
            ]
        ]);

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalPlumbers', 
            'totalClients',
            'activeSubscriptions',
            'totalRequests',
            'completedJobs',
            'averageRating',
            'totalCoverageAreas',
            'recentActivity'
        ));
    }
}
