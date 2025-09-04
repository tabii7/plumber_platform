<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\{User, WaLog, WaSession, WaRequest, WaOffer, WaFlow, WaNode};
use App\Jobs\SendRatingReminder;

class WaRuntimeController extends Controller
{
    public function incoming(Request $request)
    {
        $from         = preg_replace('/\D+/', '', (string) $request->input('from'));
        $originalText = trim((string) $request->input('message'));
        $text         = strtolower($originalText);
        $now          = now();

        // Log incoming
        WaLog::create([
            'wa_number'    => $from,
            'direction'    => 'in',
            'payload_json' => $request->all(),
            'status'       => 'recv'
        ]);

        // Find user
        $user = User::where('whatsapp_number', $from)->first();
        if (!$user) {
            return $this->handleDynamicFlow($from, $text, null, 'unregistered_flow', 'register_prompt', null, $originalText);
        }

        // Check subscription for clients
        if ($user->role === 'client' && !$this->checkClientSubscription($user)) {
            return $this->showSubscriptionPrompt($from, $user);
        }

        // Get/create session (auto only for clients)
        $session = WaSession::where('wa_number', $from)->first();
        if (!$session && $user->role === 'client') {
            $session = WaSession::create([
                'wa_number'    => $from,
                'user_id'      => $user->id,
                'flow_code'    => 'client_flow',
                'node_code'    => 'C0',
                'context_json' => [
                    'user_first_name'   => explode(' ', $user->full_name)[0],
                    'user_address'      => $user->address,
                    'user_postal_code'  => $user->postal_code,
                    'user_city'         => $user->city,
                ],
                'last_message_at' => $now,
            ]);
        }

        // Commands
        if ($text === 'menu' || $text === 'help') {
            return $this->showMenu($from, $user, $session);
        }

        if ($text === 'exit' || $text === '6') {
            if ($session) $session->delete();
            return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
        }

        if ($text === 'rate' && $user->role === 'client') {
            return $this->handleRatingRequest($from, $user, $session);
        }

        if ($text === 'complete' && $user->role === 'plumber') {
            return $this->markJobCompleted($from, $user, $session);
        }

        if ($text === 'current_request' && $user->role === 'plumber') {
            return $this->showPlumberCurrentRequest($from, $user, $session);
        }

        // Start command
        if ($text === 'start' || $originalText === 'Start') {
            if ($user->role === 'client') {
                $activeRequest = WaRequest::where('customer_id', $user->id)
                    ->whereIn('status', ['broadcasting', 'active', 'in_progress'])
                    ->first();

                if ($activeRequest) {
                    $msg = "You already have an active request (ID: {$activeRequest->id}).\n\n";
                    if ($activeRequest->status === 'broadcasting') {
                        $msg .= "Your request is currently being sent to available plumbers.\n";
                        $msg .= "Type 'offers' to check for responses from plumbers.";
                    } else {
                        $msg .= "A plumber has been selected for your job.\n";
                        $msg .= "Type 'status' to check the current status.";
                    }
                    return $this->replyText($from, $msg);
                }
            }

            if ($session) $session->delete();

            if ($user->role === 'client') {
                $session = WaSession::create([
                    'wa_number'    => $from,
                    'user_id'      => $user->id,
                    'flow_code'    => 'client_flow',
                    'node_code'    => 'C0',
                    'context_json' => [
                        'user_first_name'   => explode(' ', $user->full_name)[0],
                        'user_address'      => $user->address,
                        'user_postal_code'  => $user->postal_code,
                        'user_city'         => $user->city,
                    ],
                    'last_message_at' => $now,
                ]);
            } else {
                return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
            }
        }

        if ($text === 'offers' && $user->role === 'client') {
            return $this->showOffersList($from, $user, $session);
        }

        if ($text === 'status' && $user->role === 'client') {
            return $this->showRequestStatus($from, $user, $session);
        }

        // No session?
        if (!$session) {
            if ($user->role === 'plumber') {
                return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
            }
            return $this->replyText($from, "Session error. Please try again.");
        }

        $currentFlowCode = $session->flow_code ?? ($user->role === 'client' ? 'client_flow' : 'plumber_flow');

        // Menu numeric selections (from list fallback)
        if (is_numeric($text) && $session->node_code === 'menu') {
            switch ($text) {
                case '1':
                    if ($user->role === 'client') return $this->handleStartCommand($from, $user, $session);
                    return $this->setAvailability($from, $user, $session, true);
                case '2':
                    if ($user->role === 'client') return $this->showOffersList($from, $user, $session);
                    return $this->setAvailability($from, $user, $session, false);
                case '3':
                    if ($user->role === 'client') return $this->handleRatingRequest($from, $user, $session);
                    return $this->markJobCompleted($from, $user, $session);
                case '4':
                    if ($user->role === 'client') return $this->showRequestStatus($from, $user, $session);
                    return $this->showPlumberCurrentRequest($from, $user, $session);
                case '5':
                    return $this->showSupportMessage($from, $user, $session);
                case '6':
                    if ($session) $session->delete();
                    return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
                default:
                    return $this->showMenu($from, $user, $session);
            }
        }

        return $this->handleDynamicFlow($from, $text, $user, $currentFlowCode, $session->node_code, $session, $originalText);
    }

