#!/bin/bash

DOMAIN="crm.barmagiat.com"
EMAIL="tools@mijra.io"

echo "🔐 SSL Setup for $DOMAIN"

# Create directories
mkdir -p ./letsencrypt/www
mkdir -p ./letsencrypt/live/$DOMAIN

# Function to create dummy SSL certificates (needed for nginx to start)
create_dummy_certs() {
    echo "🔧 Creating temporary SSL certificates..."
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
        -keyout "./letsencrypt/live/$DOMAIN/privkey.pem" \
        -out "./letsencrypt/live/$DOMAIN/fullchain.pem" \
        -subj "/CN=$DOMAIN"
    echo "✅ Temporary certificates created"
}

# Function to get real SSL certificates
get_real_certs() {
    echo "🚀 Getting real SSL certificates..."

    # Remove temporary certificates
    rm -rf "./letsencrypt/live/$DOMAIN"

    # Get real certificates
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml run --rm certbot \
        certonly --webroot \
        --webroot-path=/var/www/certbot \
        --email $EMAIL \
        --agree-tos \
        --no-eff-email \
        --force-renewal \
        -d $DOMAIN

    if [ $? -eq 0 ]; then
        echo "✅ Real certificates obtained!"
        echo "🔍 Checking certificate files..."

        # Find the actual certificate directory
        CERT_DIR=$(ls -1 "./letsencrypt/live/" | grep "$DOMAIN" | head -1)
        if [ -n "$CERT_DIR" ]; then
            echo "📁 Certificate directory: $CERT_DIR"
            ls -la "./letsencrypt/live/$CERT_DIR/"

            # Check if nginx config matches
            if grep -q "$CERT_DIR" "./docker/nginx/default.prod.conf"; then
                echo "✅ Nginx config matches certificate directory"
            else
                echo "⚠️  WARNING: Update nginx config to use: /etc/letsencrypt/live/$CERT_DIR/"
            fi
        fi

        echo "🔄 Reloading nginx..."
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec nginx nginx -s reload
    else
        echo "❌ Failed to get certificates"
    fi
}

# Function to renew certificates
renew_certs() {
    echo "🔄 Renewing certificates..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml run --rm certbot certbot renew
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec nginx nginx -s reload
}

# Main command handling
case "$1" in
    "setup")
        echo "🏁 Initial SSL setup starting..."
        create_dummy_certs
        echo "🚀 Starting services..."
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
        sleep 10
        get_real_certs
        ;;
    "renew")
        renew_certs
        ;;
    "debug")
        echo "🔍 Debugging certificate setup..."
        echo "Host certificates:"
        ls -la "./letsencrypt/live/$DOMAIN/" 2>/dev/null || echo "No certificates on host"
        echo ""
        echo "Nginx container certificates:"
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec nginx ls -la "/etc/letsencrypt/live/$DOMAIN/" 2>/dev/null || echo "No certificates in nginx container"
        echo ""
        echo "Certbot container certificates:"
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml run --rm certbot ls -la "/etc/letsencrypt/live/$DOMAIN/" 2>/dev/null || echo "No certificates in certbot container"
        ;;
    *)
        echo "Usage: $0 {setup|renew|debug}"
        echo ""
        echo "  setup  - First time SSL setup"
        echo "  renew  - Renew existing certificates"
        echo "  debug  - Check certificate status"
        ;;
esac
