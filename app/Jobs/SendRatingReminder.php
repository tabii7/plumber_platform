<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WaRequest;
use App\Models\WaLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRatingReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $requestId;

    public function __construct(int $requestId)
    {
        $this->requestId = $requestId;
    }

    public function handle(): void
    {
        $request = WaRequest::find($this->requestId);
        if (!$request) {
            return;
        }

        // If already rated, skip
        if ($request->status === 'rated') {
            return;
        }

        $customer = User::find($request->customer_id);
        if (!$customer) {
            return;
        }

        $msg = "⭐ Quick reminder to rate your plumber\n\n"
             . "Job ID: {$request->id}\n"
             . "Problem: {$request->problem}\n"
             . "Description: \"{$request->description}\"\n\n"
             . "Please reply with a number from 1 to 5 to rate your experience.\n"
             . "You can also type 'rate' anytime.";

        try {
            $botUrl = rtrim(config('services.wa_bot.url', 'http://127.0.0.1:3000'), '/');
            \Http::post($botUrl . '/send-message', [
                'number'  => $customer->whatsapp_number,
                'message' => $msg,
            ])->throw();
        } catch (\Throwable $e) {
            \Log::error('WA rating reminder failed', ['to' => $customer->whatsapp_number, 'error' => $e->getMessage()]);
            // Fallback log record
            WaLog::create([
                'wa_number' => $customer->whatsapp_number,
                'direction' => 'out',
                'payload_json' => ['type' => 'text', 'body' => $msg, 'fallback' => 'rating_reminder_failed_send'],
                'status' => 'queued'
            ]);
        }
    }
}
