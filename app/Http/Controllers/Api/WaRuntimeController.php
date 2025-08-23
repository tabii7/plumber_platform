<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{User, WaLog, WaSession, WaRequest, WaOffer, WaFlow, WaNode};

class WaRuntimeController extends Controller
{
    public function incoming(Request $request)
    {
        $from = preg_replace('/\D+/', '', (string) $request->input('from'));
        $text = strtolower(trim((string) $request->input('message')));
        $now = now();

        // Log incoming message
        WaLog::create([
            'wa_number' => $from,
            'direction' => 'in',
            'payload_json' => $request->all(),
            'status' => 'recv'
        ]);

        // Find user
        $user = User::where('whatsapp_number', $from)->first();

        if (!$user) {
            // Handle unregistered user flow
            return $this->handleDynamicFlow($from, $text, null, 'unregistered_flow', 'U0');
        }

        // Get or create session
        $session = WaSession::where('wa_number', $from)->first();
        
        if (!$session) {
            // Start new session based on user role
            $flowCode = $user->role === 'client' ? 'client_flow' : 'plumber_flow';
            $startNode = $user->role === 'client' ? 'C0' : 'P0';
            
            $session = WaSession::create([
                'wa_number' => $from,
                'user_id' => $user->id,
                'flow_code' => $flowCode,
                'node_code' => $startNode,
                'context_json' => [],
                'last_message_at' => $now,
            ]);
        }

        // Handle menu commands
        if ($text === 'menu' || $text === 'help') {
            return $this->showMenu($from, $user, $session);
        }

        // Handle start command - reset session and start fresh
        if ($text === 'start') {
            if ($session) {
                $session->delete();
            }
            
            // Create new session
            $flowCode = $user->role === 'client' ? 'client_flow' : 'plumber_flow';
            $startNode = $user->role === 'client' ? 'C0' : 'P0';
            
            $session = WaSession::create([
                'wa_number' => $from,
                'user_id' => $user->id,
                'flow_code' => $flowCode,
                'node_code' => $startNode,
                'context_json' => [],
                'last_message_at' => $now,
            ]);
        }

        // Handle offers command for customers
        if ($text === 'offers' && $user->role === 'client') {
            return $this->showOffersList($from, $user, $session);
        }

        // Handle dynamic flow based on user role
        if ($user->role === 'client') {
            return $this->handleDynamicFlow($from, $text, $user, 'client_flow', $session->node_code, $session);
        } else {
            return $this->handleDynamicFlow($from, $text, $user, 'plumber_flow', $session->node_code, $session);
        }
    }

