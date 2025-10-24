<?php
require_once 'connection.php';
require_once 'otp_service.php';

// Test OTP functionality
try {
    $otpService = new OTPService($Connection);
    
    // Test email (replace with your test email)
    $testEmail = 'test@example.com';
    
    echo "<h2>Testing OTP Service</h2>";
    echo "<p>Testing with email: $testEmail</p>";
    
    // Test OTP generation
    $otp = $otpService->generateOTP();
    echo "<p>Generated OTP: $otp</p>";
    
    // Test sending OTP
    echo "<h3>Testing OTP Send:</h3>";
    $result = $otpService->sendOTP($testEmail);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ " . $result['message'] . "</p>";
        
        // Test OTP verification
        echo "<h3>Testing OTP Verification:</h3>";
        $verifyResult = $otpService->verifyOTP($testEmail, $otp);
        
        if ($verifyResult['success']) {
            echo "<p style='color: green;'>✅ " . $verifyResult['message'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ " . $verifyResult['message'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ " . $result['message'] . "</p>";
    }
    
    // Display current configuration
    echo "<h3>Current Configuration:</h3>";
    $config = include 'otp_config.php';
    echo "<pre>";
    echo "SMTP Host: " . $config['email']['smtp_host'] . "\n";
    echo "SMTP Port: " . $config['email']['smtp_port'] . "\n";
    echo "SMTP Username: " . $config['email']['smtp_username'] . "\n";
    echo "SMTP Encryption: " . $config['email']['smtp_encryption'] . "\n";
    echo "From Email: " . $config['email']['from_email'] . "\n";
    echo "From Name: " . $config['email']['from_name'] . "\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
