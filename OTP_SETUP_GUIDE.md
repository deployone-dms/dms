# OTP Setup Guide for Yakap Daycare Center

## Overview
This guide will help you set up the OTP (One-Time Password) email functionality for the Yakap Daycare Center management system.

## Prerequisites
- PHP 8.1 or higher
- Composer installed
- SMTP email service (Gmail, Outlook, etc.)

## Installation Steps

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Variables Setup

Create a `.env` file in your project root with the following variables:

```env
# Database Configuration
DB_HOST=your_database_host
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
DB_DATABASE=your_database_name
DB_PORT=your_database_port

# SMTP Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_ENCRYPTION=tls
FROM_EMAIL=your_email@gmail.com
FROM_NAME=Yakap Daycare Center
```

### 3. Gmail Setup (Recommended)

If using Gmail, follow these steps:

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate an App Password**:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a new app password for "Mail"
   - Use this password in your `SMTP_PASSWORD` environment variable

3. **Update your .env file**:
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_gmail@gmail.com
SMTP_PASSWORD=your_16_character_app_password
SMTP_ENCRYPTION=tls
FROM_EMAIL=your_gmail@gmail.com
FROM_NAME=Yakap Daycare Center
```

### 4. Alternative Email Providers

#### Outlook/Hotmail
```env
SMTP_HOST=smtp-mail.outlook.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

#### Yahoo Mail
```env
SMTP_HOST=smtp.mail.yahoo.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
```

## Testing the Setup

### 1. Run the Test Script
```bash
php test_otp.php
```

### 2. Manual Testing
1. Navigate to your application
2. Try to register a new parent account
3. Check if OTP email is received
4. Verify the OTP code works

## Troubleshooting

### Common Issues

#### 1. "SMTP Error: Could not connect to SMTP host"
- **Solution**: Check your SMTP_HOST and SMTP_PORT settings
- **For Gmail**: Ensure you're using `smtp.gmail.com` and port `587`

#### 2. "Authentication failed"
- **Solution**: 
  - For Gmail: Use App Password instead of regular password
  - Check SMTP_USERNAME and SMTP_PASSWORD
  - Ensure 2FA is enabled for Gmail

#### 3. "Connection timed out"
- **Solution**: 
  - Check firewall settings
  - Try different SMTP ports (465 for SSL, 587 for TLS)
  - Contact your hosting provider about SMTP restrictions

#### 4. "SSL/TLS connection failed"
- **Solution**: 
  - Try changing SMTP_ENCRYPTION to 'ssl' and port to 465
  - Or use 'tls' with port 587

### Debug Mode

To enable debug mode, add this to your PHP code:
```php
$mail->SMTPDebug = 2; // Enable verbose debug output
$mail->Debugoutput = 'html';
```

## Security Notes

1. **Never commit .env files** to version control
2. **Use App Passwords** instead of main account passwords
3. **Regularly rotate** your email credentials
4. **Monitor** email sending limits

## Production Deployment

### Railway/Heroku
Set environment variables in your hosting platform:
```bash
# Example for Railway
railway variables set SMTP_HOST=smtp.gmail.com
railway variables set SMTP_PORT=587
railway variables set SMTP_USERNAME=your_email@gmail.com
railway variables set SMTP_PASSWORD=your_app_password
railway variables set SMTP_ENCRYPTION=tls
railway variables set FROM_EMAIL=your_email@gmail.com
railway variables set FROM_NAME=Yakap Daycare Center
```

### Vercel
Add environment variables in Vercel dashboard or vercel.json:
```json
{
  "env": {
    "SMTP_HOST": "smtp.gmail.com",
    "SMTP_PORT": "587",
    "SMTP_USERNAME": "your_email@gmail.com",
    "SMTP_PASSWORD": "your_app_password",
    "SMTP_ENCRYPTION": "tls",
    "FROM_EMAIL": "your_email@gmail.com",
    "FROM_NAME": "Yakap Daycare Center"
  }
}
```

## Support

If you encounter issues:
1. Check the error logs in your application
2. Verify all environment variables are set correctly
3. Test with a simple email client first
4. Contact your hosting provider for SMTP restrictions

## Files Modified
- `otp_service.php` - Fixed configuration key mismatches
- `otp_config.php` - Improved environment variable handling
- `test_otp.php` - Created test script for OTP functionality