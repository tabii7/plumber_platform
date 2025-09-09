<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminWhatsappController extends Controller
{
    protected string $botUrl;

    public function __construct()
    {
        $this->botUrl = config('services.whatsapp.bot_url', 'http://127.0.0.1:3000');
    }

    /**
     * Page: shows Connected state OR QR to scan.
     */
    public function index()
    {
        $connected = false;
        $status = null;
        $qr = null;
        $error = null;

        // 1) Check status
        try {
            $statusRes = Http::timeout(5)->get($this->botUrl . '/status');
            $statusData = $statusRes->json();
            $status = $statusData['status'] ?? 'Unknown';
            $connected = str_contains(strtolower($status), 'connected');
        } catch (\Throwable $e) {
            $status = 'Bot not reachable';
            $error = $e->getMessage();
        }

        // 2) If not connected, get QR
        if (! $connected) {
            try {
                $qrRes = Http::timeout(5)->get($this->botUrl . '/get-qr');
                $qr = $qrRes->json('qr'); // can be null if bot says already connected
            } catch (\Throwable $e) {
                $error = $error ?: $e->getMessage();
            }
        }

        return view('admin.whatsapp', [
            'connected' => $connected,
            'qr'        => $qr,
            'status'    => $status,
            'error'     => $error,
            'bot'       => $this->botUrl,
        ]);
    }

    /**
     * JSON: fetch QR (for the Refresh button or AJAX).
     */
    public function qr()
    {
        try {
            $res = Http::timeout(5)->get($this->botUrl . '/get-qr');
            return response()->json($res->json());
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Bot not reachable'], 500);
        }
    }

    /**
     * JSON: fetch status (for the Check Status button or AJAX).
     */
    public function status()
    {
        try {
            $res = Http::timeout(5)->get($this->botUrl . '/status');
            return response()->json($res->json());
        } catch (\Throwable $e) {
            return response()->json(['status' => 'Bot not reachable'], 200);
        }
    }

    /**
     * Logout from WhatsApp - clears session and disconnects.
     */
    public function logout()
    {
        try {
            $res = Http::timeout(10)->post($this->botUrl . '/logout');
            $data = $res->json();
            
            if ($data['success'] ?? false) {
                return redirect()->route('admin.whatsapp')
                    ->with('success', 'WhatsApp session logged out successfully. The bot will need to be restarted to reconnect.');
            } else {
                return redirect()->route('admin.whatsapp')
                    ->with('error', 'Failed to logout: ' . ($data['error'] ?? 'Unknown error'));
            }
        } catch (\Throwable $e) {
            return redirect()->route('admin.whatsapp')
                ->with('error', 'Failed to logout: ' . $e->getMessage());
        }
    }
}
