<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Request as ServiceRequest;
use App\Models\Plumber;

class AdminController extends Controller
{
    public function dashboard()
    {
        $clients = User::where('role', 'client')->count();
        $plumbers = User::where('role', 'plumber')->count();
        $requests = ServiceRequest::count();

        return view('admin.dashboard', compact('clients', 'plumbers', 'requests'));
    }
}
