<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default yearly subscription to existing clients who don't have a subscription
        $clientsWithoutSubscription = User::where('role', 'client')
            ->where(function($query) {
                $query->whereNull('subscription_plan')
                      ->orWhere('subscription_plan', '')
                      ->orWhere('subscription_status', 'inactive');
            })
            ->get();

        foreach ($clientsWithoutSubscription as $client) {
            $client->update([
                'subscription_plan' => 'client_yearly',
                'subscription_status' => 'active',
                'subscription_ends_at' => now()->addYear(),
            ]);
        }

        \Log::info('Added default yearly subscription to ' . $clientsWithoutSubscription->count() . ' existing clients');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default yearly subscription from clients
        User::where('role', 'client')
            ->where('subscription_plan', 'client_yearly')
            ->where('subscription_status', 'active')
            ->update([
                'subscription_plan' => null,
                'subscription_status' => 'inactive',
                'subscription_ends_at' => null,
            ]);
    }
};