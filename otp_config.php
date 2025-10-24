<?php
// OTP Configuration
return [
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
        'max_attempts' => 3
    ],
    'email' => [
        'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'smtp_port' => (int)(getenv('SMTP_PORT') ?: 587),
        'smtp_username' => getenv('SMTP_USERNAME') ?: 'jheyjheypogi30@gmail.com',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: 'rudolvpyhkjasvqn',
        'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'from_email' => getenv('FROM_EMAIL') ?: 'jheyjheypogi30@gmail.com',
        'from_name' => getenv('FROM_NAME') ?: 'Yakap Daycare Center'
    ],
    'security' => [
        'rate_limit_minutes' => 1,
        'cleanup_hours' => 24
    ]
];
?>
