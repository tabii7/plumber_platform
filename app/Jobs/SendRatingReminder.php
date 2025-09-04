<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
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
        $req = WaRequest::find($this->requestId);
        if (!$req) return;

        // Skip if already rated
        $rated = \DB::table('ratings')->where('request_id', $req->id)->exists();
        if ($rated) return;

        $customer = User::find($req->customer_id);
        if (!$customer || !$customer->whatsapp_number) return;

        $msg  = "⭐ Rate Your Experience\n\n";
        $msg .= "How was your experience with the plumber?\n";
        $msg .= "Please reply with a number from 1 to 5.\n";
        $msg .= "You can also type 'rate' to start the rating flow.";

        try {
            $botUrl = rtrim(config('services.wa_bot.url', 'http://127.0.0.1:3000'), '/');
            Http::post($botUrl . '/send-message', [
                'number'  => $customer->whatsapp_number,
                'message' => $msg,
            ])->throw();
        } catch (\Throwable $e) {
            \Log::error('Rating reminder send failed', ['request_id'=>$req->id,'error'=>$e->getMessage()]);
        }
    }
}
