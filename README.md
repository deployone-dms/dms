# Yakap Daycare Management System

A comprehensive web-based management system for Early Childhood Care & Development (ECCD) centers.

## Features

- 👥 **Student Enrollment Management** - Track and manage student enrollments
- 📊 **Progress Assessment** - Monitor child development milestones
- 📅 **Attendance Tracking** - Daily attendance records
- 👨‍🏫 **Teacher Management** - Maintain teacher profiles and assignments
- 👪 **Parent Portal** - Parents can view their children's progress
- 🔐 **Secure Authentication** - Role-based access control with OTP verification
- 📄 **Document Management** - Upload and manage student documents

## Tech Stack

- **Backend:** PHP 8.0+
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Email:** PHPMailer for OTP delivery
- **Server:** Apache (XAMPP)

## Installation

### Prerequisites

- XAMPP (or any Apache + MySQL + PHP environment)
- PHP 8.0 or higher
- MySQL/MariaDB
- Composer (for PHPMailer dependencies)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/daycare-management-system.git
   cd daycare-management-system
   ```

2. **Move to XAMPP directory**
   ```bash
   # Copy the project to your XAMPP htdocs folder
   # Example: C:\xampp\htdocs\DMS
   ```

3. **Install dependencies**
   ```bash
   composer install
   ```

4. **Create database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `daycare_db`
   - Import the SQL file: `daycare_db.sql`

5. **Configure database connection**
   - Copy `connection.php.example` to `connection.php`
   - Update database credentials:
     ```php
     $Connection = new mysqli("localhost", "root", "", "daycare_db", 3306);
     ```

6. **Run database setup**
   - Navigate to: `http://localhost/DMS/setup_database.php`
   - This will create all required tables

7. **Default Login**
   - Email: `admin@yakapdaycare.com`
   - Password: `admin123`
   - **⚠️ Change the default password after first login!**

## Project Structure

```
DMS/
├── landing.php              # Login page
├── dashboard.php            # Admin dashboard
├── parent_dashboard.php     # Parent portal
├── index.php               # Student list
├── add_stud.php            # Student enrollment form
├── teachers_clean.php      # Teacher management
├── progress_*.php          # Progress assessment modules
├── attendance_clean.php    # Attendance tracking
├── setup_database.php      # Database setup script
├── vendor/                 # Composer dependencies
└── uploads/               # Uploaded documents
```

## Security Notes

⚠️ **Important Security Considerations:**

1. **Change default credentials** immediately after installation
2. **Never commit** `connection.php` or `db.php` with real credentials
3. **Use environment variables** for sensitive data in production
4. **Enable HTTPS** in production environments
5. **Regularly update** dependencies and PHP version
6. **Backup database** regularly

## Configuration

### Email Setup (for OTP)

Update the SMTP settings in `landing.php`:

```php
$mail->Host       = 'smtp.gmail.com';
$mail->Username   = 'your-email@gmail.com';
$mail->Password   = 'your-app-password';
```

## User Roles

1. **Admin (Type 1)** - Full system access
2. **User (Type 2)** - Limited access
3. **Supervisor (Type 3)** - Supervisory access
4. **Staff (Type 4)** - Staff-level access

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues and questions, please open an issue on GitHub.

## Acknowledgments

- Built for Yakap Daycare Center
- Designed for ECCD assessment and management

---

**Note:** This is a development version. Please ensure proper security measures before deploying to production.
