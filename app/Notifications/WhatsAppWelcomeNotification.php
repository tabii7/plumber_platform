<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class WhatsAppWelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $user = $notifiable;
        $hasActiveSubscription = $this->hasActiveSubscription($user);
        
        $message = "🚰 *Welcome to Loodgieter.app!*\n\n";
        $message .= "Hello {$user->full_name}!\n";
        $message .= "Welcome to Loodgieter.app! We're excited to have you on board.\n\n";
        
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
        $message .= "Best regards,\nThe Loodgieter.app Team";

        return [
            'whatsapp_number' => $user->whatsapp_number,
            'message' => $message,
            'type' => 'welcome_message',
            'user_id' => $user->id,
        ];
    }

    /**
     * Check if user has an active subscription
     */
    private function hasActiveSubscription(User $user): bool
    {
        return $user->subscription_status === 'active' && 
               $user->subscription_ends_at && 
               $user->subscription_ends_at->isFuture();
    }
}
