# LifeLine Blood Network - Deployment Guide

Complete guide to deploy the application to a production server.

---

## Option 1: VPS Deployment (Recommended - DigitalOcean, AWS EC2, Hetzner)

### Step 1: Provision a Server

**Minimum specs:** 1 vCPU, 1GB RAM, 25GB SSD  
**Recommended OS:** Ubuntu 22.04 LTS or 24.04 LTS

**Providers:**
- [DigitalOcean Droplet](https://www.digitalocean.com/) — $6/month
- [Hetzner Cloud](https://www.hetzner.com/cloud) — €3.29/month
- [AWS Lightsail](https://aws.amazon.com/lightsail/) — $5/month
- [Vultr](https://www.vultr.com/) — $5/month

### Step 2: Initial Server Setup

```bash
# SSH into your server
ssh root@YOUR_SERVER_IP

# Update system
apt update && apt upgrade -y

# Create a deploy user
adduser deploy
usermod -aG sudo deploy
su - deploy

# Install required software
sudo apt install -y apache2 mysql-server php8.2 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip unzip
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

### Step 3: Secure MySQL

```bash
sudo mysql_secure_installation
# Answer: Y to all prompts
# Set a strong root password
```

### Step 4: Clone & Configure the App

```bash
# Clone the repo
cd /var/www
sudo git clone https://github.com/HayreKhan750/LifeLine-Blood-Network.git bloodnetwork
sudo chown -R www-data:www-data bloodnetwork
cd bloodnetwork

# Create .env from example
cp .env.example .env
nano .env
```

**Edit `.env` with production values:**
```env
DB_HOST=localhost
DB_NAME=blood_donor_db
DB_USER=blood_app
DB_PASS=YOUR_STRONG_DB_PASSWORD

APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="LifeLine Blood Network"

MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
```

### Step 5: Create Database & User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE blood_donor_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'blood_app'@'localhost' IDENTIFIED BY 'YOUR_STRONG_DB_PASSWORD';
GRANT ALL PRIVILEGES ON blood_donor_db.* TO 'blood_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Import schema
mysql -u blood_app -p blood_donor_db < sql/setup.sql
```

### Step 6: Configure Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/bloodnetwork.conf
```

Paste:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/bloodnetwork

    <Directory /var/www/bloodnetwork>
        AllowOverride All
        Require all granted
    </Directory>

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Protect sensitive files
    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    ErrorDocument 404 /404.php
    ErrorDocument 500 /500.php

    ErrorLog ${APACHE_LOG_DIR}/bloodnetwork_error.log
    CustomLog ${APACHE_LOG_DIR}/bloodnetwork_access.log combined
</VirtualHost>
```

```bash
sudo a2dissite 000-default
sudo a2ensite bloodnetwork
sudo systemctl reload apache2
```

### Step 7: Set File Permissions

```bash
cd /var/www/bloodnetwork
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod 600 .env
```

### Step 8: SSL Certificate (HTTPS)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get free SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically, verify:
sudo certbot renew --dry-run
```

### Step 9: Change Default Admin Password

```bash
# Visit https://yourdomain.com/login.php
# Login with: admin@bloodsystem.com / SecureAdmin2024!
# Go to admin dashboard and change the password immediately
```

### Step 10: Configure Email (Gmail SMTP)

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification**
3. Go to [App Passwords](https://myaccount.google.com/apppasswords)
4. Generate a new app password (select "Mail" → "Other" → name it "LifeLine")
5. Copy the 16-character password
6. Paste it as `MAIL_PASSWORD` in your `.env` file

**Alternative SMTP providers:**
- **SendGrid** — 100 emails/day free
- **Mailgun** — 5,000 emails/month free for 3 months
- **Amazon SES** — $0.10 per 1,000 emails
- **Brevo (Sendinblue)** — 300 emails/day free

---

## Option 2: Shared Hosting (Hostinger, Namecheap, Bluehost)

### Step 1: Upload Files

1. Download the project ZIP from GitHub
2. Login to your hosting **cPanel**
3. Open **File Manager** → navigate to `public_html`
4. Upload and extract the ZIP
5. Move all files from the extracted folder to `public_html/`

### Step 2: Create Database

1. In cPanel, open **MySQL Databases**
2. Create a new database: `blood_donor_db`
3. Create a new user with a strong password
4. Add the user to the database with **ALL PRIVILEGES**
5. Open **phpMyAdmin** → select the database → Import → upload `sql/setup.sql`

### Step 3: Configure .env

1. In File Manager, rename `.env.example` to `.env`
2. Edit `.env` with your database credentials and domain
3. Set `APP_ENV=production` and `APP_DEBUG=false`

### Step 4: Set Permissions

In cPanel File Manager, set `.env` file permissions to **600** (readable only by owner).

### Step 5: SSL

In cPanel, look for **SSL/TLS** or **Let's Encrypt** and enable free SSL for your domain.

---

## Option 3: Docker Deployment

### Step 1: Create Dockerfile

```dockerfile
FROM php:8.2-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql zip

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod 600 /var/www/html/.env

EXPOSE 80
```

### Step 2: Create docker-compose.yml

```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./.env:/var/www/html/.env
    depends_on:
      - db
    restart: unless-stopped

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: blood_donor_db
      MYSQL_USER: blood_app
      MYSQL_PASSWORD: dbpassword
    volumes:
      - db_data:/var/lib/mysql
      - ./sql/setup.sql:/docker-entrypoint-initdb.d/setup.sql
    restart: unless-stopped

volumes:
  db_data:
```

### Step 3: Deploy

```bash
docker-compose up -d
docker-compose exec app php -r "echo 'App running';"
```

---

## Post-Deployment Checklist

- [ ] **Change admin password** — Login and change from default
- [ ] **Test registration** — Register a donor and hospital account
- [ ] **Test login** — Verify rate limiting works
- [ ] **Test password reset** — Request reset and check email
- [ ] **Test blood request** — Create a request as hospital
- [ ] **Test donor search** — Verify distance-based search works
- [ ] **Test email notifications** — Check welcome email arrives
- [ ] **Verify HTTPS** — Ensure SSL is active and redirects HTTP→HTTPS
- [ ] **Check security headers** — Visit [securityheaders.com](https://securityheaders.com)
- [ ] **Set up backups** — Schedule daily database backups
- [ ] **Configure monitoring** — Use `health.php` endpoint with UptimeRobot

---

## Database Backups

### Automated Daily Backup Script

```bash
sudo nano /var/www/bloodnetwork/backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/www/backups"
DATE=$(date +%Y-%m-%d_%H%M)
mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u blood_app -p'YOUR_DB_PASSWORD' blood_donor_db | gzip > "$BACKUP_DIR/blood_$DATE.sql.gz"

# Keep only last 30 days
find $BACKUP_DIR -name "blood_*.sql.gz" -mtime +30 -delete

echo "Backup created: blood_$DATE.sql.gz"
```

```bash
sudo chmod +x /var/www/bloodnetwork/backup.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# Add this line:
0 2 * * * /var/www/bloodnetwork/backup.sh >> /var/log/blood_backup.log 2>&1
```

---

## Monitoring Setup

### Free Monitoring with UptimeRobot

1. Sign up at [UptimeRobot](https://uptimerobot.com/) (free — 50 monitors)
2. Add a new monitor:
   - **Type:** HTTP(s)
   - **URL:** `https://yourdomain.com/health.php`
   - **Interval:** 5 minutes
3. You'll get email alerts if the server goes down

### Log Monitoring

```bash
# Watch error logs in real-time
sudo tail -f /var/log/apache2/bloodnetwork_error.log

# Check for PHP errors
sudo grep -i "error" /var/log/apache2/bloodnetwork_error.log | tail -20
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| **Blank page** | Check `error_log`, set `APP_DEBUG=true` temporarily |
| **Database connection error** | Verify `.env` DB credentials, check MySQL is running: `sudo systemctl status mysql` |
| **Emails not sending** | Verify SMTP credentials, check spam folder, test with `MAIL_PASSWORD` as Gmail App Password |
| **404 on pages** | Ensure `AllowOverride All` in Apache config and `mod_rewrite` is enabled |
| **Permission denied** | Run `sudo chown -R www-data:www-data /var/www/bloodnetwork` |
| **CSS not loading** | Check `APP_URL` in `.env` matches your actual domain |
| **CSRF token errors** | Clear browser cookies, check session save path is writable |
| **Rate limit triggering** | Wait 15 minutes or clear session cookies |

---

## Domain Setup

1. **Buy a domain** from Namecheap, GoDaddy, or Cloudflare Registrar (~$10/year)
2. **Point DNS to your server:**
   - Add an **A Record**: `@` → `YOUR_SERVER_IP`
   - Add an **A Record**: `www` → `YOUR_SERVER_IP`
3. Wait for DNS propagation (5 min - 48 hours)
4. Run Certbot for SSL after DNS is active

---

## Estimated Costs

| Component | Cost |
|-----------|------|
| VPS Server | $5-6/month |
| Domain Name | ~$10/year |
| SSL Certificate | Free (Let's Encrypt) |
| Email (Gmail SMTP) | Free |
| **Total** | **~$6/month** |
