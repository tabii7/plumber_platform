# WhatsApp Flow System

## Overview
This system implements a complete WhatsApp-based communication flow between customers and plumbers using Baileys server. The system handles real-time messaging, job requests, plumber offers, and selection processes.

## Architecture

### **Roles & Communication Flow**
```
Customer → chats with Baileys Server
Plumbers → each chats with Baileys Server  
Baileys Server → routing, fan-out, ranking, updates, selection, rating
```

### **System Components**
- **Baileys WhatsApp Bot** (`whatsapp-bot/index.js`) - Handles WhatsApp connection and message routing
- **Laravel API** (`WaRuntimeController`) - Processes business logic and flow management
- **Database Models** - `WaRequest`, `WaOffer`, `WaSession`, `WaLog` for data persistence
- **Session Management** - Tracks user state and conversation flow

## Customer Flow (C0-C11)

### **C0 — Greeting / Address confirmation**
```
Hi {{firstname}}!
Do you want plumber service at {{address}}, {{postal}} {{city}}?

1) Yes
2) No

If 2 No → "Okay, canceled for now. Type start anytime to create a new request."
```

### **C1 — Problem type (choose one)**
```
What's the problem?

1) Leak
2) Blockage / Drain  
3) Heating / Boiler
4) Installation / Replacement
5) Other
```

### **C2 — Urgency**
```
How urgent is it?

1) High — max 60 min
2) Normal — max 2 hours
3) Later today / schedule
```

### **C3 — Short description (free text)**
```
Please describe the problem in 1–2 sentences.
You may add a photo or video.
```

### **C4 — Consent to broadcast**
```
Do you want me to send this request to available plumbers near you?

1) Yes, send to all available
2) No, cancel

If 2 No → "Request canceled. Type start to try again."
```

### **C5 — Broadcast started**
```
Got it. I'm notifying nearby plumbers now. You'll receive options as they accept.
You can reply help for commands.
```

### **C6 — Offers arriving (dynamic list)**
```
Plumbers who accepted your job (choose a number to view details):

1) John Smith Plumbing • ⭐ 3.5 • 35 min 🚗
2) AquaFix Services • ⭐ 4.0 • 20 min 🚗
3) PipeMasters BV • ⭐ 4.7 • 28 min 🚗

Type the number to see details, or wait for more options.
```

### **C7 — Plumber detail (on selecting a number)**
```
Do you want to select this plumber?
Name: AquaFix Services
From: Ghent • ETA: 20 min 🚗 • Distance: 12 km
Rating: ⭐ 4.0
Message to you: "I have jetting equipment with me and can be there in 20 minutes."

1) Yes
2) No
3) Choose again

If 1 Yes → go C8
If 2 No → return to C6 (list)
If 3 Choose again → return to C6
```

### **C8 — Customer confirms selection**
```
Great! You selected AquaFix Services.
I'll share your full address and contact info now and notify other plumbers that the job is taken.
```

### **C9 — Post-selection confirmation to customer**
```
Confirmed. AquaFix Services is on the way.

Address: {{address}}, {{postal}} {{city}}
Problem: {{problem}}
Urgency: {{urgency_label}}
Your description: "{{description}}"

If you need help, type help.
```

### **C10 — 24h rating request (automated)**
```
How was the work? Please score 1–5 (1 bad, 5 excellent).
You can also add a short comment.
```

### **C11 — After rating**
```
Thanks! Your rating has been saved.
Type start to create a new request, or exit to end the session.
```

## Plumber Flow (P0-P4)

### **P0 — New job broadcast**
```
New request near you:
Client: {{firstname}}
Address: {{address}}, {{postal}} {{city}}
Problem: {{problem}}
Description: "{{description}}"
Urgency: {{urgency_label}}
Distance: {{distance_km}} km • ETA: {{eta_min}} min 🚗
Do you want to accept?

1) Yes
2) No

(Optional: add a short personal message for the client. It stays hidden until you are selected.)
```

### **P1 — After plumber accepts**
```
Send your short message (one sentence). Example: "I can be there in {{eta_min}} minutes. I have jetting equipment."
```

### **P2 — Plumber chosen**
```
✅ You were selected by {{firstname}}.
Address: {{address}}, {{postal}} {{city}}
Please proceed. Good luck!
```

### **P3 — Plumber not chosen**
```
❌ Someone else was selected. Thanks for responding — next time, be quick!
We'll keep sending you nearby requests.
```

### **P4 — 24h rating request**
```
How did it go with this job? Score 1–5.
You can add a short comment if you like.
```

## Menu System

### **Customer Menu**
```
Main menu:
1. Start new request
2. View status of current request
3. Edit description / urgency
4. Cancel current request
5. Contact support
6. Exit this menu
```

### **Plumber Menu**
```
Plumber menu:
1. Set availability ON
2. Set availability OFF
3. Current request
4. Contact support
5. Exit this menu
```

### **Support Integration**
- Support menu option links to: `www.domain.com/support`
- Simple contact form for basic support requests
- Future upgrade path for advanced support features

## Technical Implementation

### **Database Schema**

