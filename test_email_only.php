<?php
require_once 'email_service.php';

// Test email service without database
try {
    $emailService = new EmailService();
    
    // Test email (replace with your test email)
    $testEmail = 'test@example.com';
    
    echo "<h2>Testing Brevo Email Service</h2>";
    echo "<p>Testing with email: $testEmail</p>";
    
    // Test sending OTP email
    echo "<h3>Testing Email Send:</h3>";
    $result = $emailService->sendOTP($testEmail, '123456');
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ " . $result['message'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ " . $result['message'] . "</p>";
    }
    
    // Display current configuration
    echo "<h3>Current Configuration:</h3>";
    echo "<pre>";
    echo "Email Service: Brevo API\n";
    echo "Brevo API Key: " . (getenv('BREVO_API_KEY') ? 'Set ✅' : 'Not set ❌') . "\n";
    echo "From Email: " . (getenv('FROM_EMAIL') ?: 'jheyjheypogi30@gmail.com') . "\n";
    echo "From Name: " . (getenv('FROM_NAME') ?: 'Yakap Daycare Center') . "\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
