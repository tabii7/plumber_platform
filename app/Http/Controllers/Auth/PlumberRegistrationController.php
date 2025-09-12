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

class PlumberRegistrationController extends Controller
{
    /**
     * Display the plumber registration view.
     */
    public function create(): View
    {
        $postalCodes = PostalCode::orderBy('Postcode')->get();
        return view('auth.plumber-register', compact('postalCodes'));
    }

    /**
     * Handle an incoming plumber registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:255'],
            'address_json' => ['nullable', 'string'],
            'btw_number' => ['nullable', 'string', 'max:50'],
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
            'role' => 'plumber',
            'btw_number' => $request->btw_number,
            'address_json' => $request->address_json,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Automatically add user's municipality to coverage areas
        $user->addDefaultMunicipalityCoverage();

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

        return redirect()->route('plumber.dashboard')->with('success', 'Plumber account created successfully!');
    }
}
