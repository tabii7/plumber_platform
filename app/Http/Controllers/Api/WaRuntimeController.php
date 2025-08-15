<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{WaFlow, WaNode, WaSession, WaLog, User, Category};

class WaRuntimeController extends Controller
{
    public function incoming(Request $request)
    {
        $from   = preg_replace('/\D+/', '', (string) $request->input('from'));
        $text   = strtolower(trim((string) $request->input('message')));
        $now    = now();

        // Log incoming
        WaLog::create([
            'wa_number'    => $from,
            'direction'    => 'in',
            'payload_json' => $request->all(),
            'status'       => 'recv'
        ]);

        $user = User::where('whatsapp_number', $from)
            ->orWhere('phone', $from)
            ->first();

        if (!$user) {
            return $this->replyText($from, "You are not registered on our platform. Please register to continue.");
        }

        $role = $user->role ?? 'any';

        $session = WaSession::where('wa_number', $from)->first();

        /**
         * 1. No session yet → must send one of the allowed keywords
         */
        if (!$session) {
            $allowedKeywords = ['help', 'info', 'plumber'];

            if (!in_array($text, $allowedKeywords)) {
                return $this->replyText($from, "Type 'info' if you're a client 📋 or 'plumber' if you're a plumber 🔧");
            }

            // Select flow by role and keyword
            $flowQuery = WaFlow::where('is_active', true)
                ->whereRaw('LOWER(entry_keyword) = ?', [$text]);

            if ($role !== 'any') {
                $flowQuery->where(function ($q) use ($role) {
                    $q->where('target_role', 'any')->orWhere('target_role', $role);
                });
            }

            $flow = $flowQuery->first();

            if (!$flow) {
                return $this->replyText($from, "No active flow found for your role.");
            }

            $node = $flow->nodes()->orderBy('sort')->first();

            $session = WaSession::create([
                'wa_number'       => $from,
                'user_id'         => $user->id ?? null,
                'flow_code'       => $flow->code,
                'node_code'       => $node->code,
                'context_json'    => [],
                'last_message_at' => $now,
            ]);

            return $this->renderNode($from, $node, $user, $session);
        }

        /**
         * 2. Continuing existing session
         */
        $flow = WaFlow::where('code', $session->flow_code)->firstOrFail();
        $node = WaNode::where('flow_id', $flow->id)
            ->where('code', $session->node_code)
            ->firstOrFail();

        $ctx = $session->context_json ?? [];

        switch ($node->type) {
            case 'buttons':
                $nextKey = $node->next_map_json[$text] ?? ($node->next_map_json['default'] ?? null);

                if ($flow->code === 'plumber_flow' && in_array($text, ['available', 'busy', 'holiday']) && $user) {
                    $user->status = $text;
                    $user->save();
                    $ctx['status'] = $text;
                }

                if (!$nextKey) {
                    return $this->repeatNode($from, $node);
                }

                $session->node_code = $nextKey;
                $session->context_json = $ctx;
                $session->last_message_at = $now;
                $session->save();

                $next = WaNode::where('flow_id', $flow->id)
                    ->where('code', $nextKey)
                    ->first();

                return $this->renderNode($from, $next, $user, $session);

            case 'list':
                $ctx['selected'] = $text;
                if (str_starts_with($text, 'cat:')) {
                    $ctx['category_row'] = $text;
                }
                $nextKey = $node->next_map_json['default'] ?? null;
                if (!$nextKey) return $this->replyText($from, "Sorry, try again.");

                $session->node_code = $nextKey;
                $session->context_json = $ctx;
                $session->last_message_at = $now;
                $session->save();

                $next = WaNode::where('flow_id', $flow->id)
                    ->where('code', $nextKey)
                    ->first();

                return $this->renderNode($from, $next, $user, $session);

            case 'collect_text':
                $ctx['description'] = $request->input('message');
                $nextKey = $node->next_map_json['default'] ?? null;

                $session->node_code = $nextKey;
                $session->context_json = $ctx;
                $session->last_message_at = $now;
                $session->save();

                $next = WaNode::where('flow_id', $flow->id)
                    ->where('code', $nextKey)
                    ->first();

                return $this->renderNode($from, $next, $user, $session);

            case 'dispatch':
                $this->dispatchToPlumbers($user, $ctx);
                $nextKey = $node->next_map_json['default'] ?? null;

                if ($nextKey) {
                    $session->node_code = $nextKey;
                    $session->last_message_at = $now;
                    $session->save();

                    $next = WaNode::where('flow_id', $flow->id)
                        ->where('code', $nextKey)
                        ->first();

                    return $this->renderNode($from, $next, $user, $session);
                }
                return $this->replyText($from, $node->body ?? 'Request sent.');

            default:
                $nextKey = $node->next_map_json['default'] ?? null;
                if ($nextKey) {
                    $session->node_code = $nextKey;
                    $session->last_message_at = $now;
                    $session->save();

                    $next = WaNode::where('flow_id', $flow->id)
                        ->where('code', $nextKey)
                        ->first();

                    return $this->renderNode($from, $next, $user, $session);
                }
                return $this->replyText($from, $node->body ?? 'OK.');
        }
    }

