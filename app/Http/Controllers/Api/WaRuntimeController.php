<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\{User, WaLog, WaSession, WaRequest, WaOffer, WaFlow, WaNode};

class WaRuntimeController extends Controller
{
    public function incoming(Request $request)
    {
        $from = preg_replace('/\D+/', '', (string) $request->input('from'));
        $originalText = trim((string) $request->input('message'));
        $text = strtolower($originalText);
        $now = now();

        // Log incoming message
        WaLog::create([
            'wa_number'   => $from,
            'direction'   => 'in',
            'payload_json'=> $request->all(),
            'status'      => 'recv'
        ]);

        // Find user
        $user = User::where('whatsapp_number', $from)->first();

        if (!$user) {
            // Unregistered flow
            return $this->handleDynamicFlow($from, $text, null, 'unregistered_flow', 'register_prompt', null, $originalText);
        }

        // Check subscription status for clients
        if ($user->role === 'client') {
            $subscriptionValid = $this->checkClientSubscription($user);
            if (!$subscriptionValid) {
                return $this->showSubscriptionPrompt($from, $user);
            }
        }

        // Get or create session
        $session = WaSession::where('wa_number', $from)->first();

        if (!$session) {
            // Create only for clients by default
            if ($user->role === 'client') {
                $session = WaSession::create([
                    'wa_number'      => $from,
                    'user_id'        => $user->id,
                    'flow_code'      => 'client_flow',
                    'node_code'      => 'C0',
                    'context_json'   => [
                        'user_first_name'  => explode(' ', $user->full_name)[0],
                        'user_address'     => $user->address,
                        'user_postal_code' => $user->postal_code,
                        'user_city'        => $user->city,
                    ],
                    'last_message_at' => $now,
                ]);
            }
        }

        // Menu
        if ($text === 'menu' || $text === 'help') {
            return $this->showMenu($from, $user, $session);
        }

        // Exit command (text only; numeric exit is handled in menu switch)
        if ($text === 'exit') {
            if ($session) $session->delete();
            return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
        }

        // Client: rate
        if ($text === 'rate' && $user->role === 'client') {
            return $this->handleRatingRequest($from, $user, $session);
        }

        // Mark complete (both roles)
        if ($text === 'complete') {
            if ($user->role === 'plumber') {
                return $this->markJobCompleted($from, $user, $session);
            } else {
                return $this->markJobCompletedByClient($from, $user, $session);
            }
        }

        // Plumber: show current request
        if ($text === 'current_request' && $user->role === 'plumber') {
            return $this->showPlumberCurrentRequest($from, $user, $session);
        }

        // Start/new
        if ($text === 'start' || $originalText === 'Start') {
            if ($user->role === 'client') {
                $activeRequest = WaRequest::where('customer_id', $user->id)
                    ->whereIn('status', ['broadcasting', 'active', 'in_progress'])
                    ->first();

                if ($activeRequest) {
                    $message = "You already have an active request (ID: {$activeRequest->id}).\n\n";
                    if ($activeRequest->status === 'broadcasting') {
                        $message .= "Your request is currently being sent to available plumbers.\n";
                        $message .= "Type 'offers' to check for responses from plumbers.";
                    } else {
                        $message .= "A plumber has been selected for your job.\n";
                        $message .= "Type 'status' to check the current status.";
                    }
                    return $this->replyText($from, $message);
                }
            }

            if ($session) $session->delete();

            if ($user->role === 'client') {
                $session = WaSession::create([
                    'wa_number'      => $from,
                    'user_id'        => $user->id,
                    'flow_code'      => 'client_flow',
                    'node_code'      => 'C0',
                    'context_json'   => [
                        'user_first_name'  => explode(' ', $user->full_name)[0],
                        'user_address'     => $user->address,
                        'user_postal_code' => $user->postal_code,
                        'user_city'        => $user->city,
                    ],
                    'last_message_at' => $now,
                ]);
            } else {
                return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
            }
        }

        // Client: offers list
        if ($text === 'offers' && $user->role === 'client') {
            return $this->showOffersList($from, $user, $session);
        }

        // Client: status
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

        // If in menu + numeric selection
        if (is_numeric($text) && $session && $session->node_code === 'menu') {
            switch ($text) {
                case '1':
                    if ($user->role === 'client') {
                        return $this->handleStartCommand($from, $user, $session);
                    } else {
                        return $this->setAvailability($from, $user, $session, true);
                    }
                case '2':
                    if ($user->role === 'client') {
                        return $this->showOffersList($from, $user, $session);
                    } else {
                        return $this->setAvailability($from, $user, $session, false);
                    }
                case '3':
                    if ($user->role === 'client') {
                        return $this->markJobCompletedByClient($from, $user, $session);
                    } else {
                        return $this->markJobCompleted($from, $user, $session);
                    }
                case '4':
                    if ($user->role === 'client') {
                        return $this->handleRatingRequest($from, $user, $session);
                    } else {
                        return $this->showPlumberCurrentRequest($from, $user, $session);
                    }
                case '5':
                    if ($user->role === 'client') {
                        return $this->showRequestStatus($from, $user, $session);
                    } else {
                        return $this->showSupportMessage($from, $user, $session);
                    }
                case '6':
                    if ($user->role === 'client') {
                        return $this->showSupportMessage($from, $user, $session);
                    } else {
                        // plumber exit
                        if ($session) $session->delete();
                        return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
                    }
                case '7':
                    if ($user->role === 'client') {
                        if ($session) $session->delete();
                        return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
                    }
                    return $this->showMenu($from, $user, $session);
                default:
                    return $this->showMenu($from, $user, $session);
            }
        }

        return $this->handleDynamicFlow($from, $text, $user, $currentFlowCode, $session->node_code, $session, $originalText);
    }

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
                    $nextNode = $this->getNextNode($node, $text) ?: $this->getNode('client_flow', 'C5');
                    if ($session && $nextNode) {
                        $session->node_code = $nextNode->code;
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->replyText($from, "Got it. I'm notifying nearby plumbers now. You'll receive options as they accept.\nYou can reply help for commands.");
                } elseif ($text === 'no' || $text === '2') {
                    if ($session) $session->delete();
                    return $this->replyText($from, "Request canceled. Type start to try again.");
                }
                break;

