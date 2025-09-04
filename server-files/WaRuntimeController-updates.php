<?php
// UPDATES FOR: app/Http/Controllers/Api/WaRuntimeController.php
// 
// ADD THESE CHANGES TO YOUR EXISTING FILE:

// 1. ADD EXIT COMMAND HANDLING (around line 70, after menu commands)
        // Handle exit command
        if ($text === 'exit' || $text === '6') {
            if ($session) {
                $session->delete();
            }
            return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
        }

// 2. ADD MENU OPTION HANDLING (around line 150, before handleDynamicFlow call)
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

// 3. ADD SESSION CREATION LOGIC (around line 45, replace the existing session creation)
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

// 4. ADD NO SESSION HANDLING (around line 180, before currentFlowCode)
        if (!$session) {
            // No session - handle basic commands for plumbers
            if ($user->role === 'plumber') {
                return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
            }
            // For clients, session should have been created above
            return $this->replyText($from, "Session error. Please try again.");
        }

// 5. ADD HELPER METHODS (at the end of the class, before the closing brace)
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
        $message .= "📱 Phone: +32 490 46 80 09\n";
        $message .= "🌐 Website: " . config('app.url') . "/support\n\n";
        $message .= "Our support team is available 24/7 to help you.";
        
        // Clear session after showing support message
        if ($session) {
            $session->delete();
        }
        
        return $this->replyText($from, $message);
    }

// 6. UPDATE showMenu METHOD (find the existing showMenu method and add this at the beginning)
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

// 7. UPDATE showPlumberCurrentRequest METHOD (find the existing method and update the "no request" part)
        if (!$request) {
            $message = "You don't have any active jobs at the moment.\n\nYou'll receive notifications when new jobs are available in your area.";
            
            // Clear session after showing current request
            if ($session) {
                $session->delete();
            }
            
            return $this->replyText($from, $message);
        }

// 8. UPDATE markJobCompleted METHOD (find the existing method and update the return message)
        return $this->replyText($from, "✅ Job marked as completed successfully!\n\nThe customer has been notified and can now rate your work.\n\nType 'help' for available commands.");
