<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

// 👇 add these for dynamic flows
use App\Models\WaSession;
use App\Models\WaFlow;
use App\Models\WaLog;
use App\Services\WaFlowEngine;

class WhatsAppController extends Controller
{
    public function incoming(Request $request, WaFlowEngine $engine)
    {
        $from    = preg_replace('/[^0-9]/', '', (string) $request->input('from'));
        $message = strtolower(trim((string) $request->input('message')));

        if ($from === '' || $message === '') {
            return response()->json(['reply' => null]);
        }

        $user = User::where('whatsapp_number', $from)
                    ->orWhere('phone', $from)
                    ->first();

        // --- DYNAMIC FLOW HANDOFF (if session exists or entry keyword matches) ---
        $role = $user?->role; // may be null for unregistered
        $hasActiveSession = WaSession::where('wa_number', $from)
            ->where('last_message_at', '>=', now()->subMinutes(240))
            ->exists();

        $matchesEntryKeyword = WaFlow::query()
            ->where('is_active', true)
            ->when($role, fn($q) => $q->where('target_role', $role))
            ->where('entry_keyword', $message)
            ->exists();

        if ($hasActiveSession || $matchesEntryKeyword) {
            // Log inbound
            WaLog::create([
                'wa_number'    => $from,
                'direction'    => 'in',
                'payload_json' => ['text' => $message],
                'status'       => 'ok',
            ]);

            $reply = $engine->startOrResume($from, $role, $message);

            if ($reply) {
                WaLog::create([
                    'wa_number'    => $from,
                    'direction'    => 'out',
                    'payload_json' => ['text' => $reply],
                    'status'       => 'ok',
                ]);
            }

            return response()->json(['reply' => $reply]);
        }
        // --- END dynamic handoff. If no flow triggers, fall back to your old logic below. ---

        // ORIGINAL BEHAVIOR (kept)
        if (!$user) {
            return response()->json([
                'reply' => "👋 Welcome! Please register on our website with your postal code."
            ]);
        }

        $state = $user->conversation_state;

        // CLIENT FLOW
        if ($user->role === 'client') {
            if ($message === 'info') {
                $user->update(['conversation_state' => 'awaiting_confirmation']);
                return response()->json([
                    'reply' => "Hello {$user->full_name}, do you need a plumber in {$user->postal_code} - {$user->city}? Reply YES or NO."
                ]);
            }

            if ($state === 'awaiting_confirmation' && $message === 'yes') {
                $user->update(['conversation_state' => 'awaiting_service']);
                return response()->json([
                    'reply' => "Please choose a service:\n\n".
                               "1. Emergency plumber\n".
                               "2. 24/7 plumber\n".
                               "3. Sanitary repairs\n".
                               "4. Toilet clogged\n".
                               "5. Kitchen drain blocked\n".
                               "Reply with the number."
                ]);
            }

            if ($state === 'awaiting_confirmation' && in_array($message, ['no', 'n'])) {
                $user->update(['conversation_state' => null]);
                return response()->json([
                    'reply' => "👍 Thanks for using our service!"
                ]);
            }

            if ($state === 'awaiting_service' && in_array($message, ['1','2','3','4','5'])) {
                $user->update(['conversation_state' => null]);
                return response()->json([
                    'reply' => "✅ Service request received! We'll assign a plumber shortly."
                ]);
            }
        }

        // PLUMBER FLOW
        if ($user->role === 'plumber') {
            if ($message === 'plumber') {
                $user->update(['conversation_state' => 'awaiting_status']);
                return response()->json([
                    'reply' => "Hello {$user->full_name}, set your status:\n".
                               "1. Available\n".
                               "2. Busy\n".
                               "3. On holiday"
                ]);
            }

            if ($state === 'awaiting_status' && in_array($message, ['1', 'available'])) {
                $user->update(['conversation_state' => null, 'status' => 'available']); // ← store status (not werk_radius)
                return response()->json(['reply' => "✅ Status set: Available"]);
            }
            if ($state === 'awaiting_status' && in_array($message, ['2', 'busy'])) {
                $user->update(['conversation_state' => null, 'status' => 'busy']);
                return response()->json(['reply' => "⏳ Status set: Busy"]);
            }
            if ($state === 'awaiting_status' && in_array($message, ['3', 'holiday'])) {
                $user->update(['conversation_state' => null, 'status' => 'holiday']);
                return response()->json(['reply' => "🌴 Status set: On holiday"]);
            }
        }

        // Fallback
        return response()->json([
            'reply' => "🤔 Please type 'info' if you're a client 📋 or 'plumber' if you're a plumber 🔧"
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'number'  => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $response = Http::timeout(15)->post('http://127.0.0.1:3000/send-message', [
                'number'  => $validated['number'],
                'message' => $validated['message'],
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
