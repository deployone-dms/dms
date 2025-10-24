# Deployment Guide for Daycare Management System

## Quick Deployment Options

### 1. Heroku (Recommended - Easiest)

#### Prerequisites:

- Heroku CLI installed
- Git repository

#### Steps:

1. **Create Heroku App:**

   ```bash
   heroku create your-app-name
   ```

2. **Add MySQL Database:**

   ```bash
   heroku addons:create jawsdb:kitefin
   ```

3. **Set Environment Variables:**

   ```bash
   heroku config:set DB_HOST=your-jawsdb-host
   heroku config:set DB_USERNAME=your-username
   heroku config:set DB_PASSWORD=your-password
   heroku config:set DB_DATABASE=your-database
   ```

4. **Deploy:**

   ```bash
   git add .
   git commit -m "Deploy to Heroku"
   git push heroku main
   ```

5. **Run Database Setup:**
   ```bash
   heroku run php setup_database.php
   ```

### 2. Railway

1. Connect your GitHub repository
2. Add MySQL service
3. Set environment variables
4. Deploy automatically

### 3. DigitalOcean App Platform

1. Connect repository
2. Choose PHP buildpack
3. Add MySQL database
4. Configure environment variables

### 4. Traditional VPS (DigitalOcean, Linode, Vultr)

1. Set up Ubuntu server
2. Install Apache/Nginx + PHP + MySQL
3. Upload files via SFTP/SCP
4. Configure database
5. Set up domain and SSL

## Environment Variables Required:

- `DB_HOST`: Database host
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `DB_DATABASE`: Database name
- `DB_PORT`: Database port (usually 3306)

## Database Setup:

After deployment, run the database setup script to create tables and initial data.