    private function handleDynamicFlow($from, $text, $user, $flowCode, $nodeCode, $session = null)
    {
        // Get the flow and current node
        $flow = WaFlow::where('code', $flowCode)->where('is_active', true)->first();
        if (!$flow) {
            return $this->replyText($from, "Flow not found or inactive.");
        }

        $node = WaNode::where('flow_id', $flow->id)
                     ->where('code', $nodeCode)
                     ->first();
        
        if (!$node) {
            return $this->replyText($from, "Node not found.");
        }

        $ctx = $session ? ($session->context_json ?? []) : [];

        // Handle special nodes that need custom logic
        switch ($nodeCode) {
            case 'C4': // Consent to broadcast
                if ($text === 'yes' || $text === '1') {
                    // Create request and broadcast to plumbers
                    $this->createAndBroadcastRequest($user, $ctx);
                    
                    // Move to next node
                    $nextNode = $this->getNextNode($node, $text);
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

            case 'C5': // Broadcast started - waiting for offers
                if ($text === 'help') {
                    return $this->showMenu($from, $user, $session);
                } else {
                    // Check for offers and show list
                    return $this->showOffersList($from, $user, $session);
                }
                break;

            case 'C6': // Offers list
                if (is_numeric($text)) {
                    $offerNumber = (int) $text;
                    return $this->showOfferDetails($from, $user, $session, $offerNumber);
                } else {
                    return $this->showOffersList($from, $user, $session);
                }
                break;

            case 'C7': // Offer details
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
                } elseif ($text === 'no' || $text === '2') {
                    if ($session) {
                        $session->node_code = 'C6';
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->showOffersList($from, $user, $session);
                } elseif ($text === 'choose_again' || $text === '3') {
                    if ($session) {
                        $session->node_code = 'C6';
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->showOffersList($from, $user, $session);
                }
                break;

            case 'P0': // New job broadcast
                if ($text === 'yes' || $text === '1') {
                    if ($session) {
                        $session->node_code = 'P1';
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    
                    // Show job details again and ask for personal message
                    $customer = User::find($ctx['customer_id'] ?? null);
                    $message = "Great! Here are the job details:\n\n";
                    $message .= "Client: " . ($customer ? explode(' ', $customer->full_name)[0] : 'Unknown') . "\n";
                    $message .= "Address: " . ($customer ? "{$customer->address}, {$customer->postal_code} {$customer->city}" : 'Unknown') . "\n";
                    $message .= "Problem: {$ctx['problem']}\n";
                    $message .= "Description: \"{$ctx['description']}\"\n";
                    $message .= "Urgency: {$ctx['urgency_label']}\n\n";
                    $message .= "Send your short message (one sentence). Example: \"I can be there in 20 minutes. I have jetting equipment.\"";
                    
                    return $this->replyText($from, $message);
                } elseif ($text === 'no' || $text === '2') {
                    if ($session) $session->delete();
                    return $this->replyText($from, "No problem. We'll keep sending you nearby requests.");
                }
                break;

            case 'P1': // After plumber accepts
                $ctx['personal_message'] = $text;
                if ($session) {
                    $session->context_json = $ctx;
                    $session->save();
                }
                
                // Create offer
                $this->createOffer($user, $ctx);
                
                if ($session) $session->delete();
                return $this->replyText($from, "Thanks! Your offer has been sent to the client. You'll be notified if they choose you.");
                break;
        }

        // Handle dynamic node processing
        return $this->processDynamicNode($from, $text, $user, $node, $ctx, $session);
    }

    private function processDynamicNode($from, $text, $user, $node, $ctx, $session)
    {
        $options = $node->options_json ?? [];
        $nextMap = $node->next_map_json ?? [];

        // Check if input matches any option
        $matchedOption = null;
        foreach ($options as $option) {
            if (isset($option['id']) && $option['id'] === $text) {
                $matchedOption = $option;
                break;
            }
        }

        // If no direct match, check for numeric input
        if (!$matchedOption && is_numeric($text)) {
            $optionIndex = (int) $text - 1;
            if (isset($options[$optionIndex])) {
                $matchedOption = $options[$optionIndex];
            }
        }

        // If still no match, check for text variations
        if (!$matchedOption) {
            foreach ($options as $option) {
                if (isset($option['variations']) && in_array($text, $option['variations'])) {
                    $matchedOption = $option;
                    break;
                }
            }
        }

        // Get next node based on input
        $nextNode = null;
        if ($matchedOption && isset($nextMap[$matchedOption['id']])) {
            $nextNodeCode = $nextMap[$matchedOption['id']];
            $nextNode = WaNode::where('flow_id', $node->flow_id)
                             ->where('code', $nextNodeCode)
                             ->first();
        } elseif (isset($nextMap['default'])) {
            $nextNodeCode = $nextMap['default'];
            $nextNode = WaNode::where('flow_id', $node->flow_id)
                             ->where('code', $nextNodeCode)
                             ->first();
        }

        // Update context with user input
        if ($matchedOption) {
            $ctx['last_input'] = $text;
            $ctx['last_option'] = $matchedOption;
        }

        // Update session
        if ($session && $nextNode) {
            $session->node_code = $nextNode->code;
            $session->context_json = $ctx;
            $session->save();
        }

        // Send response based on node type
        return $this->sendNodeResponse($from, $nextNode ?: $node, $ctx);
    }

    private function sendNodeResponse($from, $node, $ctx)
    {
        $body = $this->replaceVariables($node->body, $ctx);
        
        switch ($node->type) {
            case 'buttons':
                $options = $node->options_json ?? [];
                return $this->sendButtons($from, [
                    'body' => $body,
                    'options' => $options
                ]);
            
            case 'list':
                $options = $node->options_json ?? [];
                return $this->sendList($from, [
                    'title' => $node->title,
                    'body' => $body,
                    'options' => $options
                ]);
            
            case 'text':
            default:
                return $this->replyText($from, $body);
        }
    }

    private function replaceVariables($text, $ctx)
    {
        // Replace variables in text like {{variable_name}}
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($ctx) {
            $varName = $matches[1];
            return $ctx[$varName] ?? $matches[0];
        }, $text);
    }

    private function getNextNode($currentNode, $input)
    {
        $nextMap = $currentNode->next_map_json ?? [];
        
        if (isset($nextMap[$input])) {
            $nextNodeCode = $nextMap[$input];
            return WaNode::where('flow_id', $currentNode->flow_id)
                        ->where('code', $nextNodeCode)
                        ->first();
        }
        
        return null;
    }

    private function showMenu($from, $user, $session)
    {
        if ($user->role === 'client') {
            return $this->sendList($from, [
                'title' => 'Main menu',
                'body' => 'Choose an option:',
                'options' => [
                    [
                        'title' => 'Customer Options',
                        'rows' => [
                            ['id' => 'start', 'title' => '1. Start new request'],
                            ['id' => 'status', 'title' => '2. View status of current request'],
                            ['id' => 'edit', 'title' => '3. Edit description / urgency'],
                            ['id' => 'cancel', 'title' => '4. Cancel current request'],
                            ['id' => 'support', 'title' => '5. Contact support'],
                            ['id' => 'exit', 'title' => '6. Exit this menu']
                        ]
                    ]
                ]
            ]);
        } else {
            return $this->sendList($from, [
                'title' => 'Plumber menu',
                'body' => 'Choose an option:',
                'options' => [
                    [
                        'title' => 'Plumber Options',
                        'rows' => [
                            ['id' => 'available_on', 'title' => '1. Set availability ON'],
                            ['id' => 'available_off', 'title' => '2. Set availability OFF'],
                            ['id' => 'current_request', 'title' => '3. Current request'],
                            ['id' => 'support', 'title' => '4. Contact support'],
                            ['id' => 'exit', 'title' => '5. Exit this menu']
                        ]
                    ]
                ]
            ]);
        }
    }

    private function createAndBroadcastRequest($user, $ctx)
    {
        // Create request record
        $request = WaRequest::create([
            'customer_id' => $user->id,
            'problem' => $ctx['problem'],
            'urgency' => $ctx['urgency'],
            'description' => $ctx['description'],
            'status' => 'broadcasting'
        ]);

        // Store request ID in context
        $ctx['request_id'] = $request->id;

        // Find nearby available plumbers
        $plumbers = User::where('role', 'plumber')
            ->where('status', 'available')
            ->where('subscription_status', 'active')
            ->get();

        // Broadcast to each plumber
        foreach ($plumbers as $plumber) {
            $this->sendJobBroadcast($plumber, $user, $ctx, $request);
        }
    }

    private function sendJobBroadcast($plumber, $customer, $ctx, $request)
    {
        // Create or update plumber session with request context
        $plumberSession = WaSession::where('wa_number', $plumber->whatsapp_number)->first();
        if ($plumberSession) {
            $plumberSession->delete(); // Clear existing session
        }
        
        $plumberSession = WaSession::create([
            'wa_number' => $plumber->whatsapp_number,
            'user_id' => $plumber->id,
            'flow_code' => 'plumber_flow',
            'node_code' => 'P0',
            'context_json' => [
                'request_id' => $request->id,
                'customer_id' => $customer->id,
                'problem' => $ctx['problem'],
                'urgency' => $ctx['urgency'],
                'description' => $ctx['description']
            ],
            'last_message_at' => now(),
        ]);

        $message = "New request near you:\n";
        $message .= "Client: " . explode(' ', $customer->full_name)[0] . "\n";
        $message .= "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n";
        $message .= "Problem: {$ctx['problem']}\n";
        $message .= "Description: \"{$ctx['description']}\"\n";
        $message .= "Urgency: {$ctx['urgency_label']}\n";
        $message .= "Distance: 5 km • ETA: 20 min 🚗\n";
        $message .= "Do you want to accept?";

        // Send via WhatsApp bot API
        try {
            $response = \Http::post('http://127.0.0.1:3000/send-message', [
                'number' => $plumber->whatsapp_number,
                'message' => $message
            ]);
            
            if ($response->successful()) {
                \Log::info("Job broadcast sent to plumber", [
                    'plumber_id' => $plumber->id,
                    'request_id' => $request->id,
                    'response' => $response->json()
                ]);
            } else {
                \Log::error("Failed to send job broadcast", [
                    'plumber_id' => $plumber->id,
                    'request_id' => $request->id,
                    'error' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Exception sending job broadcast", [
                'plumber_id' => $plumber->id,
                'request_id' => $request->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function createOffer($plumber, $ctx)
    {
        // Create offer record
        $offer = WaOffer::create([
            'plumber_id' => $plumber->id,
            'request_id' => $ctx['request_id'] ?? 1,
            'personal_message' => $ctx['personal_message'],
            'status' => 'pending',
            'eta_minutes' => 20, // Default ETA
            'distance_km' => 5.0, // Default distance
            'rating' => 4.5 // Default rating
        ]);

        // Notify customer about new offer
        $request = WaRequest::find($ctx['request_id'] ?? 1);
        if ($request) {
            $customer = User::find($request->customer_id);
            if ($customer) {
                $message = "🎉 New plumber offer received!\n\n";
                $message .= "Plumber: {$plumber->full_name}\n";
                $message .= "Message: \"{$ctx['personal_message']}\"\n";
                $message .= "ETA: 20 min 🚗\n\n";
                $message .= "Type 'offers' to view all offers or wait for more.";

                try {
                    $response = \Http::post('http://127.0.0.1:3000/send-message', [
                        'number' => $customer->whatsapp_number,
                        'message' => $message
                    ]);
                    
                    if ($response->successful()) {
                        \Log::info("Offer notification sent to customer", [
                            'customer_id' => $customer->id,
                            'offer_id' => $offer->id,
                            'plumber_id' => $plumber->id
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to notify customer about offer", [
                        'customer_id' => $customer->id,
                        'offer_id' => $offer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    private function showOffersList($from, $user, $session)
    {
        // Get offers for this customer
        $offers = WaOffer::whereHas('request', function($q) use ($user) {
            $q->where('customer_id', $user->id);
        })->with('plumber')->get();

        if ($offers->isEmpty()) {
            return $this->replyText($from, "Waiting for plumbers to accept your job...\nYou can reply help for commands.");
        }

        $message = "Plumbers who accepted your job (choose a number to view details):\n\n";
        $options = [];

        foreach ($offers as $index => $offer) {
            $plumber = $offer->plumber;
            $message .= ($index + 1) . ") {$plumber->full_name} • ⭐ 4.5 • 20 min 🚗\n";
            $options[] = ['id' => ($index + 1), 'text' => ($index + 1) . ') ' . $plumber->full_name];
        }

        $message .= "\nType the number to see details, or wait for more options.";

        if ($session) {
            $session->node_code = 'C6';
            $session->context_json = ['offers' => $offers->pluck('id')->toArray()];
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
                'body' => $message,
                'options' => [
                    ['id' => 'yes', 'text' => 'Yes'],
                    ['id' => 'no', 'text' => 'No'],
                    ['id' => 'choose_again', 'text' => 'Choose again']
                ]
            ]);
        } else {
            return $this->showOffersList($from, $user, $session);
        }
    }

    private function selectPlumber($customer, $offerId)
    {
        $offer = WaOffer::with(['plumber', 'request'])->find($offerId);
        if (!$offer) return;

        // Update offer status
        $offer->update(['status' => 'selected']);

        // Update request status
        $offer->request->update([
            'status' => 'active',
            'selected_plumber_id' => $offer->plumber_id
        ]);

        // Notify selected plumber
        $this->replyText($offer->plumber->whatsapp_number, 
            "✅ You were selected by " . explode(' ', $customer->full_name)[0] . ".\n" .
            "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n" .
            "Please proceed. Good luck!"
        );

        // Notify other plumbers
        $otherOffers = WaOffer::where('request_id', $offer->request_id)
            ->where('id', '!=', $offerId)
            ->with('plumber')
            ->get();

        foreach ($otherOffers as $otherOffer) {
            $this->replyText($otherOffer->plumber->whatsapp_number,
                "❌ Another plumber was selected for this job. Thanks for responding — better luck next time!"
            );
        }
    }

    private function showJobBroadcast($from, $user, $session)
    {
        // This would show the job broadcast message again
        // For now, we'll just show a generic message
        return $this->replyText($from, "New job broadcast received. Please respond with Yes or No.");
    }

    private function sendButtons($to, $payload)
    {
        WaLog::create([
            'wa_number' => $to,
            'direction' => 'out',
            'payload_json' => $payload,
            'status' => 'queued'
        ]);

        return response()->json(['reply' => [
            'type' => 'buttons',
            'body' => $payload['body'],
            'options' => $payload['options']
        ]]);
    }

    private function sendList($to, $payload)
    {
        WaLog::create([
            'wa_number' => $to,
            'direction' => 'out',
            'payload_json' => $payload,
            'status' => 'queued'
        ]);

        return response()->json(['reply' => [
            'type' => 'list',
            'title' => $payload['title'],
            'body' => $payload['body'],
            'options' => $payload['options']
        ]]);
    }

    private function replyText($to, $text)
    {
        WaLog::create([
            'wa_number' => $to,
            'direction' => 'out',
            'payload_json' => ['type' => 'text', 'body' => $text],
            'status' => 'queued'
        ]);

        return response()->json(['reply' => ['type' => 'text', 'body' => $text]]);
    }
}