#### **wa_requests Table**
```sql
- id (primary key)
- customer_id (foreign key to users)
- problem (string: Leak, Blockage, Heating, etc.)
- urgency (string: high, normal, later)
- description (text)
- status (string: broadcasting, active, completed, cancelled)
- selected_plumber_id (foreign key to users, nullable)
- completed_at (timestamp, nullable)
- rating (integer 1-5, nullable)
- rating_comment (text, nullable)
- timestamps
```

#### **wa_offers Table**
```sql
- id (primary key)
- plumber_id (foreign key to users)
- request_id (foreign key to wa_requests)
- personal_message (text)
- status (string: pending, selected, rejected)
- eta_minutes (integer, nullable)
- distance_km (decimal, nullable)
- rating (decimal, nullable)
- timestamps
```

### **Session Management**
- **WaSession** tracks user conversation state
- **Node-based flow** (C0-C11 for customers, P0-P4 for plumbers)
- **Context persistence** stores request data and user selections
- **Automatic cleanup** when conversations complete

### **Message Processing**
1. **Incoming message** → Baileys bot receives
2. **Route to Laravel** → `/api/wa/incoming` endpoint
3. **Session lookup** → Find or create user session
4. **Flow processing** → Handle based on current node
5. **Response generation** → Send back to Baileys
6. **Outgoing message** → Baileys sends to user

### **Broadcasting System**
1. **Customer creates request** → Stored in `wa_requests`
2. **Find available plumbers** → Query by location and subscription status
3. **Create plumber sessions** → Each plumber gets P0 session with request context
4. **Send broadcast messages** → Individual messages to each plumber
5. **Track responses** → Store offers in `wa_offers` table

### **Selection Process**
1. **Customer views offers** → Dynamic list from `wa_offers`
2. **Select plumber** → Update offer status to 'selected'
3. **Notify selected plumber** → Send P2 message
4. **Notify other plumbers** → Send P3 messages
5. **Update request status** → Mark as 'active'

## Key Features

### **Real-time Updates**
- **Dynamic offer lists** refresh as plumbers accept
- **Live status updates** for customers and plumbers
- **Instant notifications** when selections are made

### **Smart Routing**
- **Role-based flows** (customer vs plumber)
- **Session persistence** across message exchanges
- **Context awareness** maintains conversation state

### **Professional Messaging**
- **Formatted messages** with emojis and clear structure
- **Personalized content** using user data
- **Consistent branding** across all communications

### **Error Handling**
- **Graceful fallbacks** for invalid inputs
- **Session recovery** for interrupted conversations
- **Logging system** for debugging and monitoring

## Integration Points

### **User Management**
- **Registration required** for WhatsApp access
- **Role-based access** (client vs plumber)
- **Subscription validation** for plumber availability

### **Location Services**
- **Address validation** using existing postal code system
- **Distance calculation** for ETA estimates
- **Coverage area matching** for plumber selection

### **Payment Integration**
- **Subscription status** affects plumber availability
- **Future billing** integration for completed jobs
- **Rating system** for service quality

## Testing Scenarios

### **Customer Journey**
1. **New customer** → C0 greeting and address confirmation
2. **Problem selection** → C1-C3 problem, urgency, description
3. **Broadcast consent** → C4 confirmation to send to plumbers
4. **Waiting for offers** → C5-C6 dynamic offer list
5. **Plumber selection** → C7-C9 detailed view and confirmation
6. **Job completion** → C10-C11 rating and feedback

### **Plumber Journey**
1. **Job broadcast** → P0 receive new job notification
2. **Accept/reject** → P1 personal message if accepted
3. **Selection notification** → P2 if chosen, P3 if not
4. **Job completion** → P4 rating request

### **Edge Cases**
- **No plumbers available** → Customer gets waiting message
- **Multiple offers** → Dynamic list with selection options
- **Session timeout** → Automatic cleanup and restart
- **Invalid inputs** → Graceful error messages and retry

## Future Enhancements

### **Advanced Features**
1. **Photo/video support** → Media message handling
2. **Scheduled appointments** → Calendar integration
3. **Payment processing** → In-chat payment options
4. **Multi-language support** → Localized message templates

### **Analytics & Monitoring**
1. **Response time tracking** → Performance metrics
2. **Success rate analysis** → Conversion optimization
3. **User behavior insights** → Flow improvement
4. **Real-time dashboards** → Live system monitoring

### **Integration Extensions**
1. **CRM integration** → Customer relationship management
2. **Scheduling system** → Calendar and availability sync
3. **Inventory management** → Parts and equipment tracking
4. **Insurance integration** → Claims and coverage verification

## Deployment & Configuration

### **Environment Variables**
```env
LARAVEL_API_URL=http://127.0.0.1:8001
WHATSAPP_BOT_URL=http://127.0.0.1:3000
```

### **Service Dependencies**
- **Laravel application** running on port 8001
- **Baileys bot** running on port 3000
- **MySQL database** for data persistence
- **Redis** (optional) for session caching

### **Monitoring & Logs**
- **WaLog table** tracks all message exchanges
- **Application logs** for error tracking
- **Performance metrics** for system health
- **User feedback** for continuous improvement

## Conclusion

The WhatsApp Flow System provides a complete, professional communication platform for connecting customers with plumbers. The system handles the entire journey from initial request to job completion, with intelligent routing, real-time updates, and comprehensive tracking.

The implementation follows the exact specifications provided, ensuring a seamless user experience for both customers and plumbers while maintaining the flexibility to add advanced features in the future.
