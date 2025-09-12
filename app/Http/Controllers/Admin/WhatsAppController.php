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

    /**
     * Disconnect WhatsApp session - clears session and disconnects.
     */
    public function logout()
    {
        $base = config('services.whatsapp_bot.url', 'http://127.0.0.1:3000');
        
        try {
            // First, try to call the bot's logout endpoint
            $res = Http::timeout(15)->post($base . '/logout');
            $data = $res->json();
            
            // Also clear local session files as backup
            $this->clearLocalSessionFiles();
            
            if ($data['success'] ?? false) {
                return redirect()->route('admin.whatsapp')
                    ->with('success', 'WhatsApp disconnected! New QR code will appear shortly.');
            } else {
                // Even if bot logout failed, we cleared local files
                return redirect()->route('admin.whatsapp')
                    ->with('success', 'Session cleared! New QR code will appear shortly.');
            }
        } catch (\Throwable $e) {
            // If bot is not reachable, still clear local files
            $this->clearLocalSessionFiles();
            
            return redirect()->route('admin.whatsapp')
                ->with('success', 'Session cleared! New QR code will appear shortly.');
        }
    }

    /**
     * Clear local WhatsApp session files as backup
     */
    private function clearLocalSessionFiles()
    {
        try {
            $whatsappBotPath = base_path('whatsapp-bot');
            
            // Directories to clear
            $directoriesToClear = [
                $whatsappBotPath . '/auth_info',
                $whatsappBotPath . '/.wwebjs_auth',
                $whatsappBotPath . '/sessions',
                $whatsappBotPath . '/store'
            ];
            
            foreach ($directoriesToClear as $dir) {
                if (is_dir($dir)) {
                    $this->deleteDirectory($dir);
                    \Log::info("Cleared WhatsApp session directory: " . $dir);
                }
            }
            
            // Clear auth-related JSON files
            if (is_dir($whatsappBotPath)) {
                $files = glob($whatsappBotPath . '/*.json');
                foreach ($files as $file) {
                    $filename = basename($file);
                    if (strpos($filename, 'auth') !== false || 
                        strpos($filename, 'session') !== false || 
                        strpos($filename, 'store') !== false) {
                        unlink($file);
                        \Log::info("Cleared WhatsApp session file: " . $file);
                    }
                }
            }
            
        } catch (\Throwable $e) {
            \Log::error('Failed to clear local WhatsApp session files: ' . $e->getMessage());
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        return rmdir($dir);
    }


}
