<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\WelcomeNotification;
use App\Services\WhatsAppService;

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
            'company_name'     => 'nullable|string|max:255',
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
        ]);

        $user = User::create([
            'full_name'       => $validated['full_name'],
            'company_name'    => $validated['company_name'] ?? null,
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
            'address_json'    => $validated['address_json'] ?? null,
        ]);

        Auth::login($user);

        // Send welcome notification via email
        $user->notify(new WelcomeNotification());

        // Send welcome message via WhatsApp
        $whatsappService = new WhatsAppService();
        $hasActiveSubscription = $user->subscription_status === 'active' && 
                               $user->subscription_ends_at && 
                               $user->subscription_ends_at->isFuture();
        $whatsappService->sendWelcomeMessage($user, $hasActiveSubscription);

        return redirect()->route('dashboard')->with('success', 'Account aangemaakt!');
    }
}
