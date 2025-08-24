# 🚀 WhatsApp Bot Live Server Deployment Guide

## 🔧 **Step 1: Server Environment Setup**

### **Create WhatsApp Bot .env file:**
```bash
# In whatsapp-bot directory
cat > .env << 'EOF'
# WhatsApp Bot Environment Configuration
LARAVEL_API_URL=https://your-domain.com
PORT=3000
HOST=0.0.0.0

# WhatsApp Bot Settings
BROWSER_NAME=LoodgieterApp
BROWSER_VERSION=Chrome
BROWSER_OS=1.0.0

# Connection Settings
RECONNECT_INTERVAL=1500
MAX_RECONNECT_ATTEMPTS=10

# Logging
LOG_LEVEL=info
EOF
```

### **Install Dependencies:**
```bash
cd whatsapp-bot
npm install
```

## 🔒 **Step 2: Server Configuration**

### **A. Firewall Settings (Ubuntu/Debian):**
```bash
# Allow WhatsApp Web ports
sudo ufw allow 3000
sudo ufw allow 443
sudo ufw allow 80

# Check if ports are open
sudo netstat -tlnp | grep :3000
```

### **B. Create Systemd Service (Recommended):**
```bash
sudo nano /etc/systemd/system/whatsapp-bot.service
```

**Service Content:**
```ini
[Unit]
Description=WhatsApp Bot Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/html/whatsapp-bot
ExecStart=/usr/bin/node index.js
Restart=always
RestartSec=10
Environment=NODE_ENV=production
Environment=PORT=3000
Environment=HOST=0.0.0.0

[Install]
WantedBy=multi-user.target
```

