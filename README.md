# LifeLine Blood Network

A comprehensive Community Blood Donor & Emergency Matching System that connects hospitals with voluntary blood donors to save lives in emergencies.

## Features

### For Donors
- **Registration & Profile Management** - Create and manage donor profiles with blood type, location, and availability
- **Password Security** - Strong password requirements and password reset via email
- **Request Notifications** - Receive email alerts when hospitals need your blood type
- **Donation Tracking** - Track your donation history and update availability status
- **Privacy Protection** - Contact details only shown to logged-in hospitals

### For Hospitals
- **Request Management** - Create and manage urgent blood requests
- **Smart Matching** - Automatic matching with compatible donors in your area
- **Match Status Tracking** - Track contacted, confirmed, and declined donors
- **Profile Management** - Manage hospital profile and contact information

### For Administrators
- **Full System Management** - Manage all donors, hospitals, and requests
- **Audit Logging** - Track all system activities for accountability
- **User Management** - Activate/deactivate accounts and edit records
- **Bulk Operations** - Export data and perform bulk actions

## Security Features

- ✅ Environment-based configuration (.env)
- ✅ CSRF protection on all forms
- ✅ Rate limiting on login (5 attempts per 15 minutes)
- ✅ Strong password requirements (8+ chars, mixed case, numbers, symbols)
- ✅ Secure session management with regeneration
- ✅ Input sanitization and validation
- ✅ SQL injection protection via prepared statements
- ✅ XSS protection via output escaping
- ✅ Secure headers (X-Frame-Options, CSP, etc.)
- ✅ .htaccess protection for sensitive directories

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite (or Nginx with equivalent)
- SMTP server for email notifications (Gmail, SendGrid, etc.)

## Installation

### 1. Clone or Download
```bash
git clone https://github.com/HayreKhan750/LifeLine-Blood-Network.git
cd LifeLine-Blood-Network
```

### 2. Database Setup
```bash
# Create the database and tables
mysql -u root -p < sql/setup.sql
```

Or use phpMyAdmin to import `sql/setup.sql`.

### 3. Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit .env with your settings
nano .env
```

Required configuration:
- Database credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`)
- Application URL (`APP_URL`)
- SMTP settings for email (`MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`)

### 4. Web Server Setup

#### Apache
Ensure `.htaccess` files are enabled and mod_rewrite is active:
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

#### PHP Development Server (for testing)
```bash
php -S 127.0.0.1:8080
```

### 5. Default Admin Access
- Email: `admin@bloodsystem.com`
- Password: `SecureAdmin2024!`

**⚠️ IMPORTANT: Change the admin password immediately after first login!**

## Configuration Reference

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | Database host | localhost |
| `DB_NAME` | Database name | blood_donor_db |
| `DB_USER` | Database username | root |
| `DB_PASS` | Database password | (empty) |
| `APP_URL` | Application base URL | http://localhost:8080 |
| `APP_ENV` | Environment (development/production) | development |
| `APP_DEBUG` | Enable debug mode | true |
| `MAIL_HOST` | SMTP server host | smtp.gmail.com |
| `MAIL_PORT` | SMTP port | 587 |
| `MAIL_USERNAME` | SMTP username | (empty) |
| `MAIL_PASSWORD` | SMTP password/app password | (empty) |
| `MAX_LOGIN_ATTEMPTS` | Failed login attempts before lockout | 5 |
| `LOGIN_LOCKOUT_MINUTES` | Lockout duration in minutes | 15 |

### Email Setup (Gmail)
1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password at https://myaccount.google.com/apppasswords
3. Use the 16-character app password in `MAIL_PASSWORD`
4. Set `MAIL_USERNAME` to your Gmail address

## Project Structure

```
.
├── .env                    # Environment configuration (not in git)
├── .env.example            # Example configuration
├── .htaccess              # Apache security configuration
├── .gitignore            # Git ignore rules
├── README.md             # This file
├── admin/                # Admin panel pages
│   ├── dashboard.php
│   ├── manage_donors.php
│   ├── manage_hospitals.php
│   ├── manage_requests.php
│   ├── edit_record.php
│   └── delete_record.php
├── donor/                # Donor dashboard pages
│   ├── dashboard.php
│   └── edit_profile.php
├── hospital/             # Hospital dashboard pages
│   ├── dashboard.php
│   ├── edit_profile.php
│   ├── create_request.php
│   └── request_matches.php
├── includes/             # Core includes (protected by .htaccess)
│   ├── config.php       # Configuration loader
│   ├── db.php           # Database connection
│   ├── functions.php    # Helper functions
│   ├── header.php       # Page header template
│   ├── footer.php       # Page footer template
│   └── email_service.php # Email handling
├── assets/              # Public assets
│   └── css/
│       └── style.css    # Main stylesheet
├── sql/                 # Database schemas (protected by .htaccess)
│   └── setup.sql        # Database setup
├── index.php            # Public homepage
├── login.php            # User login
├── logout.php           # User logout
├── register.php         # User registration
├── forgot_password.php  # Password reset request
├── reset_password.php   # Password reset confirmation
├── find_donors.php      # Public donor search
└── view_request.php     # View blood request details
```

## Usage

### For Donors
1. Register as a donor with your blood type and location
2. Keep your availability status updated
3. You'll receive email notifications for matching requests
4. Update your last donation date after each donation

### For Hospitals
1. Register your hospital with license information
2. Create blood requests specifying patient blood type and urgency
3. View compatible donors and contact them directly
4. Track match status (contacted, confirmed, declined)

### For Administrators
1. Login with admin credentials
2. Manage all users (donors, hospitals)
3. Oversee blood requests
4. View system statistics on the dashboard

## Production Deployment

### Security Checklist
- [ ] Change default admin password
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure SMTP for email notifications
- [ ] Enable HTTPS and set `session.cookie_secure=1`
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Remove or protect any development/test files
- [ ] Configure server-level rate limiting
- [ ] Set up database backups
- [ ] Configure log rotation

### Performance Optimization
- [ ] Enable OPcache for PHP
- [ ] Configure MySQL query cache
- [ ] Use a CDN for static assets (optional)
- [ ] Enable Apache/Nginx gzip compression
- [ ] Set up Redis/Memcached for session storage (optional)

## Troubleshooting

### Database Connection Issues
Check your `.env` file database credentials and ensure MySQL is running:
```bash
sudo service mysql status
```

### Email Not Sending
- Verify SMTP credentials in `.env`
- Check spam folders
- Enable debug mode to see error logs
- For Gmail, use App Password instead of account password

### 404 Errors on Subdirectories
Ensure Apache `mod_rewrite` is enabled and `.htaccess` files are being read.

### Session Issues
Check PHP session configuration and ensure the session save path is writable.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open source. Please add a LICENSE file with your preferred license (MIT, GPL, etc.).

## Support

For support, email support@lifelineblood.network or create an issue on GitHub.

## Acknowledgments

- Thank you to all blood donors who save lives every day
- Thanks to hospitals and healthcare workers
- Built with passion for community service

---

**Made with ❤️ for saving lives**
