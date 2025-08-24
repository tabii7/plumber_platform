#!/bin/bash

# 🚀 WhatsApp Bot Proxy Setup Script
# This script sets up Nginx reverse proxy for WhatsApp bot

echo "🚀 Setting up WhatsApp Bot Proxy..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

DOMAIN="plumber.easyred.com"

print_status "Creating Nginx configuration for $DOMAIN..."

# Create Nginx configuration
cat > /etc/nginx/sites-available/whatsapp-bot << EOF
server {
    listen 80;
    server_name $DOMAIN;

    # Main Laravel app
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    # WhatsApp bot proxy
    location /whatsapp-bot/ {
        proxy_pass http://127.0.0.1:3000/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_cache_bypass \$http_upgrade;
    }
}
EOF

print_status "Enabling Nginx site..."

# Enable the site
ln -sf /etc/nginx/sites-available/whatsapp-bot /etc/nginx/sites-enabled/

print_status "Testing Nginx configuration..."

# Test Nginx configuration
if nginx -t; then
    print_success "Nginx configuration is valid"
else
    print_error "Nginx configuration is invalid"
    exit 1
fi

print_status "Reloading Nginx..."

# Reload Nginx
systemctl reload nginx

if [ $? -eq 0 ]; then
    print_success "Nginx reloaded successfully"
else
    print_error "Failed to reload Nginx"
    exit 1
fi

print_success "WhatsApp bot proxy setup complete!"
print_status "Your WhatsApp bot will be available at: https://$DOMAIN/whatsapp-bot/"
print_status "Make sure to update your .env file with:"
print_warning "WHATSAPP_BOT_URL=https://$DOMAIN/whatsapp-bot"

