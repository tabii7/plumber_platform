# 🚀 Server Deployment Guide

## Overview
This guide will help you deploy the enhanced address search functionality and WhatsApp menu fixes to your server.

## 📋 Files to Update

### 1. **API Routes** (`routes/api.php`)
**Replace the entire file** with the content from `deploy-address-search.sh` (the API routes section).

### 2. **WhatsApp Runtime Controller** (`app/Http/Controllers/Api/WaRuntimeController.php`)
**Apply these specific changes:**

#### A. Add Exit Command Handling (around line 70)
```php
// Handle exit command
if ($text === 'exit' || $text === '6') {
    if ($session) {
        $session->delete();
    }
    return $this->replyText($from, "Menu closed. Type 'help' to open the menu again or 'start' to begin a new request.");
}
```

#### B. Update Session Creation Logic (around line 45)
```php
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
```

#### C. Add Menu Option Handling (around line 150)
```php
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
```

#### D. Add No Session Handling (around line 180)
```php
if (!$session) {
    // No session - handle basic commands for plumbers
    if ($user->role === 'plumber') {
        return $this->replyText($from, "👋 Hi " . explode(' ', $user->full_name)[0] . "! You're now available to receive job requests.\n\nWhen a customer creates a request near you, you'll receive a notification automatically.\n\nType 'help' for available commands.");
    }
    // For clients, session should have been created above
    return $this->replyText($from, "Session error. Please try again.");
}
```

#### E. Add Helper Methods (at the end of the class)
```php
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
```

#### F. Update showMenu Method
Add this at the beginning of the `showMenu` method:
```php
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
```

### 3. **Register View** (`resources/views/auth/register.blade.php`)
**Replace the entire address search JavaScript section** with the content from `server-files/register-view-updates.js`.

### 4. **Add Test Route** (`routes/web.php`)
Add this line at the end of the file:
```php
Route::get('/test-address', function () {
    return view('test-address');
});
```

### 5. **Create Test View** (`resources/views/test-address.blade.php`)
Copy the entire content from the `test-address.blade.php` file we created.

## 🔧 Deployment Commands

After updating all files, run these commands on your server:

```bash
# Navigate to your project directory
cd /path/to/your/project

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Clear WhatsApp sessions (optional)
php artisan tinker --execute="App\Models\WaSession::truncate(); echo 'All WhatsApp sessions cleared successfully.';"

# Clear broadcasting requests (optional)
php artisan tinker --execute="App\Models\WaRequest::where('status', 'broadcasting')->update(['status' => 'cancelled']); echo 'Broadcasting requests cancelled.';"
```

## 🧪 Testing

After deployment, test these URLs:

1. **Register Page**: `https://yourdomain.com/register`
   - Try typing addresses like "Minnewater 22 Brugge"
   - Test keyboard navigation (↑↓ arrows, Enter, Escape)

2. **Address Test Page**: `https://yourdomain.com/test-address`
   - Test various address formats
   - Verify JSON data is captured correctly

3. **WhatsApp Bot**: 
   - Test "help" command
   - Test "6" or "exit" command
   - Verify no automatic job broadcasts appear

## 🐛 Troubleshooting

### Common Issues:

1. **Address search not working**:
   - Check browser console for JavaScript errors
   - Verify API routes are accessible
   - Check server logs for PHP errors

2. **WhatsApp menu issues**:
   - Clear all sessions: `php artisan tinker --execute="App\Models\WaSession::truncate();"`
   - Check WhatsApp bot logs
   - Verify the controller changes were applied correctly

3. **API errors**:
   - Check if cURL is enabled on your server
   - Verify external API endpoints are accessible
   - Check rate limiting

### Debug Commands:
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check WhatsApp bot logs
tail -f whatsapp-bot/whatsapp-bot.log

# Test API endpoints
curl "https://yourdomain.com/api/address/search-vlaanderen?q=test"
```

## ✅ Success Indicators

- ✅ Address search shows suggestions with [VL] and [OSM] badges
- ✅ Form fields auto-fill when selecting an address
- ✅ WhatsApp "help" shows menu, "6" or "exit" closes menu
- ✅ No automatic job broadcasts when plumbers send messages
- ✅ Test page shows JSON data correctly

## 📞 Support

If you encounter issues:
1. Check the Laravel logs first
2. Verify all file changes were applied correctly
3. Test the API endpoints individually
4. Clear all caches and sessions

The enhanced address search should now work with robust Belgian address data and the WhatsApp menu should handle exits properly!