    /* =========================
     * FLOW ENGINE
     * ========================= */

    private function handleDynamicFlow($from, $text, $user, $flowCode, $nodeCode, $session = null, $originalText = null)
    {
        $flow = WaFlow::where('code', $flowCode)->where('is_active', true)->first();
        if (!$flow) return $this->replyText($from, "Flow not found or inactive.");

        $node = WaNode::where('flow_id', $flow->id)->where('code', $nodeCode)->first();
        if (!$node) return $this->replyText($from, "Node not found.");

        $ctx = $session ? ($session->context_json ?? []) : [];

        switch ($nodeCode) {
            case 'C4': // consent to broadcast
                if ($text === 'yes' || $text === '1') {
                    $this->createAndBroadcastRequest($user, $ctx);
                    $nextNode = $this->getNextNode($node, 'yes') ?: $node;
                    if ($session && $nextNode) {
                        $session->node_code    = $nextNode->code;
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->replyText($from, "Got it. I'm notifying nearby plumbers now. You'll receive options as they accept.\nYou can reply help for commands.");
                }
                if ($text === 'no' || $text === '2') {
                    if ($session) $session->delete();
                    return $this->replyText($from, "Request canceled. Type start to try again.");
                }
                break;

            case 'C5': // waiting/offers
                if ($text === 'help') return $this->showMenu($from, $user, $session);
                return $this->showOffersList($from, $user, $session);

            case 'C6': // offers list
                if (is_numeric($text)) {
                    $offerNumber = (int) $text;
                    return $this->showOfferDetails($from, $user, $session, $offerNumber);
                }
                return $this->showOffersList($from, $user, $session);

            case 'C7': // offer details confirm
                if ($text === 'yes' || $text === '1') {
                    $selectedOfferId = $ctx['selected_offer_id'] ?? null;
                    if ($selectedOfferId) {
                        $this->selectPlumber($user, $selectedOfferId);
                        if ($session) {
                            $session->node_code    = 'C8';
                            $session->context_json = $ctx;
                            $session->save();
                        }
                        return $this->replyText($from, "Great! You selected the plumber. Other plumbers have been notified.");
                    }
                } elseif ($text === 'no' || $text === '2' || $text === '3' || $text === 'choose_again') {
                    if ($session) {
                        $session->node_code    = 'C6';
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->showOffersList($from, $user, $session);
                }
                break;

            case 'P0': // plumber new job broadcast
                if ($text === 'yes' || $text === '1') {
                    $nextNode = $this->getNextNode($node, 'yes');
                    if ($session && $nextNode) {
                        $session->node_code    = $nextNode->code;
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->sendNodeResponse($from, $nextNode, $ctx);
                }
                if ($text === 'no' || $text === '2') {
                    if ($session) $session->delete();
                    return $this->replyText($from, "No problem. We'll keep sending you nearby requests.");
                }
                break;

            case 'P1': // plumber message -> create offer
                $ctx['personal_message'] = $originalText;
                if ($session) {
                    $existing = $session->context_json ?? [];
                    $session->context_json = array_merge($existing, $ctx);
                    $session->save();
                }
                $this->createOffer($user, $ctx);
                return $this->replyText($from, "Thanks! Your offer has been sent to the client. You'll be notified if they choose you.");

            case 'R1':
                return $this->processRating($from, $user, $session, $text);
        }

        return $this->processDynamicNode($from, $text, $user, $node, $ctx, $session, $originalText);
    }

    private function processDynamicNode($from, $text, $user, $node, $ctx, $session, $originalText = null)
    {
        $options = $node->options_json ?? [];
        $nextMap = $node->next_map_json ?? [];

        // Match option
        $matched = null;
        foreach ($options as $opt) {
            if (isset($opt['id']) && $opt['id'] === $text) { $matched = $opt; break; }
        }
        if (!$matched && is_numeric($text)) {
            $idx = (int)$text - 1;
            if (isset($options[$idx])) $matched = $options[$idx];
        }
        if (!$matched) {
            foreach ($options as $opt) {
                if (isset($opt['text'])) {
                    $ot = strtolower($opt['text']);
                    $ut = strtolower($text);
                    if (stripos($ot, $ut) !== false || stripos($ut, $ot) !== false) { $matched = $opt; break; }
                }
            }
        }

        // Next node
        $nextNode = null;
        if ($matched && isset($nextMap[$matched['id']])) {
            $nextNodeCode = $nextMap[$matched['id']];
            $nextNode = WaNode::where('flow_id', $node->flow_id)->where('code', $nextNodeCode)->first();
        } elseif (isset($nextMap['default'])) {
            $nextNodeCode = $nextMap['default'];
            $nextNode = WaNode::where('flow_id', $node->flow_id)->where('code', $nextNodeCode)->first();
        }

        // Update context
        if ($matched) {
            $ctx['last_input']  = $text;
            $ctx['last_option'] = $matched;

            if ($node->code === 'C1') {
                $ctx['problem']       = $matched['id'];
                $ctx['problem_label'] = $matched['text'];
            } elseif ($node->code === 'C2') {
                $ctx['urgency']       = $matched['id'];
                $ctx['urgency_label'] = $matched['text'];
            }
        } elseif ($node->type === 'collect_text') {
            $ctx['description'] = $originalText;
            $ctx['last_input']  = $originalText;
        }

        if ($session && $nextNode) {
            $existing = $session->context_json ?? [];
            $session->node_code    = $nextNode->code;
            $session->context_json = array_merge($existing, $ctx);
            $session->save();
        }

        return $this->sendNodeResponse($from, $nextNode ?: $node, $ctx);
    }

    private function sendNodeResponse($from, $node, $ctx)
    {
        $body = $this->replaceVariables($node->body, $ctx);

        switch ($node->type) {
            case 'buttons':
                $options = $node->options_json ?? [];
                $formatted = [];
                foreach ($options as $opt) {
                    $formatted[] = ['id' => $opt['id'], 'text' => $opt['text']];
                }
                return $this->sendButtons($from, ['body' => $body, 'options' => $formatted, 'footer' => $node->footer ?? null]);

            case 'list':
                $options = $node->options_json ?? [];
                return $this->sendList($from, ['title' => $node->title, 'body' => $body, 'options' => $options]);

            case 'collect_text':
            case 'text':
            default:
                return $this->replyText($from, $body);
        }
    }

    /* =========================
     * OUTBOUND HELPERS
     * ========================= */

    private function formatButtonsAsText(array $payload): string
    {
        $lines = [];
        $lines[] = $payload['body'] ?? '';
        $options = $payload['options'] ?? [];
        if (!empty($options)) {
            $lines[] = '';
            foreach ($options as $i => $opt) {
                $n = $i + 1;
                $lines[] = "{$n}) " . ($opt['text'] ?? ("Option {$n}"));
            }
        }
        return implode("\n", $lines);
    }

    private function buildP0Text(array $ctx): string
    {
        $problem  = $this->replaceVariables('{{problem}}', $ctx);
        $urgency  = $this->replaceVariables('{{urgency_label}}', $ctx);
        $desc     = $this->replaceVariables('{{description}}', $ctx);
        $body  = "🆕 New job near you!\n\n";
        $body .= "Customer: " . ($ctx['customer_name'] ?? 'Customer') . "\n";
        $body .= "Area: " . ($ctx['postal_code'] ?? '') . " " . ($ctx['city'] ?? '') . "\n";
        $body .= "Problem: {$problem}\n";
        $body .= "Urgency: {$urgency}\n";
        $body .= "Description: {$desc}\n\n";
        $body .= "Distance: " . ($ctx['distance_km'] ?? '5') . " km • ETA: " . ($ctx['eta_min'] ?? '20') . " min\n\n";
        $body .= "Reply 1 to send offer, or 2 to skip.";
        return $body;
    }

    private function replaceVariables($text, $ctx)
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($m) use ($ctx) {
            $name = $m[1];
            switch ($name) {
                case 'first_name':   return $ctx['user_first_name'] ?? 'User';
                case 'customer_name':return $ctx['customer_name'] ?? 'Customer';
                case 'address':
                    $addr = $ctx['address'] ?? $ctx['user_address'] ?? '';
                    return $addr !== '' ? $addr : 'Address not provided';
                case 'postal_code':  return $ctx['postal_code'] ?? $ctx['user_postal_code'] ?? '';
                case 'city':         return $ctx['city'] ?? $ctx['user_city'] ?? 'Unknown city';
                case 'plumber_name': return $ctx['plumber_name'] ?? 'the plumber';
                case 'problem':
                    $map = [
                        'leak' => 'Leak',
                        'blockage' => 'Blockage / Drain',
                        'heating' => 'Heating / Boiler',
                        'installation' => 'Installation / Replacement',
                        'other' => 'Other'
                    ];
                    $id = $ctx['problem'] ?? '';
                    return $map[$id] ?? 'Unknown problem';
                case 'urgency_label':
                    $map = [
                        'high' => 'High — max 60 min',
                        'normal' => 'Normal — max 2 hours',
                        'later' => 'Later today / schedule'
                    ];
                    $id = $ctx['urgency'] ?? '';
                    return $map[$id] ?? 'Normal';
                case 'description':  return $ctx['description'] ?? 'No description provided';
                case 'distance_km':  return $ctx['distance_km'] ?? '5';
                case 'eta_min':      return $ctx['eta_min'] ?? '20';
                default:             return $ctx[$name] ?? $m[0];
            }
        }, $text);
    }

    private function getNode($flowCode, $nodeCode)
    {
        $flow = WaFlow::where('code', $flowCode)->where('is_active', true)->first();
        if (!$flow) return null;

        return WaNode::where('flow_id', $flow->id)->where('code', $nodeCode)->first();
    }

    private function getNextNode($currentNode, $input)
    {
        $map = $currentNode->next_map_json ?? [];
        if (isset($map[$input])) {
            $next = $map[$input];
            return WaNode::where('flow_id', $currentNode->flow_id)->where('code', $next)->first();
        }
        return null;
    }

    /* =========================
     * MENUS / COMMANDS
     * ========================= */

    private function showMenu($from, $user, $session)
    {
        if (!$session && $user->role === 'plumber') {
            $session = WaSession::create([
                'wa_number'    => $from,
                'user_id'      => $user->id,
                'flow_code'    => 'plumber_flow',
                'node_code'    => 'menu',
                'context_json' => [
                    'user_first_name'   => explode(' ', $user->full_name)[0],
                    'user_address'      => $user->address,
                    'user_postal_code'  => $user->postal_code,
                    'user_city'         => $user->city,
                ],
                'last_message_at' => now(),
            ]);
        }

        if ($session) {
            $session->node_code = 'menu';
            $session->save();
        }

        if ($user->role === 'client') {
            // Titles WITHOUT numbers; bot adds 1,2,3...
            return $this->sendList($from, [
                'title'  => 'Main menu',
                'body'   => 'Choose an option:',
                'options'=> [[
                    'title' => 'Customer Options',
                    'rows'  => [
                        ['id' => 'start',  'title' => 'Start new request'],
                        ['id' => 'offers', 'title' => 'View offers'],
                        ['id' => 'rate',   'title' => 'Rate completed job'],
                        ['id' => 'status', 'title' => 'View status of current request'],
                        ['id' => 'support','title' => 'Contact support'],
                        ['id' => 'exit',   'title' => 'Exit this menu'],
                    ],
                ]],
            ]);
        } else {
            return $this->sendList($from, [
                'title'  => 'Plumber menu',
                'body'   => 'Choose an option:',
                'options'=> [[
                    'title' => 'Plumber Options',
                    'rows'  => [
                        ['id' => 'available_on',  'title' => 'Set availability ON'],
                        ['id' => 'available_off', 'title' => 'Set availability OFF'],
                        ['id' => 'complete',      'title' => 'Mark job as completed'],
                        ['id' => 'current_request','title'=> 'Current request'],
                        ['id' => 'support',       'title' => 'Contact support'],
                        ['id' => 'exit',          'title' => 'Exit this menu'],
                    ],
                ]],
            ]);
        }
    }

    private function createAndBroadcastRequest($user, $ctx)
    {
        $existing = WaRequest::where('customer_id', $user->id)
            ->whereIn('status', ['broadcasting', 'active', 'in_progress'])
            ->first();
        if ($existing) return;

        // Create request
        $request = WaRequest::create([
            'customer_id' => $user->id,
            'problem'     => $ctx['problem'] ?? null,
            'urgency'     => $ctx['urgency'] ?? null,
            'description' => $ctx['description'] ?? null,
            'status'      => 'broadcasting',
        ]);

        $ctx['request_id'] = $request->id;

        // Available plumbers (not on active job)
        $plumbers = User::where('role', 'plumber')
            ->where(function($q){ $q->where('status','available')->orWhere('status','Available'); })
            ->where(function($q){ $q->where('subscription_status','active')->orWhere('subscription_status','Active')->orWhereNull('subscription_status'); })
            ->whereNotExists(function($q){
                $q->select(DB::raw(1))->from('wa_requests')
                    ->whereRaw('wa_requests.selected_plumber_id = users.id')
                    ->whereIn('wa_requests.status', ['active', 'in_progress']);
            })
            ->get();

        foreach ($plumbers as $plumber) {
            $this->sendJobBroadcast($plumber, $user, $ctx, $request);
        }
    }

    private function sendJobBroadcast($plumber, $customer, $ctx, $request)
    {
        $activeJob = WaRequest::where('selected_plumber_id', $plumber->id)
            ->whereIn('status', ['active', 'in_progress'])
            ->first();
        if ($activeJob) return;

        // Reset plumber session
        WaSession::where('wa_number', $plumber->whatsapp_number)->delete();

        // Build plumber session at P0
        $urgencyLabel = $this->replaceVariables('{{urgency_label}}', ['urgency' => $ctx['urgency'] ?? 'normal']);
        WaSession::create([
            'wa_number' => $plumber->whatsapp_number,
            'user_id'   => $plumber->id,
            'flow_code' => 'plumber_flow',
            'node_code' => 'P0',
            'context_json' => [
                'request_id'   => $request->id,
                'customer_id'  => $customer->id,
                'customer_name'=> explode(' ', $customer->full_name)[0],
                'address'      => $customer->address,
                'postal_code'  => $customer->postal_code,
                'city'         => $customer->city,
                'problem'      => $ctx['problem'] ?? null,
                'urgency'      => $ctx['urgency'] ?? 'normal',
                'urgency_label'=> $urgencyLabel,
                'description'  => $ctx['description'] ?? null,
                'distance_km'  => '5',
                'eta_min'      => '20',
            ],
            'last_message_at' => now(),
        ]);

        // Proactive TEXT message to plumber
        $text = $this->buildP0Text([
            'customer_name'=> explode(' ', $customer->full_name)[0],
            'postal_code'  => $customer->postal_code,
            'city'         => $customer->city,
            'problem'      => $ctx['problem'] ?? null,
            'urgency'      => $ctx['urgency'] ?? 'normal',
            'description'  => $ctx['description'] ?? null,
            'distance_km'  => '5',
            'eta_min'      => '20',
        ]);
        $this->waSend($plumber->whatsapp_number, $text);
    }

    private function createOffer($plumber, $ctx)
    {
        // Find request id
        $requestId = $ctx['request_id'] ?? null;
        if (!$requestId) {
            $sess = WaSession::where('wa_number', $plumber->whatsapp_number)->first();
            if ($sess && isset($sess->context_json['request_id'])) $requestId = $sess->context_json['request_id'];
        }
        if (!$requestId) return;

        // Upsert (per plumber per request)
        $offer = WaOffer::updateOrCreate(
            ['plumber_id' => $plumber->id, 'request_id' => $requestId],
            [
                'personal_message' => $ctx['personal_message'] ?? null,
                'status'           => 'pending',
                'eta_minutes'      => 20,
                'distance_km'      => 5.0,
                'rating'           => 4.5,
            ]
        );

        // Notify customer
        $request = WaRequest::find($requestId);
        if ($request) {
            $customer = User::find($request->customer_id);
            if ($customer) {
                $msg  = "🎉 New plumber offer received!\n\n";
                $msg .= "Plumber: {$plumber->full_name}\n";
                if (!empty($ctx['personal_message'])) $msg .= "Message: \"{$ctx['personal_message']}\"\n";
                $msg .= "ETA: 20 min 🚗\n\n";
                $msg .= "Type 'offers' to view all offers or wait for more.";
                $this->waSend($customer->whatsapp_number, $msg);
            }
        }
    }

    private function showOffersList($from, $user, $session)
    {
        $offers = WaOffer::whereHas('request', fn($q)=>$q->where('customer_id',$user->id))
            ->with('plumber')->latest()->get()->unique('plumber_id')->values();

        if ($offers->isEmpty()) {
            return $this->replyText($from, "Waiting for plumbers to accept your job...\nYou can reply help for commands.");
        }

        $message = "Plumbers who accepted your job (choose a number to view details):\n\n";
        foreach ($offers as $i => $offer) {
            $pl = $offer->plumber;
            $message .= ($i+1).") {$pl->full_name} • ⭐ 4.5 • 20 min 🚗\n";
        }
        $message .= "\nType the number to see details, or wait for more options.";

        if ($session) {
            $session->node_code = 'C6';
            $session->context_json = array_merge($session->context_json ?? [], [
                'offer_ids' => $offers->pluck('id')->toArray()
            ]);
            $session->save();
        }

        return $this->replyText($from, $message);
    }

    private function showOfferDetails($from, $user, $session, $offerNumber)
    {
        $ids = $session->context_json['offer_ids'] ?? [];
        if ($offerNumber <= 0 || $offerNumber > count($ids)) {
            return $this->showOffersList($from, $user, $session);
        }

        $offer = WaOffer::with('plumber')->find($ids[$offerNumber - 1]);
        if (!$offer) return $this->showOffersList($from, $user, $session);

        $plumber = $offer->plumber;

        $message  = "Do you want to select this plumber?\n";
        $message .= "Name: {$plumber->full_name}\n";
        $message .= "From: {$plumber->city} • ETA: 20 min 🚗 • Distance: 5 km\n";
        $message .= "Phone: +{$plumber->whatsapp_number}\n";
        $message .= "Rating: ⭐ 4.5\n";
        if (!empty($offer->personal_message)) $message .= "Message to you: \"{$offer->personal_message}\"";

        if ($session) {
            $ctx = $session->context_json ?? [];
            $ctx['selected_offer_id'] = $offer->id;
            $session->node_code = 'C7';
            $session->context_json = $ctx;
            $session->save();
        }

        return $this->sendButtons($from, [
            'body' => $message,
            'options' => [
                ['id' => 'yes',          'text' => 'Yes'],
                ['id' => 'no',           'text' => 'No'],
                ['id' => 'choose_again', 'text' => 'Choose again'],
            ]
        ]);
    }

    private function selectPlumber($customer, $offerId)
    {
        $offer = WaOffer::with(['plumber','request'])->find($offerId);
        if (!$offer) return;

        // Update statuses
        $offer->update(['status' => 'selected']);
        $offer->request->update([
            'status' => 'active',
            'selected_plumber_id' => $offer->plumber_id
        ]);

        // Clear all plumber sessions for this request
        $allOffers = WaOffer::where('request_id', $offer->request_id)->with('plumber')->get();
        foreach ($allOffers as $o) {
            WaSession::where('wa_number', $o->plumber->whatsapp_number)->delete();
        }

        // Notify selected plumber (with client phone)
        $cust = $customer; // alias
        $req  = $offer->request;

        $plumberMsg  = "✅ You were selected by " . explode(' ', $cust->full_name)[0] . ".\n";
        $plumberMsg .= "Phone: +{$cust->whatsapp_number}\n";
        $plumberMsg .= "Address: {$cust->address}, {$cust->postal_code} {$cust->city}\n";
        $plumberMsg .= "Problem: " . $this->getProblemLabel($req->problem) . "\n";
        $plumberMsg .= "Description: \"{$req->description}\"\n";
        $plumberMsg .= "Urgency: " . $this->getUrgencyLabel($req->urgency) . "\n\n";
        $plumberMsg .= "Please proceed. Good luck!";
        $this->waSend($offer->plumber->whatsapp_number, $plumberMsg);

        // Notify customer (with plumber phone)
        $pl = $offer->plumber;
        $custMsg  = "✅ Plumber selected: {$pl->full_name}\n";
        $custMsg .= "Phone: +{$pl->whatsapp_number}\n";
        $custMsg .= "City: {$pl->city} • ETA: 20 min 🚗 • Distance: 5 km\n";
        $custMsg .= "They will contact you shortly.";
        $this->waSend($cust->whatsapp_number, $custMsg);

        // Notify other plumbers
        $others = WaOffer::where('request_id', $offer->request_id)->where('id','!=',$offerId)->with('plumber')->get();
        foreach ($others as $other) {
            $this->waSend($other->plumber->whatsapp_number, "❌ Another plumber was selected for this job. Thanks for responding — better luck next time!");
        }
    }

    private function showRequestStatus($from, $user, $session)
    {
        $request = WaRequest::where('customer_id',$user->id)
            ->whereIn('status', ['broadcasting','active','in_progress','completed'])
            ->latest()->first();

        if (!$request) return $this->replyText($from, "You don't have any active requests at the moment.\n\nType 'start' to create a new request.");

        $message  = "📋 Request Status\n\n";
        $message .= "Request ID: {$request->id}\n";
        $message .= "Status: " . ucfirst($request->status) . "\n";
        $message .= "Problem: " . $this->getProblemLabel($request->problem) . "\n";
        $message .= "Urgency: " . $this->getUrgencyLabel($request->urgency) . "\n";
        $message .= "Description: \"{$request->description}\"\n\n";

        switch ($request->status) {
            case 'broadcasting':
                $message .= "🔄 Your request is being sent to available plumbers.\nType 'offers' to check for responses from plumbers.";
                break;
            case 'active':
                if ($request->selected_plumber_id) {
                    $pl = User::find($request->selected_plumber_id);
                    $message .= "✅ Plumber selected: " . ($pl ? $pl->full_name : 'Unknown') . "\nThe plumber is on their way.";
                }
                break;
            case 'in_progress':
                $message .= "🛠️ Work is in progress.\n";
                break;
            case 'completed':
                $message .= "✅ Job completed!\nType 'rate' to rate your experience with the plumber.";
                break;
        }

        return $this->replyText($from, $message);
    }

    private function showPlumberCurrentRequest($from, $user, $session)
    {
        $request = WaRequest::where('selected_plumber_id',$user->id)
            ->whereIn('status',['active','in_progress'])->latest()->first();

        if (!$request) {
            if ($session) $session->delete();
            return $this->replyText($from, "You don't have any active jobs at the moment.\n\nYou'll receive notifications when new jobs are available in your area.");
        }

        $customer = User::find($request->customer_id);

        $message  = "🛠️ Current Job\n\n";
        $message .= "Request ID: {$request->id}\n";
        $message .= "Status: " . ucfirst($request->status) . "\n";
        $message .= "Customer: " . ($customer ? $customer->full_name : 'Unknown') . "\n";
        $message .= "Phone: +" . ($customer ? $customer->whatsapp_number : '-') . "\n";
        $message .= "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n";
        $message .= "Problem: " . $this->getProblemLabel($request->problem) . "\n";
        $message .= "Urgency: " . $this->getUrgencyLabel($request->urgency) . "\n";
        $message .= "Description: \"{$request->description}\"\n\n";
        $message .= $request->status === 'active'
            ? "Type 'complete' to mark this job as completed when you finish."
            : "Work is in progress. Type 'complete' when finished.";

        return $this->replyText($from, $message);
    }

    private function handleStartCommand($from, $user, $session)
    {
        $activeRequest = WaRequest::where('customer_id',$user->id)
            ->whereIn('status',['broadcasting','active','in_progress'])->first();

        if ($activeRequest) {
            $msg = "You already have an active request (ID: {$activeRequest->id}).\n\n";
            if ($activeRequest->status === 'broadcasting') {
                $msg .= "Your request is currently being sent to available plumbers.\nType 'offers' to check for responses from plumbers.";
            } else {
                $msg .= "A plumber has been selected for your job.\nType 'status' to check the current status.";
            }
            return $this->replyText($from, $msg);
        }

        if ($session) $session->delete();

        WaSession::create([
            'wa_number'    => $from,
            'user_id'      => $user->id,
            'flow_code'    => 'client_flow',
            'node_code'    => 'C0',
            'context_json' => [
                'user_first_name'   => explode(' ', $user->full_name)[0],
                'user_address'      => $user->address,
                'user_postal_code'  => $user->postal_code,
                'user_city'         => $user->city,
            ],
            'last_message_at' => now(),
        ]);

        return $this->replyText($from, "Starting new request... Please describe your problem.");
    }

    private function setAvailability($from, $user, $session, $available)
    {
        $user->update(['status' => $available ? 'available' : 'unavailable']);
        if ($session) $session->delete();

        return $this->replyText($from, $available
            ? "✅ You are now available to receive job requests.\n\nYou'll be notified when new jobs are available in your area."
            : "❌ You are now unavailable and won't receive job requests.\n\nType 'help' to change your status.");
    }

    private function showSupportMessage($from, $user, $session)
    {
        if ($session) $session->delete();

        $message  = "📞 Contact Support\n\n";
        $message .= "📧 Email: support@plumberplatform.com\n";
        $message .= "📱 Phone: +32 490 46 80 09\n";
        $message .= "🌐 Website: " . config('app.url') . "/support\n\n";
        $message .= "Our support team is available 24/7 to help you.";
        return $this->replyText($from, $message);
    }

    /* =========================
     * UTILITIES
     * ========================= */

    private function checkClientSubscription($user)
    {
        if ($user->subscription_status === 'active') {
            if ($user->subscription_ends_at && now()->gt($user->subscription_ends_at)) return false;
            return true;
        }
        return false;
    }

    private function showSubscriptionPrompt($from, $user)
    {
        $message  = "🔒 Subscription Required\n\n";
        $message .= "Hi " . explode(' ', $user->full_name)[0] . "!\n\n";
        $message .= "To use our plumber service, you need an active subscription.\n\n";
        $message .= "📋 Plans:\n";
        $message .= "• One-time request: €25\n• Monthly: €9.99\n• Yearly: €99\n\n";
        $message .= "🌐 " . config('app.url') . "/pricing\n\n";
        $message .= "After subscribing, you can start using our service immediately!";
        return $this->replyText($from, $message);
    }

    private function getProblemLabel($id){ return [
        'leak'=>'Leak','blockage'=>'Blockage / Drain','heating'=>'Heating / Boiler','installation'=>'Installation / Replacement','other'=>'Other'
    ][$id] ?? 'Unknown problem'; }

    private function getUrgencyLabel($id){ return [
        'high'=>'High — max 60 min','normal'=>'Normal — max 2 hours','later'=>'Later today / schedule'
    ][$id] ?? 'Normal'; }

    /* =========================
     * BOT BRIDGE (proactive)
     * ========================= */

    private function sendButtons($to, $payload)
    {
        WaLog::create([
            'wa_number'    => $to,
            'direction'    => 'out',
            'payload_json' => $payload,
            'status'       => 'queued'
        ]);

        return response()->json(['reply' => [
            'type'    => 'buttons',
            'body'    => $payload['body'],
            'footer'  => $payload['footer'] ?? null,
            'options' => $payload['options']
        ]]);
    }

    private function sendList($to, $payload)
    {
        WaLog::create([
            'wa_number'    => $to,
            'direction'    => 'out',
            'payload_json' => $payload,
            'status'       => 'queued'
        ]);

        return response()->json(['reply' => [
            'type'    => 'list',
            'title'   => $payload['title'],
            'body'    => $payload['body'],
            'options' => $payload['options']
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

    private function waSend(string $number, string $message): void
    {
        try {
            $botUrl = rtrim(config('services.wa_bot.url', 'http://127.0.0.1:3000'), '/');
            Http::post($botUrl . '/send-message', ['number'=>$number,'message'=>$message])->throw();
        } catch (\Throwable $e) {
            \Log::error('WA send failed', ['to'=>$number,'error'=>$e->getMessage()]);
        }
    }
}
