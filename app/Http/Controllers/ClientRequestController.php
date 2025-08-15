<?php

namespace App\Http\Controllers;
use App\Models\Request as ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ClientRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
   public function create()
{
    $services = [
        1 => 'Loodgieter dringend',
        2 => 'Spoed loodgieter',
        3 => 'Loodgieter 24/7',
        4 => 'Sanitair herstellen',
        5 => 'WC verstopt',
        6 => 'Toilet loopt over',
        7 => 'Afvoer verstopt',
        8 => 'Lavabo loopt niet door',
        9 => 'Badkamer kraan lekt',
        10 => 'Keukenafvoer verstopt',
        11 => 'Waterlek in keuken/badkamer',
        12 => 'Lekkende buis herstellen',
        13 => 'Waterdruk te laag',
    ];

    return view('client.requests.create', compact('services'));
}


    /**
     * Store a newly created resource in storage.
     */public function store(Request $request)
{
    $request->validate([
        'service_id'   => 'required|integer',
        'description'  => 'required|string|max:500',
    ]);

    $user = Auth::user();

    if (!$user->postal_code_id) {
        return redirect()->back()->withErrors(['postal_code' => 'Your profile does not have a postal code set.']);
    }

    ServiceRequest::create([
        'client_id'       => $user->id,
        'postal_code_id'  => $user->postal_code_id,   // 🔹 using the FK instead of string
        'service_id'      => $request->service_id,
        'description'     => $request->description,
        'status'          => 'pending',
    ]);

    return redirect()->route('requests.index')->with('success', 'Request submitted successfully!');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
