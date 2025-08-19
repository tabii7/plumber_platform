<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
     public function show()
    {
        return view('auth.register'); // loads resources/views/register.blade.php
    }

    public function store(Request $request)
    {

   
        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'whatsapp_number'  => 'required|string|max:20',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|string|min:6',
            'address'          => 'required|string|max:255',
            'number'           => 'nullable|string|max:10',
            'postal_code'      => 'nullable|string|max:10',
            'city'             => 'nullable|string|max:255',
            'address_json'     => 'nullable|string',
            'role'             => 'required|in:client,plumber',
            'btw_number'       => 'nullable|string|max:50',
            'werk_radius'      => 'nullable|string',
        ]);

        $user = User::create([
            'full_name'       => $validated['full_name'],
            'phone'           => $validated['phone'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'address'         => $validated['address'],
            'number'          => $validated['number'] ?? null,
            'postal_code'     => $validated['postal_code'] ?? null,
            'city'            => $validated['city'] ?? null,
            'country'         => 'Belgium', // Default to Belgium
            'role'            => $validated['role'],
            'btw_number'      => $validated['btw_number'] ?? null,
            'werk_radius'     => $validated['werk_radius'] ?? null,
            'address_json'    => $validated['address_json'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account aangemaakt!');
    }
}
