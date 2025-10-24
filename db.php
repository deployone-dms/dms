<?php 
    // Database Configuration for Railway/Heroku
    // Uses environment variables for production deployment
    
    // Check for Railway's MySQL environment variables
    $host = getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost';
    $username = getenv('MYSQL_USER') ?: getenv('DB_USERNAME') ?: 'root';
    $password = getenv('MYSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '';
    $database = getenv('MYSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'daycare_db';
    $port = getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306;
    
    // Try DATABASE_URL if individual variables are not set
    $database_url = getenv('DATABASE_URL');
    if ($database_url && ($host === 'localhost' || $username === 'root')) {
        $url = parse_url($database_url);
        if ($url && isset($url['host'])) {
            $host = $url['host'];
            $username = $url['user'] ?? 'root';
            $password = $url['pass'] ?? '';
            $database = ltrim($url['path'] ?? '/daycare_db', '/');
            $port = isset($url['port']) ? $url['port'] : 3306;
        }
    }
    
    $conn = new mysqli($host, $username, $password, $database, $port);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
