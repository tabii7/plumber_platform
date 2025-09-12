<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\PostalCode;
use App\Notifications\WelcomeNotification;
use App\Services\WhatsAppService;

class ClientRegistrationController extends Controller
{
    /**
     * Display the client registration view.
     */
    public function create(): View
    {
        $postalCodes = PostalCode::orderBy('Postcode')->get();
        return view('auth.client-register', compact('postalCodes'));
    }

    /**
     * Handle an incoming client registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:255'],
            'address_json' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'whatsapp_number' => $request->whatsapp_number,
            'address' => $request->address,
            'number' => $request->number,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'country' => 'Belgium',
            'role' => 'client',
            'address_json' => $request->address_json,
            // Add default yearly subscription for clients
            'subscription_plan' => 'client_yearly',
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Send welcome notification via email
        try {
            $user->notify(new WelcomeNotification());
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }

        // Send welcome message via WhatsApp
        try {
            $whatsappService = new WhatsAppService();
            $hasActiveSubscription = $user->subscription_status === 'active' && 
                                   $user->subscription_ends_at && 
                                   $user->subscription_ends_at->isFuture();
            $whatsappService->sendWelcomeMessage($user, $hasActiveSubscription);
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome WhatsApp message', [
                'user_id' => $user->id,
                'whatsapp_number' => $user->whatsapp_number,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('client.dashboard')->with('success', 'Client account created successfully!');
    }
}
