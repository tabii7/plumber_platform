<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mollie\Laravel\Facades\Mollie;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $plan = $request->get('plan');
        
        // Define subscription plans
        $plans = [
            'client_monthly' => [
                'name' => 'Client Monthly',
                'price' => 19.99,
                'currency' => 'EUR',
                'description' => 'Client subscription - Monthly plan'
            ],
            'client_yearly' => [
                'name' => 'Client Yearly',
                'price' => 119.88,
                'currency' => 'EUR',
                'description' => 'Client subscription - Yearly plan (50% off)'
            ],
            'company_monthly' => [
                'name' => 'Company Monthly',
                'price' => 29.99,
                'currency' => 'EUR',
                'description' => 'Company subscription - Monthly plan'
            ],
            'company_yearly' => [
                'name' => 'Company Yearly',
                'price' => 179.88,
                'currency' => 'EUR',
                'description' => 'Company subscription - Yearly plan (50% off)'
            ]
        ];

        if (!isset($plans[$plan])) {
            return redirect()->route('welcome')->with('error', 'Invalid subscription plan.');
        }

        $selectedPlan = $plans[$plan];

        // Create Mollie payment
        $paymentData = [
            'amount' => [
                'currency' => $selectedPlan['currency'],
                'value' => number_format($selectedPlan['price'], 2, '.', '')
            ],
            'description' => $selectedPlan['description'],
            'redirectUrl' => route('checkout.success', ['plan' => $plan]),
            'metadata' => [
                'plan' => $plan,
                'user_id' => Auth::id(),
                'plan_name' => $selectedPlan['name']
            ]
        ];

        // Only add webhook URL if not in local development
        if (!app()->environment('local')) {
            $paymentData['webhookUrl'] = route('checkout.webhook');
        } else {
            \Log::info('Skipping webhook URL for local development');
        }

        try {
            \Log::info('Creating Mollie payment with data:', $paymentData);
        $payment = Mollie::api()->payments->create($paymentData);
        } catch (\Exception $e) {
            \Log::error('Mollie payment creation failed: ' . $e->getMessage());
            return redirect()->route('welcome')->with('error', 'Payment setup failed. Please try again.');
        }

        // Store payment info in session for success page
        session([
            'payment_id' => $payment->id,
            'plan' => $plan,
            'plan_details' => $selectedPlan
        ]);

        // Redirect to Mollie payment page
        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function success(Request $request)
    {
        $plan = $request->get('plan');
        $paymentId = session('payment_id');
        
        if (!$paymentId) {
            return redirect()->route('welcome')->with('error', 'Payment session not found.');
        }

        // Get payment status from Mollie
        try {
            $payment = Mollie::api()->payments->get($paymentId);
        } catch (\Exception $e) {
            \Log::error('Mollie payment retrieval failed: ' . $e->getMessage());
            return redirect()->route('welcome')->with('error', 'Unable to verify payment status. Please contact support.');
        }
        
        if ($payment->isPaid()) {
            // Update user subscription
            $user = Auth::user();
            $user->subscription_plan = $plan;
            $user->subscription_status = 'active';
            $user->subscription_ends_at = $this->calculateSubscriptionEndDate($plan);
            $user->save();

            return view('checkout.success', [
                'plan' => $plan,
                'payment' => $payment,
                'plan_details' => session('plan_details')
            ]);
        } elseif ($payment->isPending()) {
            // Payment is pending, show pending message
            return view('checkout.pending', [
                'plan' => $plan,
                'payment' => $payment,
                'plan_details' => session('plan_details')
            ]);
        } else {
            return redirect()->route('welcome')->with('error', 'Payment was not completed successfully. Status: ' . $payment->status);
        }
    }

    public function webhook(Request $request)
    {
        $paymentId = $request->input('id');
        
        try {
            $payment = Mollie::api()->payments->get($paymentId);
        } catch (\Exception $e) {
            \Log::error('Mollie webhook payment retrieval failed: ' . $e->getMessage());
            return response('Error', 500);
        }

        if ($payment->isPaid()) {
            // Handle successful payment
            $metadata = $payment->metadata;
            $userId = $metadata->user_id ?? null;
            $plan = $metadata->plan ?? null;

            if ($userId && $plan) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->subscription_plan = $plan;
                    $user->subscription_status = 'active';
                    $user->subscription_ends_at = $this->calculateSubscriptionEndDate($plan);
                    $user->save();
                }
            }
        }

        return response('OK', 200);
    }

    private function calculateSubscriptionEndDate($plan)
    {
        $now = now();
        
        if (str_contains($plan, 'yearly')) {
            return $now->addYear();
        } else {
            return $now->addMonth();
        }
    }
}
