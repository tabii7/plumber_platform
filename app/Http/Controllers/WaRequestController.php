<?php

namespace App\Http\Controllers;

use App\Models\WaRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaRequestController extends Controller
{
    /**
     * Display a listing of requests for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $requests = collect();

        if ($user->role === 'admin') {
            // Admin sees all requests
            $requests = WaRequest::with(['customer', 'selectedPlumber'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } elseif ($user->role === 'plumber') {
            // Plumber sees requests assigned to them
            $requests = WaRequest::with(['customer'])
                ->where('selected_plumber_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } elseif ($user->role === 'client') {
            // Client sees their own requests
            $requests = WaRequest::with(['selectedPlumber'])
                ->where('customer_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('requests.index', compact('requests'));
    }

    /**
     * Display the specified request
     */
    public function show(WaRequest $request)
    {
        $user = Auth::user();
        
        // Check if user has access to this request
        if ($user->role === 'client' && $request->customer_id !== $user->id) {
            abort(403, 'Unauthorized access to this request.');
        }
        
        if ($user->role === 'plumber' && $request->selected_plumber_id !== $user->id) {
            abort(403, 'Unauthorized access to this request.');
        }

        $request->load(['customer', 'selectedPlumber', 'offers']);

        return view('requests.show', compact('request'));
    }

    /**
     * Mark a request as completed (plumber only)
     */
    public function complete(Request $httpRequest, WaRequest $waRequest)
    {
        $user = Auth::user();
        
        // Only the assigned plumber can mark as complete
        if ($user->role !== 'plumber' || $waRequest->selected_plumber_id !== $user->id) {
            abort(403, 'Only the assigned plumber can mark this request as complete.');
        }

        // Only allow completion if request is active or in progress
        if (!in_array($waRequest->status, ['active', 'in_progress'])) {
            return redirect()->back()->withErrors(['status' => 'Request cannot be marked as complete in its current status.']);
        }

        $waRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Request marked as completed successfully!');
    }

    /**
     * Update request status (admin only)
     */
    public function updateStatus(Request $httpRequest, WaRequest $waRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can update request status.');
        }

        // Debug: Log the request details
        \Log::info("updateStatus called with request ID: " . $httpRequest->route('waRequest'));
        \Log::info("WaRequest model: " . ($waRequest ? "Found ID: {$waRequest->id}" : "Not found"));

        $httpRequest->validate([
            'status' => 'required|in:broadcasting,active,in_progress,completed,cancelled',
        ]);

        $oldStatus = $waRequest->status;
        $newStatus = $httpRequest->status;
        
        // Log the update attempt
        \Log::info("Updating request {$waRequest->id} from '{$oldStatus}' to '{$newStatus}'");
        
        // Prepare update data
        $updateData = ['status' => $newStatus];
        
        // Update completed_at based on status
        if ($newStatus === 'completed') {
            $updateData['completed_at'] = now();
        } elseif ($newStatus === 'cancelled') {
            $updateData['completed_at'] = null;
        } elseif (in_array($newStatus, ['broadcasting', 'active', 'in_progress'])) {
            $updateData['completed_at'] = null;
        }
        
        $waRequest->update($updateData);

        // Refresh the model to get updated data
        $waRequest->refresh();
        
        // Log the result
        \Log::info("Request {$waRequest->id} status after update: '{$waRequest->status}', completed_at: '{$waRequest->completed_at}'");

        return redirect()->back()->with('success', "Request status updated from '{$oldStatus}' to '{$waRequest->status}' successfully!");
    }


    /**
     * Get request statistics for dashboard
     */
    public function getStats()
    {
        $user = Auth::user();
        $stats = [];

        if ($user->role === 'admin') {
            $stats = [
                'total' => WaRequest::count(),
                'broadcasting' => WaRequest::where('status', 'broadcasting')->count(),
                'active' => WaRequest::where('status', 'active')->count(),
                'in_progress' => WaRequest::where('status', 'in_progress')->count(),
                'completed' => WaRequest::where('status', 'completed')->count(),
                'cancelled' => WaRequest::where('status', 'cancelled')->count(),
            ];
        } elseif ($user->role === 'plumber') {
            $stats = [
                'assigned' => WaRequest::where('selected_plumber_id', $user->id)->count(),
                'active' => WaRequest::where('selected_plumber_id', $user->id)->where('status', 'active')->count(),
                'in_progress' => WaRequest::where('selected_plumber_id', $user->id)->where('status', 'in_progress')->count(),
                'completed' => WaRequest::where('selected_plumber_id', $user->id)->where('status', 'completed')->count(),
            ];
        } elseif ($user->role === 'client') {
            $stats = [
                'total' => WaRequest::where('customer_id', $user->id)->count(),
                'broadcasting' => WaRequest::where('customer_id', $user->id)->where('status', 'broadcasting')->count(),
                'active' => WaRequest::where('customer_id', $user->id)->where('status', 'active')->count(),
                'completed' => WaRequest::where('customer_id', $user->id)->where('status', 'completed')->count(),
            ];
        }

        return response()->json($stats);
    }
}
