# LifeLine Blood Network - FREE Deployment Guide

Deploy the entire application at **zero cost** using free hosting providers.

---

## Best Free Option: InfinityFree

**Why InfinityFree?** Free PHP 8 + MySQL + custom domain + no ads + 99.9% uptime.

### Step 1: Create Account

1. Go to [infinityfree.com](https://www.infinityfree.com/)
2. Click **Sign Up** → enter email + password
3. Verify your email

### Step 2: Create a Hosting Account

1. Login to the [client area](https://www.infinityfree.com/login)
2. Click **Create New Account**
3. Choose a **subdomain** (e.g., `lifelineblood.epizy.com`) or connect your own domain
4. Select **InfinityFree** plan
5. Wait 1-5 minutes for activation

### Step 3: Get Your Database Credentials

1. In the client area, go to **MySQL Databases**
2. Click **Create New Database**
   - Database name: `epiz_xxxxxx_blood_donor_db` (note the prefix)
   - Username: create one (e.g., `epiz_xxxxxx_bloodapp`)
   - Password: set a strong password
3. **Write down** the database name, username, and password

### Step 4: Upload Your Files

**Option A: File Manager (Easiest)**

1. In the client area, click **File Manager** or go to [files.infinityfree.com](https://files.infinityfree.com)
2. Login with your hosting credentials
3. Navigate to `htdocs/` folder (this is your web root)
4. Delete default files (`default.html`, etc.)
5. Click **Upload** → upload the project ZIP
6. Right-click the ZIP → **Extract**
7. Move all files from the extracted folder into `htdocs/`

**Option B: FTP Upload**

1. Download [FileZilla](https://filezilla-project.org/) (free FTP client)
2. In client area, go to **FTP Accounts** → note the FTP credentials:
   - Host: `ftp.yourdomain.epizy.com`
   - Username: your FTP username
   - Password: your FTP password
3. Connect via FileZilla and upload all files to `htdocs/`

### Step 5: Import Database

1. In client area, click **phpMyAdmin** or go to [phpmyadmin.infinityfree.com](https://phpmyadmin.infinityfree.com)
2. Login with your MySQL username + password
3. Select your database on the left
4. Click **Import** tab
5. Choose file: upload `sql/setup.sql` from the project
6. Click **Go** → wait for import to complete

### Step 6: Configure .env

1. In File Manager, rename `.env.example` to `.env`
2. Edit `.env` with these values:

```env
DB_HOST=sql.infinityfree.com
DB_NAME=epiz_xxxxxx_blood_donor_db
DB_USER=epiz_xxxxxx_bloodapp
DB_PASS=your_database_password

APP_URL=http://lifelineblood.epizy.com
APP_ENV=production
APP_DEBUG=false

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="LifeLine Blood Network"

MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
```

**Important:** The `DB_HOST` for InfinityFree is usually `sql.infinityfree.com` (check your MySQL page for the exact host).

### Step 7: Test Your Site

Visit `http://lifelineblood.epizy.com` — your site should be live!

Login with default admin: `admin@bloodsystem.com` / `SecureAdmin2024!`  
**Change this password immediately after first login.**

---

## Alternative Free Hosts

### 000webhost (by Hostinger)

1. Go to [000webhost.com](https://www.000webhost.com/)
2. Sign up → create site → choose subdomain
3. Go to **Manage** → **File Manager** → upload files to `public_html/`
4. Go to **Manage** → **MySQL** → create database → import `setup.sql`
5. Edit `.env` with your DB credentials
6. **Limit:** 300MB storage, 3GB bandwidth/month, 1 MySQL database

### Byet.host

1. Go to [byet.host](https://byet.host/) → sign up
2. Same process as InfinityFree (same network)
3. Subdomain: `yourname.byethost.com`

### AwardSpace

1. Go to [awardspace.com](https://www.awardspace.com/) → free hosting
2. Sign up → create subdomain
3. Upload files via File Manager or FTP
4. Create MySQL database → import schema
5. **Limit:** 1GB storage, 1 MySQL DB

---

## Free Email Setup (Gmail SMTP)

Email is needed for password reset and notifications.

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification** (required for App Passwords)
3. Go to [App Passwords](https://myaccount.google.com/apppasswords)
4. Select **Other (Custom name)** → type "LifeLine"
5. Click **Generate** → copy the 16-character password
6. Use this as `MAIL_PASSWORD` in your `.env`

> **If you don't have Gmail**, you can use any free email provider. The app will still work — emails just won't send until SMTP is configured. In development mode, emails are logged instead.

---

## Free Domain Options

| Provider | Domain | Cost |
|----------|--------|------|
| InfinityFree subdomain | `yoursite.epizy.com` | Free |
| 000webhost subdomain | `yoursite.000webhostapp.com` | Free |
| Freenom | `yoursite.tk`, `.ml`, `.ga`, `.cf` | Free (unreliable) |
| GitHub Pages custom | Use your own | Free if you own it |

> **Best approach:** Use the free subdomain from your hosting provider. You can always add a custom domain later.

---

## Free Monitoring

### UptimeRobot (Free)
1. Sign up at [uptimerobot.com](https://uptimerobot.com/)
2. Add monitor → **HTTP(s)** → URL: `http://yoursite.epizy.com/health.php`
3. Interval: 5 minutes
4. You'll get email alerts if your site goes down

---

## Quick Deploy Checklist

```
1. ☐ Sign up at InfinityFree
2. ☐ Create hosting account (pick subdomain)
3. ☐ Create MySQL database
4. ☐ Upload project files to htdocs/
5. ☐ Import sql/setup.sql via phpMyAdmin
6. ☐ Rename .env.example → .env and configure
7. ☐ Visit your site URL
8. ☐ Login as admin → change default password
9. ☐ Set up Gmail App Password for email
10. ☐ Register a test donor + hospital account
11. ☐ Test password reset flow
12. ☐ Set up UptimeRobot monitoring
```

---

## Common Issues on Free Hosting

| Problem | Solution |
|---------|----------|
| **"Error establishing connection"** | Check `.env` DB_HOST — it's usually NOT `localhost` on free hosts. Check your hosting panel for the correct MySQL host. |
| **Blank white page** | Enable `APP_DEBUG=true` in `.env` temporarily to see errors, then turn off |
| **Upload too large** | Free hosts limit uploads to 5-10MB. Upload the ZIP and extract on server instead. |
| **Emails not sending** | Some free hosts block SMTP. Try port 465 with `MAIL_ENCRYPTION=ssl` instead of 587/tls. If blocked, the app still works — emails just get logged. |
| **Slow loading** | Free hosts have limited resources. Enable caching in `.htaccess` (already included). |
| **Session errors** | Add `session_save_path('/tmp');` at the top of `includes/db.php` if sessions don't work. |
| **Can't create .env** | Create it as `env.txt` locally, upload, then rename to `.env` in File Manager. |

---

## Total Cost: $0

| Component | Cost |
|-----------|------|
| Hosting (InfinityFree) | $0 |
| Domain (subdomain) | $0 |
| SSL (auto-enabled) | $0 |
| Email (Gmail SMTP) | $0 |
| Monitoring (UptimeRobot) | $0 |
| **Total** | **$0/month** |
