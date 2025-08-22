<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class WelcomeNotification extends Notification
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $user = $notifiable;
        $hasActiveSubscription = $this->hasActiveSubscription($user);
        
        $message = (new MailMessage)
            ->subject('Welcome to Plumber Platform! 🚰')
            ->greeting("Hello {$user->full_name}!")
            ->line("Welcome to Plumber Platform! We're excited to have you on board.")
            ->line("You've successfully registered as a {$user->role}.");

        if ($user->company_name) {
            $message->line("Company: {$user->company_name}");
        }

        $message->line("Your account details:")
            ->line("• Email: {$user->email}")
            ->line("• WhatsApp: {$user->whatsapp_number}")
            ->line("• Address: {$user->address}");

        if (!$hasActiveSubscription) {
            $message->line("")
                ->line("🚀 **Get Started with Our Services**")
                ->line("To start using our platform and connect with " . ($user->role === 'client' ? 'qualified plumbers' : 'clients') . ", you'll need an active subscription package.")
                ->line("")
                ->line("**Why Subscribe?**")
                ->line("• " . ($user->role === 'client' ? 'Connect with verified plumbers in your area' : 'Get matched with clients looking for your services'))
                ->line("• " . ($user->role === 'client' ? 'Quick response times and reliable service' : 'Manage your bookings and grow your business'))
                ->line("• " . ($user->role === 'client' ? 'Transparent pricing and reviews' : 'Professional profile and marketing tools'))
                ->line("")
                ->action('View Subscription Packages', url('/#pricing'))
                ->line("Choose a plan that fits your needs and start enjoying our services today!");
        } else {
            $message->line("")
                ->line("🎉 **You're All Set!**")
                ->line("You have an active subscription: {$user->subscription_plan}")
                ->line("Your subscription is valid until: " . $user->subscription_ends_at->format('F j, Y'))
                ->line("")
                ->action('Go to Dashboard', url('/dashboard'))
                ->line("Start exploring our platform and enjoy our services!");
        }

        $message->line("")
            ->line("**Need Help?**")
            ->line("If you have any questions or need assistance, feel free to reach out to our support team.")
            ->line("")
            ->line("Best regards,")
            ->line("The Plumber Platform Team");

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
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
