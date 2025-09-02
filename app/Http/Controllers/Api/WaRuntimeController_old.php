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
        $originalText = trim((string) $request->input('message'));
        $text = strtolower($originalText);
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
            // Only create session for clients automatically
            // Plumbers should only get sessions when they receive job broadcasts
            if ($user->role === 'client') {
                $session = WaSession::create([
                    'wa_number' => $from,
                    'user_id' => $user->id,
                    'flow_code' => 'client_flow',
                    'node_code' => 'C0',
                    'context_json' => [
                        'user_first_name' => explode(' ', $user->full_name)[0],
                        'user_address' => $user->address,
                        'user_postal_code' => $user->postal_code,
                        'user_city' => $user->city,
                    ],
                    'last_message_at' => $now,
                ]);
            }
        }

        // Handle menu commands
        if ($text === 'menu' || $text === 'help') {
            return $this->showMenu($from, $user, $session);
        }

        // Handle exit command
        if ($text === 'exit' || $text === '6') {
            if ($session) {
                $session->delete();
            }
            return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
        }

        // Handle rating command for customers
        if ($text === 'rate' && $user->role === 'client') {
            return $this->handleRatingRequest($from, $user, $session);
        }

        // Handle job completion command for plumbers
        if ($text === 'complete' && $user->role === 'plumber') {
            return $this->markJobCompleted($from, $user, $session);
        }

        // Handle current request command for plumbers
        if ($text === 'current_request' && $user->role === 'plumber') {
            return $this->showPlumberCurrentRequest($from, $user, $session);
        }

        // Handle start command - reset session and start fresh (case insensitive)
        if ($text === 'start' || $originalText === 'Start') {
            if ($user->role === 'client') {
                // Check if client already has an active request
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
            
            if ($session) {
                $session->delete();
            }
            
            if ($user->role === 'client') {
                // Create new client session
                $session = WaSession::create([
                    'wa_number' => $from,
                    'user_id' => $user->id,
                    'flow_code' => 'client_flow',
                    'node_code' => 'C0',
                    'context_json' => [
                        'user_first_name' => explode(' ', $user->full_name)[0],
                        'user_address' => $user->address,
                        'user_postal_code' => $user->postal_code,
                        'user_city' => $user->city,
                    ],
                    'last_message_at' => $now,
                ]);
            } else {
                // For plumbers, just send a welcome message and delete any existing session
                return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
            }
        }

        // Handle offers command for customers
        if ($text === 'offers' && $user->role === 'client') {
            return $this->showOffersList($from, $user, $session);
        }

        // Handle status command for customers
        if ($text === 'status' && $user->role === 'client') {
            return $this->showRequestStatus($from, $user, $session);
        }

        // Handle dynamic flow based on user role and current flow
        if (!$session) {
            // No session - handle basic commands for plumbers
            if ($user->role === 'plumber') {
                return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
            }
            // For clients, session should have been created above
            return $this->replyText($from, "Session error. Please try again.");
        }
        
        $currentFlowCode = $session->flow_code ?? ($user->role === 'client' ? 'client_flow' : 'plumber_flow');
        
        // Check if this is a menu option selection
        if (is_numeric($text) && $session && $session->node_code === 'menu') {
            // Handle menu option selections
            switch ($text) {
                case '1':
                    if ($user->role === 'client') {
                        return $this->handleStartCommand($from, $user, $session);
                    } else {
                        return $this->setAvailability($from, $user, $session, true);
                    }
                    break;
                case '2':
                    if ($user->role === 'client') {
                        return $this->showOffersList($from, $user, $session);
                    } else {
                        return $this->setAvailability($from, $user, $session, false);
                    }
                    break;
                case '3':
                    if ($user->role === 'client') {
                        return $this->handleRatingRequest($from, $user, $session);
                    } else {
                        return $this->markJobCompleted($from, $user, $session);
                    }
                    break;
                case '4':
                    if ($user->role === 'client') {
                        return $this->showRequestStatus($from, $user, $session);
                    } else {
                        return $this->showPlumberCurrentRequest($from, $user, $session);
                    }
                    break;
                case '5':
                    return $this->showSupportMessage($from, $user, $session);
                    break;
                case '6':
                    // Exit menu
                    if ($session) {
                        $session->delete();
                    }
                    return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
                    break;
                default:
                    return $this->showMenu($from, $user, $session);
            }
        }
        
        return $this->handleDynamicFlow($from, $text, $user, $currentFlowCode, $session->node_code, $session, $originalText);
    }

    private function handleDynamicFlow($from, $text, $user, $flowCode, $nodeCode, $session = null, $originalText = null)
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
                    $nextNode = $this->getNextNode($node, $text);
                    if ($session && $nextNode) {
                        $session->node_code = $nextNode->code;
                        $session->context_json = $ctx;
                        $session->save();
                    }
                    return $this->sendNodeResponse($from, $nextNode, $ctx);
                } elseif ($text === 'no' || $text === '2') {
                    if ($session) $session->delete();
                    return $this->replyText($from, "No problem. We'll keep sending you nearby requests.");
                }
                break;

            case 'P1': // After plumber accepts
                $ctx['personal_message'] = $text;
                if ($session) {
                    // Preserve existing context and merge with new data
                    $existingContext = $session->context_json ?? [];
                    $session->context_json = array_merge($existingContext, $ctx);
                    $session->save();
                }
                
                // Create offer
                $this->createOffer($user, $ctx);
                
                // Don't delete the session immediately - keep it until job is selected or rejected
                return $this->replyText($from, "Thanks! Your offer has been sent to the client. You'll be notified if they choose you.");
                break;

            case 'R1': // Rating flow
                return $this->processRating($from, $user, $session, $text);
                break;
        }

        // Handle dynamic node processing
        return $this->processDynamicNode($from, $text, $user, $node, $ctx, $session, $originalText);
    }

    private function processDynamicNode($from, $text, $user, $node, $ctx, $session, $originalText = null)
    {
        $options = $node->options_json ?? [];
        $nextMap = $node->next_map_json ?? [];

        // Check if input matches any option
        $matchedOption = null;
        
        // First, check for exact ID match
        foreach ($options as $option) {
            if (isset($option['id']) && $option['id'] === $text) {
                $matchedOption = $option;
                break;
            }
        }

        // If no direct match, check for numeric input (1, 2, 3, etc.)
        if (!$matchedOption && is_numeric($text)) {
            $optionIndex = (int) $text - 1;
            if (isset($options[$optionIndex])) {
                $matchedOption = $options[$optionIndex];
            }
        }

        // If still no match, check for text variations (yes/no, etc.)
        if (!$matchedOption) {
            foreach ($options as $option) {
                if (isset($option['variations']) && in_array($text, $option['variations'])) {
                    $matchedOption = $option;
                    break;
                }
            }
        }

        // If still no match, check for partial text matches
        if (!$matchedOption) {
            foreach ($options as $option) {
                if (isset($option['text'])) {
                    $optionText = strtolower($option['text']);
                    $userText = strtolower($text);
                    
                    // Check if user input contains option text or vice versa
                    if (stripos($optionText, $userText) !== false || 
                        stripos($userText, $optionText) !== false) {
                        $matchedOption = $option;
                        break;
                    }
                    
                    // Handle common variations for problem types
                    if (($optionText === '1) leak' && in_array($userText, ['leak', 'leakage', 'water leak', 'dripping'])) ||
                        ($optionText === '2) blockage / drain' && in_array($userText, ['blockage', 'drain', 'clogged', 'clog', 'blocked'])) ||
                        ($optionText === '3) heating / boiler' && in_array($userText, ['heating', 'boiler', 'hot water', 'heater'])) ||
                        ($optionText === '4) installation / replacement' && in_array($userText, ['installation', 'replace', 'new', 'install'])) ||
                        ($optionText === '5) other' && in_array($userText, ['other', 'something else', 'different']))) {
                        $matchedOption = $option;
                        break;
                    }
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
            
            // Store specific values based on current node
            if ($node->code === 'C1') {
                $ctx['problem'] = $matchedOption['id'];
                $ctx['problem_label'] = $matchedOption['text'];
            } elseif ($node->code === 'C2') {
                $ctx['urgency'] = $matchedOption['id'];
                $ctx['urgency_label'] = $matchedOption['text'];
            }
        } elseif ($node->type === 'collect_text') {
            // For text input nodes, store the original text
            $ctx['description'] = $originalText;
            $ctx['last_input'] = $originalText;
        }

        // Update session
        if ($session && $nextNode) {
            $session->node_code = $nextNode->code;
            // Preserve existing context and merge with new data
            $existingContext = $session->context_json ?? [];
            $session->context_json = array_merge($existingContext, $ctx);
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
                // Format options for WhatsApp buttons
                $formattedOptions = [];
                foreach ($options as $option) {
                    $formattedOptions[] = [
                        'id' => $option['id'],
                        'text' => $option['text']
                    ];
                }
                return $this->sendButtons($from, [
                    'body' => $body,
                    'options' => $formattedOptions
                ]);
            
            case 'list':
                $options = $node->options_json ?? [];
                return $this->sendList($from, [
                    'title' => $node->title,
                    'body' => $body,
                    'options' => $options
                ]);
            
            case 'collect_text':
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
            
            // Handle special variables
            switch ($varName) {
                case 'first_name':
                    return $ctx['user_first_name'] ?? 'User';
                case 'customer_name':
                    return $ctx['customer_name'] ?? 'Customer';
                case 'address':
                    $address = $ctx['address'] ?? $ctx['user_address'] ?? '';
                    return !empty($address) ? $address : 'Address not provided';
                case 'postal_code':
                    return $ctx['postal_code'] ?? $ctx['user_postal_code'] ?? '';
                case 'city':
                    return $ctx['city'] ?? $ctx['user_city'] ?? 'Unknown city';
                case 'plumber_name':
                    return $ctx['plumber_name'] ?? 'the plumber';
                case 'problem':
                    // Map problem IDs to readable labels
                    $problemMap = [
                        'leak' => 'Leak',
                        'blockage' => 'Blockage / Drain',
                        'heating' => 'Heating / Boiler',
                        'installation' => 'Installation / Replacement',
                        'other' => 'Other'
                    ];
                    $problemId = $ctx['problem'] ?? '';
                    return $problemMap[$problemId] ?? 'Unknown problem';
                case 'urgency_label':
                    // Map urgency IDs to readable labels
                    $urgencyMap = [
                        'high' => 'High — max 60 min',
                        'normal' => 'Normal — max 2 hours',
                        'later' => 'Later today / schedule'
                    ];
                    $urgencyId = $ctx['urgency'] ?? '';
                    return $urgencyMap[$urgencyId] ?? 'Normal';
                case 'description':
                    return $ctx['description'] ?? 'No description provided';
                case 'distance_km':
                    return $ctx['distance_km'] ?? '5';
                case 'eta_min':
                    return $ctx['eta_min'] ?? '20';
                default:
                    return $ctx[$varName] ?? $matches[0];
            }
        }, $text);
    }

    private function getNode($flowCode, $nodeCode)
    {
        $flow = WaFlow::where('code', $flowCode)->where('is_active', true)->first();
        if (!$flow) {
            return null;
        }

        return WaNode::where('flow_id', $flow->id)
                    ->where('code', $nodeCode)
                    ->first();
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
        // Create session for plumbers if they don't have one
        if (!$session && $user->role === 'plumber') {
            $session = WaSession::create([
                'wa_number' => $from,
                'user_id' => $user->id,
                'flow_code' => 'plumber_flow',
                'node_code' => 'menu',
                'context_json' => [
                    'user_first_name' => explode(' ', $user->full_name)[0],
                    'user_address' => $user->address,
                    'user_postal_code' => $user->postal_code,
                    'user_city' => $user->city,
                ],
                'last_message_at' => now(),
            ]);
        }
        
        // Set session to menu state so we can handle menu selections
        if ($session) {
            $session->node_code = 'menu';
            $session->save();
        }
        
        if ($user->role === 'client') {
            return $this->sendList($from, [
                'title' => 'Main menu',
                'body' => 'Choose an option:',
                'options' => [
                    [
                        'title' => 'Customer Options',
                        'rows' => [
                            ['id' => 'start', 'title' => '1. Start new request'],
                            ['id' => 'offers', 'title' => '2. View offers'],
                            ['id' => 'rate', 'title' => '3. Rate completed job'],
                            ['id' => 'status', 'title' => '4. View status of current request'],
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
                            ['id' => 'complete', 'title' => '3. Mark job as completed'],
                            ['id' => 'current_request', 'title' => '4. Current request'],
                            ['id' => 'support', 'title' => '5. Contact support'],
                            ['id' => 'exit', 'title' => '6. Exit this menu']
                        ]
                    ]
                ]
            ]);
        }
    }

    private function createAndBroadcastRequest($user, $ctx)
    {
        // Check if user already has an active request
        $existingRequest = WaRequest::where('customer_id', $user->id)
            ->whereIn('status', ['broadcasting', 'active', 'in_progress'])
            ->first();
        
        if ($existingRequest) {
            \Log::warning("Attempted to create duplicate request for user", [
                'user_id' => $user->id,
                'existing_request_id' => $existingRequest->id,
                'existing_status' => $existingRequest->status
            ]);
            return;
        }

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

        // Find nearby available plumbers (not currently working on a job)
        $plumbers = User::where('role', 'plumber')
            ->where(function($query) {
                $query->where('status', 'available')
                      ->orWhere('status', 'Available');
            })
            ->where(function($query) {
                $query->where('subscription_status', 'active')
                      ->orWhere('subscription_status', 'Active')
                      ->orWhereNull('subscription_status');
            })
            ->whereNotExists(function($query) {
                $query->select(\DB::raw(1))
                      ->from('wa_requests')
                      ->whereRaw('wa_requests.selected_plumber_id = users.id')
                      ->whereIn('wa_requests.status', ['active', 'in_progress']);
            })
            ->get();

        // Broadcast to each plumber
        foreach ($plumbers as $plumber) {
            $this->sendJobBroadcast($plumber, $user, $ctx, $request);
        }
    }

    private function sendJobBroadcast($plumber, $customer, $ctx, $request)
    {
        // Check if plumber already has an active job
        $activeJob = WaRequest::where('selected_plumber_id', $plumber->id)
            ->whereIn('status', ['active', 'in_progress'])
            ->first();
        
        if ($activeJob) {
            \Log::info("Skipping job broadcast - plumber has active job", [
                'plumber_id' => $plumber->id,
                'active_job_id' => $activeJob->id
            ]);
            return;
        }

        // Create or update plumber session with request context
        $plumberSession = WaSession::where('wa_number', $plumber->whatsapp_number)->first();
        if ($plumberSession) {
            $plumberSession->delete(); // Clear existing session
        }
        
        // Ensure urgency_label is correctly generated
        $urgencyLabel = $this->replaceVariables('{{urgency_label}}', ['urgency' => $ctx['urgency']]);
        
        $plumberSession = WaSession::create([
            'wa_number' => $plumber->whatsapp_number,
            'user_id' => $plumber->id,
            'flow_code' => 'plumber_flow',
            'node_code' => 'P0',
            'context_json' => [
                'request_id' => $request->id,
                'customer_id' => $customer->id,
                'customer_name' => explode(' ', $customer->full_name)[0],
                'address' => $customer->address,
                'postal_code' => $customer->postal_code,
                'city' => $customer->city,
                'problem' => $ctx['problem'],
                'urgency' => $ctx['urgency'],
                'urgency_label' => $urgencyLabel,
                'description' => $ctx['description'],
                'distance_km' => '5',
                'eta_min' => '20'
            ],
            'last_message_at' => now(),
        ]);

        // Use the dynamic flow system instead of hardcoded message
        $this->sendNodeResponse($plumber->whatsapp_number, $this->getNode('plumber_flow', 'P0'), $plumberSession->context_json);
        
        \Log::info("Job broadcast sent to plumber via dynamic flow", [
            'plumber_id' => $plumber->id,
            'request_id' => $request->id,
            'context' => $plumberSession->context_json
        ]);
    }

    private function createOffer($plumber, $ctx)
    {
        // Get the request_id from the plumber's session if not in context
        $requestId = $ctx['request_id'] ?? null;
        if (!$requestId) {
            $plumberSession = WaSession::where('wa_number', $plumber->whatsapp_number)->first();
            if ($plumberSession && isset($plumberSession->context_json['request_id'])) {
                $requestId = $plumberSession->context_json['request_id'];
            }
        }

        if (!$requestId) {
            \Log::error("Cannot create offer: No request_id found for plumber", [
                'plumber_id' => $plumber->id,
                'context' => $ctx
            ]);
            return;
        }

        // Create offer record
        $offer = WaOffer::create([
            'plumber_id' => $plumber->id,
            'request_id' => $requestId,
            'personal_message' => $ctx['personal_message'],
            'status' => 'pending',
            'eta_minutes' => 20, // Default ETA
            'distance_km' => 5.0, // Default distance
            'rating' => 4.5 // Default rating
        ]);

        // Notify customer about new offer
        $request = WaRequest::find($requestId);
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

        // Clear all plumber sessions for this request
        $allOffers = WaOffer::where('request_id', $offer->request_id)->with('plumber')->get();
        foreach ($allOffers as $offerRecord) {
            WaSession::where('wa_number', $offerRecord->plumber->whatsapp_number)->delete();
        }

        // Notify selected plumber
        $this->replyText($offer->plumber->whatsapp_number, 
            "✅ You were selected by " . explode(' ', $customer->full_name)[0] . ".\n" .
            "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n" .
            "Problem: " . $this->getProblemLabel($offer->request->problem) . "\n" .
            "Description: \"{$offer->request->description}\"\n" .
            "Urgency: " . $this->getUrgencyLabel($offer->request->urgency) . "\n\n" .
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

        \Log::info("Plumber selected for job", [
            'request_id' => $offer->request_id,
            'selected_plumber_id' => $offer->plumber_id,
            'other_plumbers_notified' => $otherOffers->count()
        ]);
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

    private function checkClientSubscription($user)
    {
        // Check if user has an active subscription
        if ($user->subscription_status === 'active') {
            // Check if subscription hasn't expired
            if ($user->subscription_ends_at && now()->gt($user->subscription_ends_at)) {
                return false;
            }
            return true;
        }
        
        return false;
    }

    private function showSubscriptionPrompt($from, $user)
    {
        $message = "🔒 Subscription Required\n\n";
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
        // Check if user has a completed job to rate
        $completedRequest = WaRequest::where('customer_id', $user->id)
            ->where('status', 'completed')
            ->whereNotExists(function($query) {
                $query->select(\DB::raw(1))
                      ->from('ratings')
                      ->whereRaw('ratings.request_id = wa_requests.id');
            })
            ->latest()
            ->first();

        if (!$completedRequest) {
            return $this->replyText($from, "You don't have any completed jobs to rate at the moment.");
        }

        // Clear any existing session for this user
        WaSession::where('wa_number', $from)->delete();

        // Create rating session
        $session = WaSession::create([
            'wa_number' => $from,
            'user_id' => $user->id,
            'flow_code' => 'rating_flow',
            'node_code' => 'R1',
            'context_json' => [
                'request_id' => $completedRequest->id,
                'plumber_id' => $completedRequest->selected_plumber_id,
                'user_first_name' => explode(' ', $user->full_name)[0],
            ],
            'last_message_at' => now(),
        ]);

        $plumber = User::find($completedRequest->selected_plumber_id);
        $message = "⭐ Rate Your Experience\n\n";
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

        // Create rating record
        \DB::table('ratings')->insert([
            'request_id' => $requestId,
            'customer_id' => $user->id,
            'plumber_id' => $plumberId,
            'rating' => $rating,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update request status to rated
        WaRequest::where('id', $requestId)->update(['status' => 'rated']);

        // Delete rating session
        $session->delete();

        $stars = str_repeat('⭐', $rating);
        $message = "Thank you for your rating!\n\n";
        $message .= "You rated: {$stars} ({$rating}/5)\n\n";
        $message .= "Your feedback helps us improve our service and helps other customers choose the right plumber.\n\n";
        $message .= "Type 'start' to create a new request or 'help' for more options.";

        return $this->replyText($from, $message);
    }

    private function markJobCompleted($from, $user, $session)
    {
        // Find active job for this plumber
        $activeRequest = WaRequest::where('selected_plumber_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$activeRequest) {
            return $this->replyText($from, "You don't have any active jobs to mark as completed.");
        }

        // Update request status to completed
        $activeRequest->update(['status' => 'completed']);

        // Notify customer
        $customer = User::find($activeRequest->customer_id);
        if ($customer) {
            $message = "✅ Job Completed\n\n";
            $message .= "Your plumber has marked the job as completed.\n\n";
            $message .= "Job Details:\n";
            $message .= "• Problem: " . $this->getProblemLabel($activeRequest->problem) . "\n";
            $message .= "• Description: \"{$activeRequest->description}\"\n\n";
            $message .= "You can rate your experience by typing 'rate' or 'start' to create a new request.";

            $this->replyText($customer->whatsapp_number, $message);
        }

        return $this->replyText($from, "✅ Job marked as completed successfully!\n\nThe customer has been notified and can now rate your work.\n\nType 'help' for available commands.");
    }

    private function getProblemLabel($problemId)
    {
        $problemMap = [
            'leak' => 'Leak',
            'blockage' => 'Blockage / Drain',
            'heating' => 'Heating / Boiler',
            'installation' => 'Installation / Replacement',
            'other' => 'Other'
        ];
        return $problemMap[$problemId] ?? 'Unknown problem';
    }

    private function getUrgencyLabel($urgencyId)
    {
        $urgencyMap = [
            'high' => 'High — max 60 min',
            'normal' => 'Normal — max 2 hours',
            'later' => 'Later today / schedule'
        ];
        return $urgencyMap[$urgencyId] ?? 'Normal';
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

        $message = "📋 Request Status\n\n";
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
                    $message .= "The plumber is on their way to your location.";
                }
                break;
            
            case 'in_progress':
                $message .= "🛠️ Work is in progress.\n";
                $message .= "The plumber is currently working on your request.";
                break;
            
            case 'completed':
                $message .= "✅ Job completed!\n";
                $message .= "Type 'rate' to rate your experience with the plumber.";
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
            
            // Clear session after showing current request
            if ($session) {
                $session->delete();
            }
            
            return $this->replyText($from, $message);
        }

        $customer = User::find($request->customer_id);
        
        $message = "🛠️ Current Job\n\n";
        $message .= "Request ID: {$request->id}\n";
        $message .= "Status: " . ucfirst($request->status) . "\n";
        $message .= "Customer: " . ($customer ? $customer->full_name : 'Unknown') . "\n";
        $message .= "Address: {$customer->address}, {$customer->postal_code} {$customer->city}\n";
        $message .= "Problem: " . $this->getProblemLabel($request->problem) . "\n";
        $message .= "Urgency: " . $this->getUrgencyLabel($request->urgency) . "\n";
        $message .= "Description: \"{$request->description}\"\n\n";

        if ($request->status === 'active') {
            $message .= "Type 'complete' to mark this job as completed when you finish.";
        } else {
            $message .= "Work is in progress. Type 'complete' when finished.";
        }

        return $this->replyText($from, $message);
    }

    private function handleStartCommand($from, $user, $session)
    {
        // Check if client already has an active request
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
        
        // Reset session and start fresh
        if ($session) {
            $session->delete();
        }
        
        // Create new client session
        $session = WaSession::create([
            'wa_number' => $from,
            'user_id' => $user->id,
            'flow_code' => 'client_flow',
            'node_code' => 'C0',
            'context_json' => [
                'user_first_name' => explode(' ', $user->full_name)[0],
                'user_address' => $user->address,
                'user_postal_code' => $user->postal_code,
                'user_city' => $user->city,
            ],
            'last_message_at' => now(),
        ]);
        
        return $this->replyText($from, "Starting new request... Please describe your problem.");
    }

    private function setAvailability($from, $user, $session, $available)
    {
        $user->update(['status' => $available ? 'available' : 'unavailable']);
        
        $status = $available ? 'available' : 'unavailable';
        $message = $available 
            ? "✅ You are now available to receive job requests.\n\nYou'll be notified when new jobs are available in your area."
            : "❌ You are now unavailable and won't receive job requests.\n\nType 'help' to change your status.";
        
        // Clear session after setting availability
        if ($session) {
            $session->delete();
        }
        
        return $this->replyText($from, $message);
    }

    private function showSupportMessage($from, $user, $session)
    {
        $message = "📞 Contact Support\n\n";
        $message .= "For immediate assistance, please contact us:\n\n";
        $message .= "📧 Email: support@plumberplatform.com\n";
        $message .= "📱 Phone: +32 123 456 789\n";
        $message .= "🌐 Website: " . config('app.url') . "/support\n\n";
        $message .= "Our support team is available 24/7 to help you.";
        
        // Clear session after showing support message
        if ($session) {
            $session->delete();
        }
        
        return $this->replyText($from, $message);
    }
}
