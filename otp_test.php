<?php
// OTP Test Script
include 'db.php';
include 'otp_service.php';

echo "<h2>OTP Service Test</h2>";

try {
    // Test database connection
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
        exit;
    }
    
    // Test OTP service initialization
    $otpService = new OTPService($conn);
    echo "<p style='color: green;'>✅ OTP Service initialized successfully</p>";
    
    // Test OTP generation
    $otp = $otpService->generateOTP();
    echo "<p style='color: green;'>✅ OTP generated: " . $otp . "</p>";
    
    // Test configuration
    $config = include 'otp_config.php';
    echo "<p style='color: green;'>✅ Configuration loaded successfully</p>";
    echo "<pre>Config: " . print_r($config, true) . "</pre>";
    
    // Check if PHPMailer is available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color: green;'>✅ PHPMailer is available</p>";
    } else {
        echo "<p style='color: red;'>❌ PHPMailer is not available</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
