# Railway Email Setup Guide

## 🚨 Important: SMTP is NOT available on Railway Free Tier

Railway **disables SMTP** on free, trial, and hobby plans to prevent spam. You need a **Pro plan ($5/month)** to use SMTP.

## ✅ Solution: Use Brevo API (Free Alternative)

I've created an alternative email service that works on Railway's free tier using Brevo API (formerly Sendinblue).

### Step 1: Get Brevo API Key

1. Go to [brevo.com](https://brevo.com)
2. Sign up for a free account
3. Go to **SMTP & API** → **API Keys**
4. Create a new API key
5. Copy the API key

### Step 2: Set Environment Variables in Railway

In your Railway dashboard:

1. Go to your **web service** (not MySQL)
2. Click **Variables** tab
3. Add these environment variables:

```
BREVO_API_KEY=xkeys-your_api_key_here
FROM_EMAIL=noreply@yourdomain.com
FROM_NAME=Yakap Daycare Center
```

### Step 3: Verify Your Sender Email (Optional)

**Important**: For better deliverability, verify your sender email:

1. In Brevo dashboard, go to **Senders & IP** → **Senders**
2. Add your sender email address
3. Verify the email by clicking the verification link sent to your inbox
4. This improves email deliverability

### Step 4: Test the Setup

Run the test script:

```bash
php test_otp.php
```

## 🔧 Alternative Email Services

If you prefer other services, here are Railway-compatible options:

### Option 1: SendGrid (Free tier: 100 emails/day)

```env
SENDGRID_API_KEY=your_sendgrid_api_key
FROM_EMAIL=noreply@yourdomain.com
FROM_NAME=Yakap Daycare Center
```

### Option 2: Mailgun (Free tier: 5,000 emails/month)

```env
MAILGUN_API_KEY=your_mailgun_api_key
MAILGUN_DOMAIN=your_domain.com
FROM_EMAIL=noreply@yourdomain.com
FROM_NAME=Yakap Daycare Center
```

### Option 3: Postmark (Free tier: 100 emails/month)

```env
POSTMARK_API_KEY=your_postmark_api_key
FROM_EMAIL=noreply@yourdomain.com
FROM_NAME=Yakap Daycare Center
```

### Option 4: Resend (Free tier: 3,000 emails/month)

```env
RESEND_API_KEY=re_your_api_key_here
FROM_EMAIL=noreply@yourdomain.com
FROM_NAME=Yakap Daycare Center
```

## 📧 Current Implementation

The system now uses:

- **`email_service.php`** - New email service using Brevo API
- **`otp_service.php`** - Updated to use the new email service
- **Fallback logging** - If no API key is set, emails are logged instead

## 🚀 Deployment Steps

1. **Install dependencies**:

   ```bash
   composer install
   ```

2. **Set environment variables** in Railway dashboard

3. **Deploy your application**

4. **Test email functionality**

## 🔍 Troubleshooting

### "Email logged (no API key configured)"

- **Solution**: Set `BREVO_API_KEY` environment variable

### "Failed to send email"

- **Solution**:
  - Check your API key is correct
  - Verify your sender email with Brevo
  - Check Railway logs for detailed error messages

### "Sender not verified"

- **Solution**: Complete sender verification in Brevo dashboard

## 💰 Cost Comparison

| Service          | Free Tier             | Pro Plan        |
| ---------------- | --------------------- | --------------- |
| **Railway SMTP** | ❌ Not available      | ✅ $5/month     |
| **Brevo**        | ✅ 300 emails/day     | ✅ $25/month    |
| **Resend**       | ✅ 3,000 emails/month | ✅ $20/month    |
| **SendGrid**     | ✅ 100 emails/day     | ✅ $19.95/month |
| **Mailgun**      | ✅ 5,000 emails/month | ✅ $35/month    |

## 🎯 Recommendation

**Use Brevo** - It's free, reliable, and works perfectly with Railway's free tier! 300 emails per day is more than enough for most applications.

## 📁 Files Created/Modified

- ✅ `email_service.php` - New Brevo-based email service
- ✅ `otp_service.php` - Updated to use new email service
- ✅ `composer.json` - Added Guzzle HTTP client
- ✅ `test_otp.php` - Test script for email functionality

## 🚀 Next Steps

1. Set up Brevo account
2. Add environment variables to Railway
3. Deploy your application
4. Test OTP functionality
5. Enjoy working emails on Railway free tier! 🎉