            case 'C5': // waiting
                if ($text === 'help') return $this->showMenu($from, $user, $session);
                return $this->showOffersList($from, $user, $session);

            case 'C6': // offers list
                if (is_numeric($text)) {
                    $offerNumber = (int) $text;
                    return $this->showOfferDetails($from, $user, $session, $offerNumber);
                }
                return $this->showOffersList($from, $user, $session);

            case 'C7': // confirm select
                if ($text === 'yes' || $text === '1') {
                    $selectedOfferId = $ctx['selected_offer_id'] ?? null;
                    if ($selectedOfferId) {
                        $this->selectPlumber($user, $selectedOfferId);
                        if ($session) {
                            $session->node_code = 'C8';
                            $session->context_json = $ctx;
                            $session->save();
                        }
                        return $this->replyText($from, "Great! You selected the plumber.\nI'll share your full address and contact info now and notify other plumbers that the job is taken.");
                    }
                } elseif ($text === 'no' || $text === '2' || $text === 'choose_again' || $text === '3') {
                    if ($session) {
                        $session->node_code = 'C6';
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->showOffersList($from, $user, $session);
                }
                break;

            case 'P0': // plumber sees new job
                if ($text === 'yes' || $text === '1') {
                    $p1 = $this->getNode('plumber_flow', 'P1');
                    if ($session && $p1) {
                        $session->node_code = 'P1';
                        $session->save();
                        return $this->sendNodeResponse($from, $p1, $ctx);
                    }
                    \Log::error("P0 accept but P1 missing", ['from'=>$from]);
                    return $this->replyText($from, "Sorry, there was an issue processing your request. Please try again.");
                } elseif ($text === 'no' || $text === '2') {
                    if ($session) $session->delete();
                    return $this->replyText($from, "No problem. We'll keep sending you nearby requests.");
                }
                break;

            case 'P1': // plumber typed personal message (offer)
                $ctx['personal_message'] = $originalText ?? $text;
                if ($session) {
                    $existingContext = $session->context_json ?? [];
                    $session->context_json = array_merge($existingContext, $ctx);
                    $session->save();
                }
                $this->createOffer($user, $ctx); // create + notify client (and list)
                return $this->replyText($from, "Thanks! Your offer has been sent to the client. You'll be notified if they choose you.");

            case 'R1': // rating
                return $this->processRating($from, $user, $session, $text);
        }

