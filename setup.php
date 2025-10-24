<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Daycare Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            color: #0c5460;
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Setup</h1>
        <p>This page will automatically set up your database tables and initial data.</p>
        
        <?php
        include 'auto_setup_database.php';
        
        if (isset($_GET['setup'])) {
            echo "<div class='info'>Setting up database...</div>";
            
            if (setupDatabase()) {
                echo "<div class='success'>";
                echo "<h3>✅ Database Setup Complete!</h3>";
                echo "<p>Your database has been successfully set up with all required tables.</p>";
                echo "<p><strong>Default Admin Login:</strong></p>";
                echo "<ul>";
                echo "<li>Email: admin@yakapdaycare.com</li>";
                echo "<li>Password: admin123</li>";
                echo "</ul>";
                echo "<p>You can now use your Daycare Management System!</p>";
                echo "</div>";
                
                echo "<a href='index.php' class='btn'>Go to Main Application</a>";
            } else {
                echo "<div class='error'>";
                echo "<h3>❌ Database Setup Failed</h3>";
                echo "<p>There was an error setting up the database. Please check:</p>";
                echo "<ul>";
                echo "<li>Database connection is working</li>";
                echo "<li>Database user has proper permissions</li>";
                echo "<li>All required files are present</li>";
                echo "</ul>";
                echo "</div>";
            }
        } else {
            echo "<div class='info'>";
            echo "<p>Click the button below to set up your database automatically.</p>";
            echo "<p>This will create all necessary tables and insert initial data.</p>";
            echo "</div>";
            
            echo "<a href='?setup=1' class='btn'>Setup Database</a>";
        }
        ?>
        
        <hr>
        <h3>Environment Information</h3>
        <p><strong>Database Host:</strong> <?php echo getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'Not set'; ?></p>
        <p><strong>Database Name:</strong> <?php echo getenv('DB_DATABASE') ?: getenv('MYSQL_DATABASE') ?: 'Not set'; ?></p>
        <p><strong>Database URL:</strong> <?php echo getenv('DATABASE_URL') ? 'Set' : 'Not set'; ?></p>
    </div>
</body>
</html>
