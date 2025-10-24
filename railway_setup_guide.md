# Railway Environment Variables Setup Guide

## The Problem

Railway should automatically provide database environment variables, but they're not being detected by your PHP application.

## Solution: Manual Environment Variable Setup

### Step 1: Go to Railway Dashboard

1. Open your Railway project dashboard
2. Click on your **web service** (not the MySQL service)
3. Go to the **Variables** tab

### Step 2: Add Environment Variables

Add these environment variables manually:

```
MYSQL_HOST=maglev.proxy.rlwy.net
MYSQL_USER=root
MYSQL_PASSWORD=nKTmUIKDoUruBImSWBzVxtrahKJMMDtQ
MYSQL_DATABASE=daycare_db
MYSQL_PORT=20122
```

### Step 3: Redeploy

1. After adding the variables, Railway will automatically redeploy
2. Wait for the deployment to complete
3. Test your application

## Alternative: Use Railway CLI

If you prefer command line:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Link to your project
railway link

# Set environment variables
railway variables set MYSQL_HOST=maglev.proxy.rlwy.net
railway variables set MYSQL_USER=root
railway variables set MYSQL_PASSWORD=nKTmUIKDoUruBImSWBzVxtrahKJMMDtQ
railway variables set MYSQL_DATABASE=daycare_db
railway variables set MYSQL_PORT=20122
```

## Why This Happens

Railway sometimes doesn't automatically expose MySQL connection details to your application. Setting these variables manually ensures your PHP app can connect to the database.

## After Setup

1. Visit your app URL
2. Go to `/setup.php`
3. Click "Setup Database"
4. Should work without errors!

## Default Admin Login

- Email: `admin@yakapdaycare.com`
- Password: `admin123`
