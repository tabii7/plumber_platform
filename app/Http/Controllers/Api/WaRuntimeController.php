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
            // Handle unregistered users
            $unregisteredFlow = WaFlow::where('code', 'unregistered_flow')
                ->where('is_active', true)
                ->first();
            
            if ($unregisteredFlow) {
                $node = $unregisteredFlow->nodes()->orderBy('sort')->first();
                return $this->renderNode($from, $node, null, null);
            }
            
            return $this->replyText($from, "This WhatsApp number is not registered. Please create your account at loodgieter.app");
        }

        $role = $user->role ?? 'any';

        $session = WaSession::where('wa_number', $from)->first();

        // Log for debugging
        \Log::info("WhatsApp incoming", [
            'from' => $from,
            'text' => $text,
            'user_role' => $role,
            'has_session' => $session ? true : false,
            'session_flow' => $session?->flow_code,
            'session_node' => $session?->node_code
        ]);

        /**
         * 1. No session yet → automatically start flow based on user role
         */
        if (!$session) {
            // Select flow by role (any message triggers the appropriate flow)
            $flowQuery = WaFlow::where('is_active', true)
                ->where('entry_keyword', 'any');

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

                // Handle category selections for client flow
                if ($flow->code === 'client_flow' && str_starts_with($text, 'cat:')) {
                    $ctx['category_row'] = $text;
                }

                if ($flow->code === 'plumber_flow' && $user) {
                    // Handle plumber status updates
                    $statusMap = [
                        '1' => 'available',
                        '2' => 'busy', 
                        '3' => 'holiday',
                        'available' => 'available',
                        'busy' => 'busy',
                        'holiday' => 'holiday'
                    ];
                    
                    if (isset($statusMap[$text])) {
                        $user->status = $statusMap[$text];
                        $user->save();
                        $ctx['status'] = ucfirst($statusMap[$text]);
                    }
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
                
                // Only accept valid category selections
                if (str_starts_with($text, 'cat:')) {
                    $ctx['category_row'] = $text;
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
                } else {
                    // Invalid selection - repeat the list or go to goodbye
                    return $this->repeatNode($from, $node);
                }

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

            case 'text':
                // Handle service selection for client flow
                if ($flow->code === 'client_flow' && $node->code === 'service_selection') {
                    $serviceNumber = (int) $text;
                    if ($serviceNumber >= 1 && $serviceNumber <= 13) {
                        $ctx['category_row'] = 'cat:' . $serviceNumber;
                        $nextKey = $node->next_map_json['default'] ?? null;
                        
                        $session->node_code = $nextKey;
                        $session->context_json = $ctx;
                        $session->last_message_at = $now;
                        $session->save();

                        $next = WaNode::where('flow_id', $flow->id)
                            ->where('code', $nextKey)
                            ->first();

                        return $this->renderNode($from, $next, $user, $session);
                    } else {
                        return $this->replyText($from, "Please enter a number between 1 and 13 to select a service.");
                    }
                }
                
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
                
                // If no next node, conversation is finished - delete session
                \Log::info("Ending session", ['wa_number' => $from, 'node' => $node->code]);
                $session->delete();
                return $this->replyText($from, $node->body ?? 'OK.');

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
                
                // If no next node, conversation is finished - delete session
                \Log::info("Ending session", ['wa_number' => $from, 'node' => $node->code]);
                $session->delete();
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
            '{{postal_code}}' => $user?->postal_code ?? '',
            '{{city}}'        => $user?->city ?? '',
            '{{status}}'      => $session?->context_json['status'] ?? '',
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
        
        // For list messages, also include a flattened options array for WhatsApp compatibility
        if ($node->type === 'list') {
            $flattenedOptions = [];
            foreach ($node->options_json ?? [] as $section) {
                if (isset($section['rows'])) {
                    foreach ($section['rows'] as $row) {
                        $flattenedOptions[] = [
                            'id' => $row['id'],
                            'title' => $row['title']
                        ];
                    }
                }
            }
            $payload['flattened_options'] = $flattenedOptions;
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
        // Get user data for token replacement
        $user = User::where('whatsapp_number', $to)
            ->orWhere('phone', $to)
            ->first();
            
        $session = WaSession::where('wa_number', $to)->first();
        
        $tokens = [
            '{{first_name}}' => (
                $user?->full_name
                    ? explode(' ', $user->full_name)[0]
                    : ($user?->name ?: 'there')
            ),
            '{{postal_code}}' => $user?->postal_code ?? '',
            '{{city}}'        => $user?->city ?? '',
            '{{status}}'      => $session?->context_json['status'] ?? '',
        ];

        $title  = $node->title  ? strtr($node->title,  $tokens) : null;
        $body   = $node->body   ? strtr($node->body,   $tokens) : null;
        $footer = $node->footer ? strtr($node->footer, $tokens) : null;

        return response()->json(['reply' => [
            'type'    => $node->type,
            'title'   => $title,
            'body'    => $body,
            'footer'  => $footer,
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
            "{$user->full_name}\n{$user->address} {$user->number}\n{$user->postal_code} {$user->city}\n\n".
            "Required: {$service} (number {$catId})\n\n".
            "Message: {$desc}\n\n".
            "Are you interested in this client? Reply YES or NO.";

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
