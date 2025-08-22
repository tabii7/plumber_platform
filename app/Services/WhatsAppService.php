<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // You can configure these in your .env file
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.whatsapp.com');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    /**
     * Send a WhatsApp message
     */
    public function sendMessage($phoneNumber, $message)
    {
        try {
            // For now, we'll log the message since we don't have a WhatsApp API configured
            // In production, you would integrate with WhatsApp Business API or a service like Twilio
            Log::info('WhatsApp message would be sent', [
                'to' => $phoneNumber,
                'message' => $message,
                'timestamp' => now()
            ]);

            // Example of how you might integrate with a real WhatsApp API:
            /*
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/messages', [
                'to' => $phoneNumber,
                'message' => $message,
                'type' => 'text'
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $phoneNumber,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'to' => $phoneNumber,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return false;
            }
            */

            return true; // For now, always return true since we're just logging
        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp message', [
                'to' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send welcome message to new user
     */
    public function sendWelcomeMessage($user, $hasActiveSubscription = false)
    {
        $message = $this->buildWelcomeMessage($user, $hasActiveSubscription);
        return $this->sendMessage($user->whatsapp_number, $message);
    }

    /**
     * Build welcome message content
     */
    private function buildWelcomeMessage($user, $hasActiveSubscription)
    {
        $message = "🚰 *Welcome to Plumber Platform!*\n\n";
        $message .= "Hello {$user->full_name}!\n";
        $message .= "Welcome to Plumber Platform! We're excited to have you on board.\n\n";
        
        $message .= "*Your Account Details:*\n";
        $message .= "• Role: " . ucfirst($user->role) . "\n";
        $message .= "• Email: {$user->email}\n";
        $message .= "• Address: {$user->address}\n";
        
        if ($user->company_name) {
            $message .= "• Company: {$user->company_name}\n";
        }
        
        if (!$hasActiveSubscription) {
            $message .= "\n🚀 *Get Started with Our Services*\n\n";
            $message .= "To start using our platform and connect with " . ($user->role === 'client' ? 'qualified plumbers' : 'clients') . ", you'll need an active subscription package.\n\n";
            
            $message .= "*Why Subscribe?*\n";
            $message .= "• " . ($user->role === 'client' ? 'Connect with verified plumbers in your area' : 'Get matched with clients looking for your services') . "\n";
            $message .= "• " . ($user->role === 'client' ? 'Quick response times and reliable service' : 'Manage your bookings and grow your business') . "\n";
            $message .= "• " . ($user->role === 'client' ? 'Transparent pricing and reviews' : 'Professional profile and marketing tools') . "\n\n";
            
            $message .= "Visit our website to view subscription packages: " . url('/#pricing') . "\n\n";
            $message .= "Choose a plan that fits your needs and start enjoying our services today!";
        } else {
            $message .= "\n🎉 *You're All Set!*\n\n";
            $message .= "You have an active subscription: {$user->subscription_plan}\n";
            $message .= "Your subscription is valid until: " . $user->subscription_ends_at->format('F j, Y') . "\n\n";
            $message .= "Visit your dashboard: " . url('/dashboard') . "\n\n";
            $message .= "Start exploring our platform and enjoy our services!";
        }
        
        $message .= "\n\n*Need Help?*\n";
        $message .= "If you have any questions or need assistance, feel free to reach out to our support team.\n\n";
        $message .= "Best regards,\nThe Plumber Platform Team";

        return $message;
    }
}
