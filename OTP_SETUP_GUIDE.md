# OTP Setup Guide for Yakap Daycare Management System

## 📧 Email Configuration

### Step 1: Configure Email Settings
Edit the file `otp_config.php` and update the following settings:

```php
'smtp' => [
    'host' => 'smtp.gmail.com', // Your SMTP server
    'port' => 587,
    'username' => 'your-email@gmail.com', // Your email address
    'password' => 'your-app-password', // Your app password
    'encryption' => 'tls'
],
'from' => [
    'email' => 'your-email@gmail.com', // Your email address
    'name' => 'Yakap Daycare Center'
]
```

### Step 2: Gmail Setup (Recommended)

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password**:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a new app password for "Mail"
   - Use this password in `otp_config.php`

### Step 3: Alternative Email Providers

#### Outlook/Hotmail:
```php
'host' => 'smtp-mail.outlook.com',
'port' => 587,
'encryption' => 'tls'
```

#### Yahoo:
```php
'host' => 'smtp.mail.yahoo.com',
'port' => 587,
'encryption' => 'tls'
```

#### Custom SMTP:
```php
'host' => 'your-smtp-server.com',
'port' => 587, // or 465 for SSL
'encryption' => 'tls' // or 'ssl'
```

## 🔧 System Configuration

### Step 4: Database Setup
The OTP system will automatically create the required table `otp_verification` when first accessed.

### Step 5: Test the System
1. Go to `test.php`
2. Enter a registered email address
3. Click "Send OTP"
4. Check your email for the OTP code
5. Enter the OTP in the verification page
6. Complete login with your password

## 📱 How It Works

### Login Flow:
1. **Step 1**: User enters email → System sends OTP
2. **Step 2**: User verifies OTP → Redirected to password entry
3. **Step 3**: User enters password → Login successful

### Security Features:
- ✅ OTP expires in 5 minutes
- ✅ Maximum 3 verification attempts
- ✅ Previous OTPs are invalidated when new one is sent
- ✅ Email validation before sending OTP
- ✅ Session-based verification tracking

## 🛠️ Troubleshooting

### Common Issues:

#### 1. "Failed to send OTP" Error
- Check email credentials in `otp_config.php`
- Verify SMTP settings
- Check if 2FA is enabled for Gmail
- Ensure app password is correct

#### 2. OTP Not Received
- Check spam/junk folder
- Verify email address is correct
- Check SMTP server status
- Try different email provider

#### 3. "Invalid or expired OTP" Error
- Check if OTP is still valid (5 minutes)
- Verify correct 6-digit code
- Check if maximum attempts exceeded

### Debug Mode:
Add this to `otp_service.php` for debugging:
```php
$mail->SMTPDebug = 2; // Enable verbose debug output
```

## 📋 Files Created/Modified

### New Files:
- `otp_config.php` - Email configuration
- `otp_service.php` - OTP functionality
- `otp_verification.php` - OTP verification page
- `vendor/` - PHPMailer library (via Composer)

### Modified Files:
- `test.php` - Updated with OTP integration

## 🔒 Security Notes

1. **Never commit** `otp_config.php` to version control
2. **Use environment variables** for production
3. **Regularly rotate** email passwords
4. **Monitor** OTP usage for suspicious activity
5. **Set up rate limiting** for OTP requests

## 📞 Support

If you encounter issues:
1. Check the error messages in the browser
2. Check PHP error logs
3. Verify email configuration
4. Test with a simple email first

## 🎯 Next Steps

1. Configure your email settings
2. Test the OTP system
3. Customize the email template if needed
4. Add rate limiting for production use
5. Consider adding SMS OTP as backup

---

**Note**: This OTP system is now fully integrated with your existing login system. Users must verify their email before they can complete the login process.


















