# 🚀 Deployment Guide

Complete guide to deploying your omni-channel super app to production.

---

## 📋 **Pre-Deployment Checklist**

- [ ] Code pushed to GitHub
- [ ] Production server ready (Ubuntu 22.04 recommended)
- [ ] Domain name configured
- [ ] SSL certificate ready
- [ ] Database created (MySQL 8.0)
- [ ] Redis installed

---

## 🔧 **Step 1: Server Setup**

### **Install Dependencies**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
sudo apt install mysql-server

# Install Redis
sudo apt install redis-server

# Install Nginx
sudo apt install nginx
```

---

## 📦 **Step 2: Clone & Install**

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/AshrafGalal/mijra.git
cd mijra
sudo git checkout stage

# Set permissions
sudo chown -R www-data:www-data /var/www/mijra
sudo chmod -R 755 /var/www/mijra/storage
sudo chmod -R 755 /var/www/mijra/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

---

## ⚙️ **Step 3: Environment Configuration**

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Edit .env file
nano .env
```

**Required `.env` Settings:**
```env
APP_NAME="Mijra Omni-Channel"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourapp.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mijra_landlord
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# WhatsApp (configure after Meta approval)
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_ACCESS_TOKEN=
# ... add all platform credentials
```

---

## 🗄️ **Step 4: Database Migration**

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed

# Verify tables
php artisan db:show
```

**Should create 11 new tables:**
- conversations, messages, message_attachments
- conversation_notes, conversation_tags, conversation_tag
- conversation_assignments, message_status_updates
- campaign_messages, automated_replies, canned_responses
- conversation_transfers, sla_policies

---

## 🔒 **Step 5: Nginx Configuration**

Create: `/etc/nginx/sites-available/mijra`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourapp.com www.yourapp.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourapp.com www.yourapp.com;
    root /var/www/mijra/public;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/yourapp.crt;
    ssl_certificate_key /etc/ssl/private/yourapp.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/mijra /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## ⚙️ **Step 6: Queue Worker (Supervisor)**

Create: `/etc/supervisor/conf.d/mijra-worker.conf`

```ini
[program:mijra-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mijra/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/mijra/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mijra-worker:*
```

---

## 📡 **Step 7: Laravel Reverb (WebSocket)**

Create: `/etc/supervisor/conf.d/mijra-reverb.conf`

```ini
[program:mijra-reverb]
command=php /var/www/mijra/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/mijra/storage/logs/reverb.log
```

Start:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mijra-reverb
```

---

## 🎯 **Step 8: Configure Platforms**

Follow platform-specific guides:
- [WhatsApp Setup](../platforms/messaging/WHATSAPP_SETUP.md)
- [Facebook Setup](../platforms/messaging/FACEBOOK_SETUP.md)
- [Shopify Setup](../platforms/ecommerce/SHOPIFY_SETUP.md)
- [Salla Setup](../platforms/ecommerce/SALLA_SETUP.md)

---

## ✅ **Verification**

Test each component:

```bash
# Test application
curl https://yourapp.com/up

# Test API
curl https://yourapp.com/api/landlord/locales

# Test webhook (WhatsApp)
curl -X GET "https://yourapp.com/api/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=YOUR_TOKEN&hub.challenge=test123"

# Should return: test123
```

---

## 📊 **Monitoring**

### **Laravel Horizon**
Access at: `https://yourapp.com/horizon`
- Monitor queue jobs
- View failed jobs
- Check job metrics

### **Logs**
```bash
# Application logs
tail -f /var/www/mijra/storage/logs/laravel.log

# Worker logs
tail -f /var/www/mijra/storage/logs/worker.log

# Reverb logs
tail -f /var/www/mijra/storage/logs/reverb.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

---

## 🔐 **Security Hardening**

```bash
# Disable directory listing
sudo nano /etc/nginx/sites-available/mijra
# Add: autoindex off;

# Configure firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Set proper permissions
sudo chown -R www-data:www-data /var/www/mijra
sudo find /var/www/mijra -type f -exec chmod 644 {} \;
sudo find /var/www/mijra -type d -exec chmod 755 {} \;
```

---

## 🔄 **Automated Deployments**

Create deployment script: `deploy.sh`

```bash
#!/bin/bash

cd /var/www/mijra

# Enable maintenance mode
php artisan down

# Pull latest code
git pull origin stage

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart mijra-worker:*
sudo supervisorctl restart mijra-reverb

# Disable maintenance mode
php artisan up

echo "Deployment complete!"
```

Make executable:
```bash
chmod +x deploy.sh
```

---

## 🎉 **You're Live!**

Your omni-channel super app is now in production!

**Next:** [Configure Platforms](../platforms/)

---

**Need help?** See [Troubleshooting](../guides/TROUBLESHOOTING.md)

