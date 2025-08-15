<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function index()
    {
        $base = config('services.whatsapp_bot.url', 'http://127.0.0.1:3000');

        $status = null;
        $qr = null;
        try {
            $status = Http::timeout(5)->get($base.'/status')->json();
            $qrResp = Http::timeout(5)->get($base.'/get-qr')->json();
            $qr = $qrResp['qr'] ?? null;
        } catch (\Throwable $e) {
            $status = ['status' => 'Bot not reachable'];
        }

        return view('admin.whatsapp.index', compact('status','qr','base'));
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'number'  => 'required|string',
            'message' => 'required|string',
        ]);

        $base = config('services.whatsapp_bot.url', 'http://127.0.0.1:3000');

        try {
            $res = Http::timeout(10)->post($base.'/send-message', [
                'number'  => $request->number,
                'message' => $request->message,
            ])->json();

            return back()->with('success', 'Message queued: '.json_encode($res));
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: '.$e->getMessage());
        }
    }
}
