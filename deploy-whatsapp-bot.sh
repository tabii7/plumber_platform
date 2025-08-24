#!/bin/bash

# 🚀 WhatsApp Bot Deployment Script
# This script will set up the WhatsApp bot on your server

echo "🚀 Starting WhatsApp Bot Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "whatsapp-bot/index.js" ]; then
    print_error "WhatsApp bot not found. Please run this script from the project root."
    exit 1
fi

print_status "Stopping any existing WhatsApp bot processes..."
pkill -f "node.*whatsapp-bot" 2>/dev/null || true
pkill -f "node.*index.js" 2>/dev/null || true
sleep 2

print_status "Clearing authentication data..."
rm -rf whatsapp-bot/auth_info 2>/dev/null || true

print_status "Creating environment configuration..."

# Detect if this is a live server or local
if [ "$1" = "live" ]; then
    DOMAIN=${2:-"your-domain.com"}
    print_status "Configuring for LIVE server: $DOMAIN"
    
    cat > whatsapp-bot/.env << EOF
# WhatsApp Bot Environment Configuration for LIVE SERVER
LARAVEL_API_URL=https://$DOMAIN
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
else
    print_status "Configuring for LOCAL development"
    
    cat > whatsapp-bot/.env << EOF
# WhatsApp Bot Environment Configuration for LOCAL
LARAVEL_API_URL=http://127.0.0.1:8001
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
fi

print_status "Installing dependencies..."
cd whatsapp-bot
npm install

print_status "Starting WhatsApp bot..."

# Check if PM2 is available (recommended for production)
if command -v pm2 &> /dev/null; then
    print_status "Using PM2 to manage the bot process..."
    pm2 delete whatsapp-bot 2>/dev/null || true
    pm2 start index.js --name whatsapp-bot
    pm2 save
    print_success "WhatsApp bot started with PM2!"
    print_status "Use 'pm2 logs whatsapp-bot' to view logs"
    print_status "Use 'pm2 restart whatsapp-bot' to restart"
else
    print_warning "PM2 not found. Starting bot directly..."
    print_warning "For production, install PM2: npm install -g pm2"
    
    # Start the bot in background
    nohup node index.js > whatsapp-bot.log 2>&1 &
    BOT_PID=$!
    echo $BOT_PID > whatsapp-bot.pid
    
    print_success "WhatsApp bot started with PID: $BOT_PID"
    print_status "Logs are saved to: whatsapp-bot/whatsapp-bot.log"
    print_status "To stop: kill \$(cat whatsapp-bot.pid)"
fi

print_status "Waiting for bot to initialize..."
sleep 5

# Test the bot
print_status "Testing bot status..."
if curl -s http://localhost:3000/status > /dev/null; then
    print_success "Bot is responding to status requests!"
    
    STATUS=$(curl -s http://localhost:3000/status | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
    print_status "Bot status: $STATUS"
    
    if [ "$STATUS" = "Awaiting QR scan" ]; then
        print_success "QR code is being generated!"
        print_status "Visit your admin panel to scan the QR code"
    elif [ "$STATUS" = "Connected" ]; then
        print_success "WhatsApp bot is already connected!"
    fi
else
    print_error "Bot is not responding. Check the logs for errors."
    exit 1
fi

print_status "Testing QR generation..."
if curl -s http://localhost:3000/get-qr | grep -q "data:image/png;base64"; then
    print_success "QR code generation is working!"
else
    print_warning "QR code generation might not be working yet. Wait a few more seconds."
fi

echo ""
print_success "🎉 WhatsApp Bot Deployment Complete!"
echo ""
print_status "Next steps:"
echo "1. Visit your admin panel: /admin/whatsapp"
echo "2. Scan the QR code with your WhatsApp"
echo "3. Test sending a message"
echo ""
print_status "Useful commands:"
if command -v pm2 &> /dev/null; then
    echo "  - View logs: pm2 logs whatsapp-bot"
    echo "  - Restart: pm2 restart whatsapp-bot"
    echo "  - Stop: pm2 stop whatsapp-bot"
else
    echo "  - View logs: tail -f whatsapp-bot/whatsapp-bot.log"
    echo "  - Stop: kill \$(cat whatsapp-bot/whatsapp-bot.pid)"
fi
echo "  - Test status: curl http://localhost:3000/status"
echo "  - Test QR: curl http://localhost:3000/get-qr"
echo ""