**Enable and Start Service:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable whatsapp-bot
sudo systemctl start whatsapp-bot
sudo systemctl status whatsapp-bot
```

## 🌐 **Step 3: Nginx Reverse Proxy (Recommended)**

### **Create Nginx Configuration:**
```bash
sudo nano /etc/nginx/sites-available/whatsapp-bot
```

**Nginx Config:**
```nginx
server {
    listen 80;
    server_name your-domain.com;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;

    # WhatsApp Bot API
    location /whatsapp/ {
        proxy_pass http://127.0.0.1:3000/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 86400;
    }

    # Laravel Application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

**Enable Site:**
```bash
sudo ln -s /etc/nginx/sites-available/whatsapp-bot /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔧 **Step 4: Update WhatsApp Bot Configuration**

### **Modify index.js for Live Server:**
```javascript
// Update the LARAVEL_API URL
const LARAVEL_API = process.env.LARAVEL_API_URL || 'https://your-domain.com';

// Add better error handling
sock = makeWASocket({
    auth: state,
    printQRInTerminal: false,
    browser: [
        process.env.BROWSER_NAME || 'LoodgieterApp',
        process.env.BROWSER_VERSION || 'Chrome',
        process.env.BROWSER_OS || '1.0.0'
    ],
    syncFullHistory: false,
    // Add connection options for live server
    connectTimeoutMs: 60000,
    qrTimeout: 40000,
    defaultQueryTimeoutMs: 60000,
    retryRequestDelayMs: 250
});
```

## 🚨 **Step 5: Common Issues & Solutions**

### **Issue 1: QR Code Not Generating**
**Symptoms:** No QR code appears, connection timeouts
**Solutions:**
```bash
# Check if bot is running
sudo systemctl status whatsapp-bot

# Check logs
sudo journalctl -u whatsapp-bot -f

# Clear auth data and restart
sudo rm -rf /var/www/html/whatsapp-bot/auth_info/*
sudo systemctl restart whatsapp-bot
```

### **Issue 2: Connection Conflicts**
**Symptoms:** "Stream Errored (conflict)" messages
**Solutions:**
```bash
# Kill all Node.js processes
sudo pkill -f node

# Check for multiple instances
ps aux | grep node

# Restart service
sudo systemctl restart whatsapp-bot
```

### **Issue 3: Port Blocked**
**Symptoms:** Can't connect to port 3000
**Solutions:**
```bash
# Check if port is listening
sudo netstat -tlnp | grep :3000

# Check firewall
sudo ufw status

# Test local connection
curl http://localhost:3000/status
```

### **Issue 4: WhatsApp Blocking Server IP**
**Symptoms:** QR codes expire quickly, connection refused
**Solutions:**
- Use a residential IP or VPS with clean IP
- Implement proper rate limiting
- Use WhatsApp Business API instead (recommended for production)

## 🔍 **Step 6: Testing & Verification**

### **Test Bot Status:**
```bash
# Local test
curl http://localhost:3000/status

# Remote test (replace with your domain)
curl https://your-domain.com/whatsapp/status
```

### **Test QR Generation:**
```bash
# Get QR code
curl https://your-domain.com/whatsapp/get-qr
```

### **Test Message Sending:**
```bash
curl -X POST https://your-domain.com/whatsapp/send-message \
  -H "Content-Type: application/json" \
  -d '{"number":"1234567890","message":"Test message"}'
```

## 📱 **Step 7: Laravel Integration Update**

### **Update WhatsAppService.php:**
```php
// Change from localhost to your domain
$response = Http::post('https://your-domain.com/whatsapp/send-message', [
    'number' => $phoneNumber,
    'message' => $message
]);
```

### **Update AdminWhatsAppController:**
```php
// Update QR endpoint
public function qr()
{
    try {
        $response = Http::get('https://your-domain.com/whatsapp/get-qr');
        return response()->json($response->json());
    } catch (\Exception $e) {
        return response()->json(['error' => 'QR not available'], 500);
    }
}
```

## 🚀 **Step 8: Production Recommendations**

### **A. Use PM2 Instead of Systemd:**
```bash
npm install -g pm2
pm2 start index.js --name whatsapp-bot
pm2 startup
pm2 save
```

### **B. Use WhatsApp Business API (Recommended):**
- More stable for production
- Better rate limits
- Official support
- No QR code needed

### **C. Implement Health Checks:**
```bash
# Create health check script
cat > /var/www/html/whatsapp-bot/health-check.sh << 'EOF'
#!/bin/bash
if ! curl -s http://localhost:3000/status | grep -q "Connected"; then
    echo "WhatsApp bot is down, restarting..."
    sudo systemctl restart whatsapp-bot
fi
EOF

chmod +x /var/www/html/whatsapp-bot/health-check.sh

# Add to crontab
echo "*/5 * * * * /var/www/html/whatsapp-bot/health-check.sh" | sudo crontab -
```

## 📞 **Step 9: Troubleshooting Commands**

### **Check Bot Status:**
```bash
sudo systemctl status whatsapp-bot
sudo journalctl -u whatsapp-bot -n 50
```

### **Check Network:**
```bash
# Test WhatsApp Web connectivity
curl -I https://web.whatsapp.com

# Test your server connectivity
curl -I https://your-domain.com/whatsapp/status
```

### **Check Logs:**
```bash
# Real-time logs
sudo journalctl -u whatsapp-bot -f

# Check Laravel logs
tail -f /var/www/html/storage/logs/laravel.log
```

## ✅ **Success Indicators**

When everything is working correctly, you should see:
- ✅ Bot service running: `Active: active (running)`
- ✅ QR code accessible: `curl https://your-domain.com/whatsapp/get-qr` returns QR data
- ✅ Status endpoint working: `{"status":"Connected","user":{"name":"Your Name"}}`
- ✅ Messages being sent successfully
- ✅ No connection conflicts in logs

## 🆘 **Emergency Fixes**

### **If QR Still Not Working:**
1. **Clear all auth data:**
   ```bash
   sudo rm -rf /var/www/html/whatsapp-bot/auth_info/*
   sudo systemctl restart whatsapp-bot
   ```

2. **Check server IP reputation:**
   ```bash
   curl https://api.abuseipdb.com/api/v2/check?ipAddress=$(curl -s ifconfig.me)
   ```

3. **Use different browser configuration:**
   ```javascript
   browser: ['Chrome (Linux)', '', '']
   ```

4. **Implement exponential backoff:**
   ```javascript
   let reconnectAttempts = 0;
   const maxReconnectAttempts = 10;
   
   function reconnect() {
     if (reconnectAttempts < maxReconnectAttempts) {
       setTimeout(connectToWhatsApp, Math.pow(2, reconnectAttempts) * 1000);
       reconnectAttempts++;
     }
   }
   ```

This guide should resolve your live server WhatsApp QR issues! 🎉

