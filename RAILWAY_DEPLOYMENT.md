# Railway Deployment Guide

## 🚀 Deploy Your Daycare Management System to Railway

Railway is perfect for your PHP project! Here's how to deploy it:

### Prerequisites

- GitHub account
- Railway account (free at railway.app)

### Step 1: Prepare Your Repository

1. Push your code to GitHub
2. Make sure all files are committed:
   ```bash
   git add .
   git commit -m "Prepare for Railway deployment"
   git push origin main
   ```

### Step 2: Deploy to Railway

#### Option A: Deploy from GitHub (Recommended)

1. Go to [railway.app](https://railway.app)
2. Sign in with GitHub
3. Click "New Project"
4. Select "Deploy from GitHub repo"
5. Choose your repository
6. Railway will automatically detect it's a PHP project

#### Option B: Deploy with Railway CLI

1. Install Railway CLI:
   ```bash
   npm install -g @railway/cli
   ```
2. Login to Railway:
   ```bash
   railway login
   ```
3. Initialize project:
   ```bash
   railway init
   ```
4. Deploy:
   ```bash
   railway up
   ```

### Step 3: Add MySQL Database

1. In your Railway project dashboard
2. Click "New" → "Database" → "MySQL"
3. Railway will automatically create a MySQL database
4. The `DATABASE_URL` environment variable will be set automatically

### Step 4: Configure Environment Variables

Railway automatically sets these from your database:

- `DATABASE_URL` (automatically set)
- `MYSQL_URL` (automatically set)

### Step 5: Set Up Database Tables

1. Go to your project's "Variables" tab
2. Add a custom domain if needed
3. Deploy your project
4. Once deployed, you can run database setup scripts

### Step 6: Access Your Application

1. Railway will provide a URL like: `https://your-app-name.railway.app`
2. Your app will be live and accessible!

## 🔧 Configuration Files Created

Your project now includes:

- `railway.json` - Railway-specific configuration
- `Procfile` - Process definition
- `nixpacks.toml` - Build configuration
- Updated `db.php` and `connection.php` - Database connection with Railway support

## 💰 Pricing

- **Free Tier**: $5 credit monthly (usually enough for small apps)
- **Pro Plan**: $5/month per service
- **Database**: Included in free tier

## 🚨 Important Notes

1. **Database Setup**: After deployment, you'll need to run your database setup script
2. **Environment Variables**: Railway automatically handles database connection
3. **Custom Domain**: Available on paid plans
4. **SSL**: Automatically provided by Railway

## 🔍 Troubleshooting

### Common Issues:

1. **Build Fails**: Check that all PHP extensions are available
2. **Database Connection**: Ensure `DATABASE_URL` is set
3. **File Permissions**: Railway handles this automatically

### Getting Help:

- Railway Documentation: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Railway Support: Available in dashboard

## 🎉 Success!

Your Daycare Management System is now live on Railway with:

- ✅ Automatic deployments from GitHub
- ✅ Managed MySQL database
- ✅ SSL certificate
- ✅ Global CDN
- ✅ Environment variable management