    private function renderNode($to, WaNode $node, $user, WaSession $session)
    {
        $tokens = [
            '{{first_name}}' => (
                $user?->full_name
                    ? explode(' ', $user->full_name)[0]
                    : ($user?->name ?: 'there')
            ),
            '{{postal_code}}' => $user->postal_code ?? '',
            '{{city}}'        => $user->city ?? '',
            '{{status}}'      => $session->context_json['status'] ?? '',
        ];

        $title  = $node->title  ? strtr($node->title,  $tokens) : null;
        $body   = $node->body   ? strtr($node->body,   $tokens) : null;
        $footer = $node->footer ? strtr($node->footer, $tokens) : null;

        $payload = [
            'type'   => $node->type,
            'title'  => $title,
            'body'   => $body,
            'footer' => $footer
        ];

        if (in_array($node->type, ['buttons', 'list'])) {
            $payload['options'] = $node->options_json ?? [];
        }

        WaLog::create([
            'wa_number'    => $to,
            'direction'    => 'out',
            'payload_json' => $payload,
            'status'       => 'queued'
        ]);

        return response()->json(['reply' => $payload]);
    }

    private function repeatNode($to, WaNode $node)
    {
        return response()->json(['reply' => [
            'type'    => $node->type,
            'title'   => $node->title,
            'body'    => $node->body,
            'footer'  => $node->footer,
            'options' => $node->options_json ?? [],
        ]]);
    }

    private function replyText($to, $text)
    {
        WaLog::create([
            'wa_number'    => $to,
            'direction'    => 'out',
            'payload_json' => ['type' => 'text', 'body' => $text],
            'status'       => 'queued'
        ]);
        return response()->json(['reply' => ['type' => 'text', 'body' => $text]]);
    }

    private function dispatchToPlumbers($user, array $ctx)
    {
        if (!$user) return;

        $catId = null;
        if (!empty($ctx['category_row']) && str_starts_with($ctx['category_row'], 'cat:')) {
            $catId = (int) str_replace('cat:', '', $ctx['category_row']);
        }

        $hg = DB::table('postal_codes')
            ->where('Postcode', $user->postal_code)
            ->value('Hoofdgemeente');

        if (!$hg) return;

        $plumberIds = DB::table('plumber_coverages')
            ->where('hoofdgemeente', $hg)
            ->pluck('plumber_id');

        if ($catId) {
            $plumberIds = DB::table('category_user')
                ->whereIn('user_id', $plumberIds)
                ->where('category_id', $catId)
                ->pluck('user_id');
        }

        $plumbers = User::whereIn('id', $plumberIds)
            ->where('role', 'plumber')
            ->where('status', 'available')
            ->get(['id', 'full_name', 'whatsapp_number']);

        $service = $catId ? Category::find($catId)?->label : 'Selected service';
        $desc = $ctx['description'] ?? '';
        $summary =
            "{$user->full_name}\n{$user->postal_code} {$user->city}\n\n".
            "Service: {$service}\nMessage: {$desc}\n\n".
            "Are you interested? Reply YES or NO.";

        foreach ($plumbers as $p) {
            try {
                \Http::post(config('services.whatsapp.bot_url', 'http://127.0.0.1:3000').'/send-message', [
                    'number'  => $p->whatsapp_number,
                    'message' => $summary,
                ]);
            } catch (\Throwable $e) {}
        }
    }
}
