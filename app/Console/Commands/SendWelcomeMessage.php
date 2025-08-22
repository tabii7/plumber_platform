<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Notifications\WelcomeNotification;

class SendWelcomeMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'welcome:send {user_id?} {--all : Send to all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send welcome message to user(s)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $users = User::all();
            $this->info("Sending welcome messages to {$users->count()} users...");
            
            foreach ($users as $user) {
                $this->sendWelcomeMessage($user);
            }
            
            $this->info('Welcome messages sent to all users!');
        } else {
            $userId = $this->argument('user_id');
            
            if (!$userId) {
                $userId = $this->ask('Enter user ID:');
            }
            
            $user = User::find($userId);
            
            if (!$user) {
                $this->error("User with ID {$userId} not found!");
                return 1;
            }
            
            $this->sendWelcomeMessage($user);
            $this->info("Welcome message sent to user: {$user->full_name} ({$user->email})");
        }
        
        return 0;
    }
    
    private function sendWelcomeMessage(User $user)
    {
        // Send email notification (with error handling)
        try {
            $user->notify(new WelcomeNotification());
            $this->line("✓ Email sent to: {$user->email}");
        } catch (\Exception $e) {
            $this->warn("⚠ Email failed: {$e->getMessage()}");
        }
        
        // Send WhatsApp message
        $whatsappService = new WhatsAppService();
        $hasActiveSubscription = $user->subscription_status === 'active' && 
                               $user->subscription_ends_at && 
                               $user->subscription_ends_at->isFuture();
        $whatsappService->sendWelcomeMessage($user, $hasActiveSubscription);
        
        $this->line("✓ WhatsApp message logged for: {$user->whatsapp_number}");
        
        // Show subscription status
        if ($hasActiveSubscription) {
            $this->line("✓ User has active subscription: {$user->subscription_plan}");
        } else {
            $this->line("⚠ User needs subscription to access services");
        }
    }
}
