<?php
session_start();
include 'db.php';
include 'otp_service.php';

$otpService = new OTPService($conn);
$message = '';
$messageType = '';

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        $email = $_SESSION['otp_email'] ?? '';
        $otp = $_POST['otp'] ?? '';
        
        if ($email && $otp) {
            $result = $otpService->verifyOTP($email, $otp);
            
            if ($result['success']) {
                $_SESSION['otp_verified'] = true;
                header('Location: test.php');
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } else {
            $message = 'Please enter the OTP code';
            $messageType = 'error';
        }
    } elseif (isset($_POST['resend_otp'])) {
        $email = $_SESSION['otp_email'] ?? '';
        
        if ($email) {
            $result = $otpService->sendOTP($email);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        } else {
            $message = 'Email not found in session';
            $messageType = 'error';
        }
    }
}

// Clean up expired OTPs
$otpService->cleanupExpiredOTPs();

// Check if email is in session
if (!isset($_SESSION['otp_email'])) {
    header('Location: test.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification - Yakap Daycare Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F4EDE4 0%, #E8F5E8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .otp-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
        }

        h1 {
            color: #1B5E20;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .email-display {
            background: #F8F9FA;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 600;
            color: #1B5E20;
        }

        .otp-form {
            margin-bottom: 30px;
        }

        .otp-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #E9ECEF;
            border-radius: 10px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 3px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #1B5E20;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: linear-gradient(135deg, #0F4A2A, #1B5E20);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(27, 94, 32, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6C757D, #495057);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057, #343A40);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message.success {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .message.error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }

        .info {
            background: #D1ECF1;
            color: #0C5460;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link {
            color: #1B5E20;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .otp-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .otp-input {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h1>Verify Your Email</h1>
        <p class="subtitle">Enter the 6-digit code sent to your email</p>
        
        <div class="email-display">
            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['otp_email']); ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> The OTP code is valid for 5 minutes only. Check your spam folder if you don't see the email.
        </div>
        
        <form method="POST" class="otp-form">
            <input type="text" 
                   name="otp" 
                   class="otp-input" 
                   placeholder="Enter 6-digit code" 
                   maxlength="6" 
                   pattern="[0-9]{6}" 
                   required
                   autocomplete="off">
            
            <div>
                <button type="submit" name="verify_otp" class="btn">
                    <i class="fas fa-check"></i> Verify OTP
                </button>
                <button type="submit" name="resend_otp" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Resend OTP
                </button>
            </div>
        </form>
        
        <a href="test.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // Auto-focus on OTP input
        document.querySelector('.otp-input').focus();
        
        // Only allow numbers
        document.querySelector('.otp-input').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Auto-submit when 6 digits are entered
        document.querySelector('.otp-input').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                setTimeout(() => {
                    document.querySelector('button[name="verify_otp"]').click();
                }, 500);
            }
        });
    </script>
</body>
</html>




