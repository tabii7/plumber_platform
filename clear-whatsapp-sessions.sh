#!/bin/bash

# 🧹 WhatsApp Bot Session Cleaner
# This script clears all authentication sessions and restarts the bot

echo "🧹 Clearing WhatsApp Bot Sessions..."

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
pkill -f "node.*whatsapp-bot" 2>/dev/null
pkill -f "node.*index.js" 2>/dev/null
sleep 2

print_status "Clearing authentication sessions..."
rm -rf whatsapp-bot/auth_info 2>/dev/null
rm -rf whatsapp-bot/.wwebjs_auth 2>/dev/null
rm -rf whatsapp-bot/sessions 2>/dev/null
rm -rf whatsapp-bot/store 2>/dev/null

# Remove any auth-related JSON files
find whatsapp-bot -name "*.json" -type f 2>/dev/null | grep -E "(auth|session|store)" | xargs rm -f 2>/dev/null

print_success "Authentication sessions cleared!"

print_status "Clearing Laravel cache..."
php artisan cache:clear >/dev/null 2>&1
php artisan config:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1

print_success "Laravel cache cleared!"

print_status "Starting WhatsApp bot..."
cd whatsapp-bot
nohup node index.js > whatsapp-bot.log 2>&1 &
BOT_PID=$!

sleep 3

# Check if bot started successfully
if ps -p $BOT_PID > /dev/null; then
    print_success "WhatsApp bot started successfully (PID: $BOT_PID)"
    print_status "Check the QR code at: http://localhost:3000/get-qr"
    print_status "Or visit your admin panel: /admin/whatsapp"
else
    print_error "Failed to start WhatsApp bot"
    exit 1
fi

echo ""
print_success "🎉 WhatsApp bot is ready for fresh authentication!"
echo ""
echo "📱 Next steps:"
echo "1. Visit your admin panel: /admin/whatsapp"
echo "2. Scan the QR code with your WhatsApp"
echo "3. The bot will be ready to handle messages"
echo ""
echo "📋 Useful commands:"
echo "• Check bot status: curl http://localhost:3000/status"
echo "• View logs: tail -f whatsapp-bot/whatsapp-bot.log"
echo "• Stop bot: pkill -f 'node.*whatsapp-bot'"
echo "• Clear sessions (Laravel): php artisan whatsapp:clear-sessions"
echo ""
echo "🔧 Session Management:"
echo "• Use 'Clear Sessions' button in admin panel for quick cleanup"
echo "• Use 'Disconnect Session' button for full disconnection"
echo "• Use this script for complete restart with cleanup"