        // Generic node processing
        return $this->processDynamicNode($from, $text, $user, $node, $ctx, $session, $originalText);
    }

    private function processDynamicNode($from, $text, $user, $node, $ctx, $session, $originalText = null)
    {
        $options = $node->options_json ?? [];
        $nextMap = $node->next_map_json ?? [];
        $matchedOption = null;

        // exact id
        foreach ($options as $option) {
            if (isset($option['id']) && $option['id'] === $text) {
                $matchedOption = $option; break;
            }
        }
        // numeric index
        if (!$matchedOption && is_numeric($text)) {
            $idx = (int)$text - 1;
            if (isset($options[$idx])) $matchedOption = $options[$idx];
        }
        // variations
        if (!$matchedOption) {
            foreach ($options as $option) {
                if (isset($option['variations']) && in_array($text, $option['variations'])) {
                    $matchedOption = $option; break;
                }
            }
        }
        // partial contains
        if (!$matchedOption) {
            foreach ($options as $option) {
                if (isset($option['text'])) {
                    $opt = strtolower($option['text']);
                    $usr = strtolower($text);
                    if (stripos($opt, $usr) !== false || stripos($usr, $opt) !== false) {
                        $matchedOption = $option; break;
                    }
                }
            }
        }

        // Next node
        $nextNode = null;
        if ($matchedOption && isset($nextMap[$matchedOption['id']])) {
            $nextNode = WaNode::where('flow_id', $node->flow_id)->where('code', $nextMap[$matchedOption['id']])->first();
        } elseif (isset($nextMap['default'])) {
            $nextNode = WaNode::where('flow_id', $node->flow_id)->where('code', $nextMap['default'])->first();
        }

        // Context updates
        if ($matchedOption) {
            $ctx['last_input'] = $text;
            $ctx['last_option'] = $matchedOption;
            if ($node->code === 'C1') {
                $ctx['problem'] = $matchedOption['id'];
                $ctx['problem_label'] = $matchedOption['text'];
            } elseif ($node->code === 'C2') {
                $ctx['urgency'] = $matchedOption['id'];
                $ctx['urgency_label'] = $matchedOption['text'];
            }
        } elseif ($node->type === 'collect_text') {
            $ctx['description'] = $originalText;
            $ctx['last_input'] = $originalText;
        }

        if ($session && $nextNode) {
            $session->node_code = $nextNode->code;
            $existingContext = $session->context_json ?? [];
            $session->context_json = array_merge($existingContext, $ctx);
            $session->save();
        }

        $targetNode = $nextNode ?: $node;
        if (!$targetNode) {
            \Log::error("Both nextNode and node are null", ['from' => $from]);
            return $this->replyText($from, "Sorry, there was an issue processing your request. Please try again.");
        }
        return $this->sendNodeResponse($from, $targetNode, $ctx);
    }

    private function sendNodeResponse($from, $node, $ctx)
    {
        if (!$node || !$node->body) {
            \Log::error("Node body null/empty", ['node_code'=>$node->code ?? 'unknown', 'from'=>$from]);
            return $this->replyText($from, "Sorry, there was an issue with the message. Please try again.");
        }

        $body = $this->replaceVariables($node->body, $ctx);

        switch ($node->type) {
            case 'buttons':
                $options = $node->options_json ?? [];
                $formattedOptions = [];
                foreach ($options as $option) {
                    $label = $option['text'] ?? 'Option';
                    $label = preg_replace('/^\s*\d+[\.\)]\s*/', '', $label);
                    $formattedOptions[] = ['id' => $option['id'], 'text' => $label];
                }
                return $this->sendButtons($from, [
                    'body'    => $body,
                    'options' => $formattedOptions
                ]);

            case 'list':
                $options = $node->options_json ?? [];
                return $this->sendList($from, [
                    'title'   => $node->title,
                    'body'    => $body,
                    'options' => $options
                ]);

            case 'collect_text':
            case 'text':
            default:
                return $this->replyText($from, $body);
        }
    }

    // ---------- proactive helpers ----------
    private function formatButtonsAsText(array $payload): string
    {
        $lines = [];
        $lines[] = $payload['body'] ?? '';
        $options = $payload['options'] ?? [];
        if (!empty($options)) {
            $lines[] = '';
            foreach ($options as $i => $opt) {
                $n = $i + 1;
                $label = $opt['text'] ?? ("Option {$n}");
                $cleanLabel = preg_replace('/^\d+[\.\)]?\s*/', '', $label);
                $lines[] = "{$n}) {$cleanLabel}";
            }
        }
        return implode("\n", $lines);
    }

    private function sendNodeOutgoing(string $to, WaNode $node, array $ctx): void
    {
        $body = $this->replaceVariables($node->body, $ctx);

        if ($node->type === 'buttons') {
            $text = $this->formatButtonsAsText([
                'body'    => $body,
                'options' => $node->options_json ?? []
            ]);
            $this->waSend($to, $text);
        } elseif ($node->type === 'list') {
            $lines = [$body];
            $sections = $node->options_json ?? [];
            if (!empty($sections)) {
                $lines[] = '';
                foreach ($sections as $section) {
                    if (!empty($section['title'])) $lines[] = $section['title'] . ':';
                    if (!empty($section['rows'])) {
                        foreach ($section['rows'] as $i => $row) {
                            $n = $i + 1;
                            $title = $row['title'] ?? ("Option {$n}");
                            $cleanTitle = preg_replace('/^\d+[\.\)]?\s*/', '', $title);
                            $lines[] = "{$n}) {$cleanTitle}";
                        }
                    }
                }
            }
            $this->waSend($to, implode("\n", $lines));
        } else {
            $this->waSend($to, $body);
        }
    }

    private function waSend(string $number, string $message): void
    {
        try {
            $botBase = rtrim(config('services.wa_bot.url', 'http://127.0.0.1:3000'), '/');
            Http::post($botBase . '/send-message', [
                'number'  => $number,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            \Log::error('WA proactive send failed', ['to' => $number, 'error' => $e->getMessage()]);
        }
    }
    // ---------- end proactive ----------

    private function replaceVariables($text, $ctx)
    {
        if (!$text) return '';
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($m) use ($ctx) {
            $varName = $m[1];
            switch ($varName) {
                case 'first_name':  return $ctx['user_first_name'] ?? 'User';
                case 'customer_name': return $ctx['customer_name'] ?? 'Customer';
                case 'address':
                    $address = $ctx['address'] ?? $ctx['user_address'] ?? '';
                    return $address !== '' ? $address : 'Address not provided';
                case 'postal_code':  return $ctx['postal_code'] ?? $ctx['user_postal_code'] ?? '';
                case 'city':         return $ctx['city'] ?? $ctx['user_city'] ?? 'Unknown city';
                case 'plumber_name': return $ctx['plumber_name'] ?? 'the plumber';
                case 'problem':
                    $map = ['leak'=>'Leak','blockage'=>'Blockage / Drain','heating'=>'Heating / Boiler','installation'=>'Installation / Replacement','other'=>'Other'];
                    return $map[$ctx['problem'] ?? ''] ?? 'Unknown problem';
                case 'urgency_label':
                    $um = ['high'=>'High — max 60 min','normal'=>'Normal — max 2 hours','later'=>'Later today / schedule'];
                    return $um[$ctx['urgency'] ?? ''] ?? 'Normal';
                case 'description':  return $ctx['description'] ?? 'No description provided';
                case 'distance_km':  return $ctx['distance_km'] ?? '5';
                case 'eta_min':      return $ctx['eta_min'] ?? '20';
                default:             return $ctx[$varName] ?? $m[0];
            }
        }, $text);
    }

    private function getNode($flowCode, $nodeCode)
    {
        $flow = WaFlow::where('code', $flowCode)->where('is_active', true)->first();
        if (!$flow) {
            \Log::error("Flow not found or inactive", ['flow_code'=>$flowCode, 'node_code'=>$nodeCode]);
            return null;
        }
        $node = WaNode::where('flow_id', $flow->id)->where('code', $nodeCode)->first();
        if (!$node) {
            \Log::error("Node not found", ['flow_code'=>$flowCode, 'node_code'=>$nodeCode, 'flow_id'=>$flow->id]);
        }
        return $node;
    }

    private function getNextNode($currentNode, $input)
    {
        $nextMap = $currentNode->next_map_json ?? [];
        if (isset($nextMap[$input])) {
            $nextNodeCode = $nextMap[$input];
            return WaNode::where('flow_id', $currentNode->flow_id)->where('code', $nextNodeCode)->first();
        }
        return null;
    }

    private function showMenu($from, $user, $session)
    {
        // Ensure plumber has a session when opening menu
        if (!$session && $user->role === 'plumber') {
            $session = WaSession::create([
                'wa_number'      => $from,
                'user_id'        => $user->id,
                'flow_code'      => 'plumber_flow',
                'node_code'      => 'menu',
                'context_json'   => [
                    'user_first_name'  => explode(' ', $user->full_name)[0],
                    'user_address'     => $user->address,
                    'user_postal_code' => $user->postal_code,
                    'user_city'        => $user->city,
                ],
                'last_message_at' => now(),
            ]);
        }
        if ($session) {
            $session->node_code = 'menu';
            $session->save();
        }

        if ($user->role === 'client') {
            // UPDATED client menu (adds "Mark job as completed" + moves support to 6, exit to 7)
            $msg  = "📋 *Client Menu*\n\n";
            $msg .= "Choose an option:\n\n";
            $msg .= "1) Start new request\n";
            $msg .= "2) View offers\n";
            $msg .= "3) Mark job as completed\n";
            $msg .= "4) Rate completed job\n";
            $msg .= "5) View status of current request\n";
            $msg .= "6) Contact support\n";
            $msg .= "7) Exit this menu\n\n";
            $msg .= "Reply with the number (1-7) to select an option.";
            return $this->replyText($from, $msg);
        } else {
            // Plumber menu unchanged
            $msg  = "🔧 *Plumber Menu*\n\n";
            $msg .= "Choose an option:\n\n";
            $msg .= "1) Set availability ON\n";
            $msg .= "2) Set availability OFF\n";
            $msg .= "3) Mark job as completed\n";
            $msg .= "4) Current request\n";
            $msg .= "5) Contact support\n";
            $msg .= "6) Exit this menu\n\n";
            $msg .= "Reply with the number (1-6) to select an option.";
            return $this->replyText($from, $msg);
        }
    }

    private function createAndBroadcastRequest($user, $ctx)
    {
        $existingRequest = WaRequest::where('customer_id', $user->id)
            ->whereIn('status', ['broadcasting', 'active', 'in_progress'])
            ->first();

        if ($existingRequest) {
            \Log::warning("Duplicate request attempt", [
                'user_id' => $user->id,
                'existing_request_id' => $existingRequest->id,
                'existing_status' => $existingRequest->status
            ]);
            return;
        }

        $request = WaRequest::create([
            'customer_id' => $user->id,
            'problem'     => $ctx['problem'],
            'urgency'     => $ctx['urgency'],
            'description' => $ctx['description'],
            'status'      => 'broadcasting'
        ]);

        $ctx['request_id'] = $request->id;

        $plumbers = User::where('role', 'plumber')
            ->where(function($q){ $q->where('status','available')->orWhere('status','Available'); })
            ->where(function($q){ $q->where('subscription_status','active')->orWhere('subscription_status','Active')->orWhereNull('subscription_status'); })
            ->whereNotExists(function($q){
                $q->select(DB::raw(1))
                  ->from('wa_requests')
                  ->whereRaw('wa_requests.selected_plumber_id = users.id')
                  ->whereIn('wa_requests.status', ['active','in_progress']);
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

        $plumberSession = WaSession::where('wa_number', $plumber->whatsapp_number)->first();
        if ($plumberSession) $plumberSession->delete();

        $urgencyLabel = $this->replaceVariables('{{urgency_label}}', ['urgency'=>$ctx['urgency']]);

        $plumberSession = WaSession::create([
            'wa_number'   => $plumber->whatsapp_number,
            'user_id'     => $plumber->id,
            'flow_code'   => 'plumber_flow',
            'node_code'   => 'P0',
            'context_json'=> [
                'request_id'   => $request->id,
                'customer_id'  => $customer->id,
                'customer_name'=> explode(' ', $customer->full_name)[0],
                'address'      => $customer->address,
                'postal_code'  => $customer->postal_code,
                'city'         => $customer->city,
                'problem'      => $ctx['problem'],
                'urgency'      => $ctx['urgency'],
                'urgency_label'=> $urgencyLabel,
                'description'  => $ctx['description'],
                'distance_km'  => '5',
                'eta_min'      => '20'
            ],
            'last_message_at' => now(),
        ]);

        $p0 = $this->getNode('plumber_flow', 'P0');
        if ($p0) $this->sendNodeOutgoing($plumber->whatsapp_number, $p0, $plumberSession->context_json);
    }

    private function createOffer($plumber, $ctx)
    {
        $requestId = $ctx['request_id'] ?? null;
        if (!$requestId) {
            $plumberSession = WaSession::where('wa_number', $plumber->whatsapp_number)->first();
            if ($plumberSession && isset($plumberSession->context_json['request_id'])) {
                $requestId = $plumberSession->context_json['request_id'];
            }
        }
        if (!$requestId) {
            \Log::error("Cannot create offer: missing request_id", ['plumber_id'=>$plumber->id, 'ctx'=>$ctx]);
            return;
        }

        $offer = WaOffer::create([
            'plumber_id'       => $plumber->id,
            'request_id'       => $requestId,
            'personal_message' => $ctx['personal_message'],
            'status'           => 'pending',
            'eta_minutes'      => 20,
            'distance_km'      => 5.0,
            'rating'           => 4.5
        ]);

        $request = WaRequest::find($requestId);
        if ($request) {
            $customer = User::find($request->customer_id);
            if ($customer) {
                // push new-offer notice
                $msg = "🎉 New plumber offer received!\n\n";
                $msg .= "Plumber: {$plumber->full_name}\n";
                $msg .= "Phone: +{$plumber->whatsapp_number}\n";
                $msg .= "Message: \"{$ctx['personal_message']}\"\n";
                $msg .= "ETA: 20 min 🚗\n\n";
                $msg .= "Type 'offers' to view all offers or wait for more.";
                $this->waSend($customer->whatsapp_number, $msg);

                // auto send full list
                $this->sendAutomaticOffersList($customer, $requestId);
            }
        }
    }

    private function sendAutomaticOffersList($customer, $requestId)
    {
        $offers = WaOffer::where('request_id', $requestId)
            ->with('plumber')
            ->latest()
            ->get()
            ->unique('plumber_id')
            ->values();

        if ($offers->isEmpty()) return;

        $message = "📋 Current plumber offers (choose a number to view details):\n\n";
        foreach ($offers as $i => $offer) {
            $pl = $offer->plumber;
            $message .= ($i+1).") {$pl->full_name} • ⭐ 4.5 • 20 min 🚗\n";
        }
        $message .= "\nType the number to see details, or wait for more options.";
        $this->waSend($customer->whatsapp_number, $message);
    }

    private function showOffersList($from, $user, $session)
    {
        $offers = WaOffer::whereHas('request', function($q) use ($user) {
            $q->where('customer_id', $user->id);
        })->with('plumber')->get();

        if ($offers->isEmpty()) {
            return $this->replyText($from, "Waiting for plumbers to accept your job...\nYou can reply help for commands.");
        }

        $message = "Plumbers who accepted your job (choose a number to view details):\n\n";
        $ids = [];
        foreach ($offers as $index => $offer) {
            $plumber = $offer->plumber;
            $message .= ($index + 1) . ") {$plumber->full_name} • ⭐ 4.5 • 20 min 🚗\n";
            $ids[] = $offer->id;
        }
        $message .= "\nType the number to see details, or wait for more options.";

        if ($session) {
            $session->node_code = 'C6';
            $session->context_json = ['offers' => $ids];
            $session->save();
        }

        return $this->replyText($from, $message);
    }

    private function showOfferDetails($from, $user, $session, $offerNumber)
    {
        $offers = WaOffer::whereHas('request', function($q) use ($user) {
            $q->where('customer_id', $user->id);
        })->with('plumber')->get();

        if ($offerNumber > 0 && $offerNumber <= $offers->count()) {
            $offer = $offers[$offerNumber - 1];
            $plumber = $offer->plumber;

            $message = "Do you want to select this plumber?\n";
            $message .= "Name: {$plumber->full_name}\n";
            $message .= "Phone: +{$plumber->whatsapp_number}\n";
            $message .= "From: {$plumber->city} • ETA: 20 min 🚗 • Distance: 5 km\n";
            $message .= "Rating: ⭐ 4.5\n";
            $message .= "Message to you: \"{$offer->personal_message}\"";

            if ($session) {
                $ctx = $session->context_json ?? [];
                $ctx['selected_offer_id'] = $offer->id;
                $session->node_code = 'C7';
                $session->context_json = $ctx;
                $session->save();
            }

            return $this->sendButtons($from, [
                'body'    => $message,
                'options' => [
                    ['id' => 'yes', 'text' => 'Yes'],
                    ['id' => 'no', 'text' => 'No'],
                    ['id' => 'choose_again', 'text' => 'Choose again']
                ]
            ]);
        }
        return $this->showOffersList($from, $user, $session);
    }

    private function selectPlumber($customer, $offerId)
    {
        $offer = WaOffer::with(['plumber', 'request'])->find($offerId);
        if (!$offer) return;

        // mark selected
        $offer->update(['status' => 'selected']);
        $offer->request->update([
            'status' => 'active',
            'selected_plumber_id' => $offer->plumber_id
        ]);

        // clear plumber sessions for this request
        $allOffers = WaOffer::where('request_id', $offer->request_id)->with('plumber')->get();
        foreach ($allOffers as $offerRecord) {
            WaSession::where('wa_number', $offerRecord->plumber->whatsapp_number)->delete();
        }

        // notify selected plumber (with client details + client phone)
        $this->waSend(
            $offer->plumber->whatsapp_number,
            "✅ You were selected by " . explode(' ', $customer->full_name)[0] . ".\n" .
            "Client phone: +{$customer->whatsapp_number}\n" .
            "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n" .
            "Problem: " . $this->getProblemLabel($offer->request->problem) . "\n" .
            "Description: \"{$offer->request->description}\"\n" .
            "Urgency: " . $this->getUrgencyLabel($offer->request->urgency) . "\n\n" .
            "Please proceed. Good luck!"
        );

        // notify client with plumber details + phone
        $this->waSend(
            $customer->whatsapp_number,
            "✅ Plumber selected!\n\n" .
            "Name: {$offer->plumber->full_name}\n" .
            "Phone: +{$offer->plumber->whatsapp_number}\n" .
            "City: {$offer->plumber->city}\n" .
            "ETA: 20 min • Distance: 5 km\n\n" .
            "They will contact you shortly."
        );

        // notify other plumbers
        $otherOffers = WaOffer::where('request_id', $offer->request_id)
            ->where('id', '!=', $offerId)
            ->with('plumber')
            ->get();

        foreach ($otherOffers as $otherOffer) {
            $this->waSend(
                $otherOffer->plumber->whatsapp_number,
                "❌ Another plumber was selected for this job. Thanks for responding — better luck next time!"
            );
        }

        \Log::info("Plumber selected for job", [
            'request_id' => $offer->request_id,
            'selected_plumber_id' => $offer->plumber_id,
            'other_plumbers_notified' => $otherOffers->count()
        ]);
    }

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
            'title'   => $payload['title'] ?? null,
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
        $message .= "📋 Available Plans:\n";
        $message .= "• One-time request: €25\n";
        $message .= "• Monthly plan: €9.99/month\n";
        $message .= "• Yearly plan: €99/year (2 months free)\n\n";
        $message .= "🌐 Visit our website to subscribe:\n";
        $message .= config('app.url') . "/pricing\n\n";
        $message .= "After subscribing, you can start using our service immediately!";
        return $this->replyText($from, $message);
    }

    private function handleRatingRequest($from, $user, $session)
    {
        $completedRequest = WaRequest::where('customer_id', $user->id)
            ->where('status', 'completed')
            ->whereNotExists(function($q){
                $q->select(DB::raw(1))->from('ratings')->whereRaw('ratings.request_id = wa_requests.id');
            })
            ->latest()
            ->first();

        if (!$completedRequest) {
            return $this->replyText($from, "You don't have any completed jobs to rate at the moment.");
        }

        WaSession::where('wa_number', $from)->delete();

        $session = WaSession::create([
            'wa_number'    => $from,
            'user_id'      => $user->id,
            'flow_code'    => 'rating_flow',
            'node_code'    => 'R1',
            'context_json' => [
                'request_id'     => $completedRequest->id,
                'plumber_id'     => $completedRequest->selected_plumber_id,
                'user_first_name'=> explode(' ', $user->full_name)[0],
            ],
            'last_message_at' => now(),
        ]);

        $plumber = User::find($completedRequest->selected_plumber_id);
        $message  = "⭐ Rate Your Experience\n\n";
        $message .= "How was your experience with " . ($plumber ? $plumber->full_name : 'the plumber') . "?\n\n";
        $message .= "Please rate from 1 to 5 stars:\n";
        $message .= "1 ⭐ - Poor\n";
        $message .= "2 ⭐⭐ - Fair\n";
        $message .= "3 ⭐⭐⭐ - Good\n";
        $message .= "4 ⭐⭐⭐⭐ - Very Good\n";
        $message .= "5 ⭐⭐⭐⭐⭐ - Excellent\n\n";
        $message .= "Reply with a number (1-5) to rate.";

        return $this->replyText($from, $message);
    }

    private function processRating($from, $user, $session, $rating)
    {
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
            return $this->replyText($from, "Please provide a valid rating between 1 and 5.");
        }

        $ctx = $session->context_json ?? [];
        $requestId = $ctx['request_id'] ?? null;
        $plumberId = $ctx['plumber_id'] ?? null;

        if (!$requestId || !$plumberId) {
            return $this->replyText($from, "Error: Could not find the job to rate.");
        }

        DB::table('ratings')->insert([
            'request_id' => $requestId,
            'customer_id'=> $user->id,
            'plumber_id' => $plumberId,
            'rating'     => $rating,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        WaRequest::where('id', $requestId)->update(['status' => 'rated']);

        $session->delete();

        $stars = str_repeat('⭐', $rating);
        $message  = "Thank you for your rating!\n\n";
        $message .= "You rated: {$stars} ({$rating}/5)\n\n";
        $message .= "Your feedback helps us improve our service and helps other customers choose the right plumber.\n\n";
        $message .= "Type 'start' to create a new request or 'help' for more options.";

        return $this->replyText($from, $message);
    }

    private function markJobCompleted($from, $user, $session)
    {
        // Plumber completes
        $activeRequest = WaRequest::where('selected_plumber_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$activeRequest) {
            return $this->replyText($from, "You don't have any active jobs to mark as completed.");
        }

        $activeRequest->update(['status' => 'completed']);

        $customer = User::find($activeRequest->customer_id);
        if ($customer) {
            $message  = "✅ Job Completed\n\n";
            $message .= "Your plumber has marked the job as completed.\n\n";
            $message .= "Job Details:\n";
            $message .= "• Problem: " . $this->getProblemLabel($activeRequest->problem) . "\n";
            $message .= "• Description: \"{$activeRequest->description}\"\n\n";
            $message .= "You can rate your experience by typing 'rate'.\n";
            $message .= "To create a new request, type 'start'.";
            $this->waSend($customer->whatsapp_number, $message);
        }

        return $this->replyText($from, "✅ Job marked as completed successfully!\n\nThe customer has been notified and can now rate your work.\n\nType 'help' for available commands.");
    }

    private function markJobCompletedByClient($from, $user, $session)
    {
        // Client completes
        $activeRequest = WaRequest::where('customer_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$activeRequest) {
            return $this->replyText($from, "You don't have any active jobs to mark as completed.\n\nType 'status' to see your latest request.");
        }

        $activeRequest->update(['status' => 'completed']);

        // Notify plumber, if any
        if ($activeRequest->selected_plumber_id) {
            $plumber = User::find($activeRequest->selected_plumber_id);
            if ($plumber) {
                $msg = "✅ The client has marked the job as completed.\n\n"
                     . "Request ID: {$activeRequest->id}\n"
                     . "Problem: " . $this->getProblemLabel($activeRequest->problem) . "\n"
                     . "Description: \"{$activeRequest->description}\"";
                $this->waSend($plumber->whatsapp_number, $msg);
            }
        }

        // Tell client next steps
        $message  = "✅ Job marked as completed!\n\n";
        $message .= "You can now type 'rate' to rate your experience.\n";
        $message .= "To create a new request, type 'start'.";
        return $this->replyText($from, $message);
    }

    private function getProblemLabel($id)
    {
        $map = ['leak'=>'Leak','blockage'=>'Blockage / Drain','heating'=>'Heating / Boiler','installation'=>'Installation / Replacement','other'=>'Other'];
        return $map[$id] ?? 'Unknown problem';
    }

    private function getUrgencyLabel($id)
    {
        $map = ['high'=>'High — max 60 min','normal'=>'Normal — max 2 hours','later'=>'Later today / schedule'];
        return $map[$id] ?? 'Normal';
    }

    private function showRequestStatus($from, $user, $session)
    {
        $request = WaRequest::where('customer_id', $user->id)
            ->whereIn('status', ['broadcasting', 'active', 'in_progress', 'completed'])
            ->latest()
            ->first();

        if (!$request) {
            return $this->replyText($from, "You don't have any active requests at the moment.\n\nType 'start' to create a new request.");
        }

        $message  = "📋 Request Status\n\n";
        $message .= "Request ID: {$request->id}\n";
        $message .= "Status: " . ucfirst($request->status) . "\n";
        $message .= "Problem: " . $this->getProblemLabel($request->problem) . "\n";
        $message .= "Urgency: " . $this->getUrgencyLabel($request->urgency) . "\n";
        $message .= "Description: \"{$request->description}\"\n\n";

        switch ($request->status) {
            case 'broadcasting':
                $message .= "🔄 Your request is being sent to available plumbers.\n";
                $message .= "Type 'offers' to check for responses from plumbers.";
                break;
            case 'active':
                if ($request->selected_plumber_id) {
                    $plumber = User::find($request->selected_plumber_id);
                    $message .= "✅ Plumber selected: " . ($plumber ? $plumber->full_name : 'Unknown') . "\n";
                    $message .= "The plumber is on their way to your location.\n";
                    $message .= "When the job is finished, type 'complete' to mark it as completed.";
                }
                break;
            case 'in_progress':
                $message .= "🛠️ Work is in progress.\n";
                $message .= "Type 'complete' when the job is finished.";
                break;
            case 'completed':
                $message .= "✅ Job completed!\n";
                $message .= "Type 'rate' to rate your experience with the plumber.\n";
                $message .= "Type 'start' to create a new request.";
                break;
        }

        return $this->replyText($from, $message);
    }

    private function showPlumberCurrentRequest($from, $user, $session)
    {
        $request = WaRequest::where('selected_plumber_id', $user->id)
            ->whereIn('status', ['active', 'in_progress'])
            ->latest()
            ->first();

        if (!$request) {
            $message = "You don't have any active jobs at the moment.\n\nYou'll receive notifications when new jobs are available in your area.";
            if ($session) $session->delete();
            return $this->replyText($from, $message);
        }

        $customer = User::find($request->customer_id);

        $message  = "🛠️ Current Job\n\n";
        $message .= "Request ID: {$request->id}\n";
        $message .= "Status: " . ucfirst($request->status) . "\n";
        $message .= "Customer: " . ($customer ? $customer->full_name : 'Unknown') . "\n";
        $message .= "Client phone: +" . ($customer ? $customer->whatsapp_number : 'N/A') . "\n";
        $message .= "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n";
        $message .= "Problem: " . $this->getProblemLabel($request->problem) . "\n";
        $message .= "Urgency: " . $this->getUrgencyLabel($request->urgency) . "\n";
        $message .= "Description: \"{$request->description}\"\n\n";
        $message .= ($request->status === 'active')
            ? "Type 'complete' to mark this job as completed when you finish."
            : "Work is in progress. Type 'complete' when finished.";

        return $this->replyText($from, $message);
    }

    private function handleStartCommand($from, $user, $session)
    {
        $activeRequest = WaRequest::where('customer_id', $user->id)
            ->whereIn('status', ['broadcasting', 'active', 'in_progress'])
            ->first();

        if ($activeRequest) {
            $message = "You already have an active request (ID: {$activeRequest->id}).\n\n";
            if ($activeRequest->status === 'broadcasting') {
                $message .= "Your request is currently being sent to available plumbers.\n";
                $message .= "Type 'offers' to check for responses from plumbers.";
            } else {
                $message .= "A plumber has been selected for your job.\n";
                $message .= "Type 'status' to check the current status.";
            }
            return $this->replyText($from, $message);
        }

        if ($session) $session->delete();

        $session = WaSession::create([
            'wa_number'      => $from,
            'user_id'        => $user->id,
            'flow_code'      => 'client_flow',
            'node_code'      => 'C0',
            'context_json'   => [
                'user_first_name'  => explode(' ', $user->full_name)[0],
                'user_address'     => $user->address,
                'user_postal_code' => $user->postal_code,
                'user_city'        => $user->city,
            ],
            'last_message_at' => now(),
        ]);

        return $this->replyText($from, "Starting new request... Please describe your problem.");
    }

    private function setAvailability($from, $user, $session, $available)
    {
        $user->update(['status' => $available ? 'available' : 'unavailable']);

        $message = $available
            ? "✅ You are now available to receive job requests.\n\nYou'll be notified when new jobs are available in your area."
            : "❌ You are now unavailable and won't receive job requests.\n\nType 'help' to change your status.";

        if ($session) $session->delete();
        return $this->replyText($from, $message);
    }

    private function showSupportMessage($from, $user, $session)
    {
        $message  = "📞 Contact Support\n\n";
        $message .= "For immediate assistance, please contact us:\n\n";
        $message .= "📧 Email: support@plumberplatform.com\n";
        $message .= "📱 Phone: +32 123 456 789\n";
        $message .= "🌐 Website: " . config('app.url') . "/support\n\n";
        $message .= "Our support team is available 24/7 to help you.";
        if ($session) $session->delete();
        return $this->replyText($from, $message);
    }
}
