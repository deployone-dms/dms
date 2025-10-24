<?php
session_start();
include("db.php");
include("otp_service.php");

$email = $password = "";
$emailErr = $passwordErr = $message = "";
$messageType = "";

// Initialize OTP service
$otpService = new OTPService($Connection);

// Handle OTP send request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_otp'])) {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = $_POST["email"];
        
        // Check if email exists in database
        $check_email = mysqli_query($Connection, "SELECT * FROM login_table WHERE email = '".mysqli_real_escape_string($Connection, $email)."'");
        if (mysqli_num_rows($check_email) > 0) {
            $result = $otpService->sendOTP($email);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            
            if ($result['success']) {
                $_SESSION['otp_email'] = $email;
                header("Location: otp_verification.php");
                exit;
            }
        } else {
            $emailErr = "Your email is unregistered";
        }
    }
}

// Handle regular login (if OTP is verified)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = $_POST["email"];
    }
    if (empty($_POST["password"])) {
        $passwordErr = "Password required";
    } else {
        $password = $_POST["password"];
    }

    if ($password && $email) {
        // Check if OTP is verified
        if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            $message = "Please verify your email with OTP first";
            $messageType = "error";
        } else {
            $check_email = mysqli_query($Connection, "SELECT * FROM login_table WHERE email = '".mysqli_real_escape_string($Connection, $email)."'");
            $check_email_row = mysqli_num_rows($check_email);
            if ($check_email_row > 0) {
                $row = mysqli_fetch_assoc($check_email);
                $db_pass = $row["password"];
                $db_acc_type = $row["account_type"];
                if ($password == $db_pass) {
                    // Clear OTP session data
                    unset($_SESSION['otp_email']);
                    unset($_SESSION['otp_verified']);
                    
                    // Login successful, redirect based on account type
                    switch($db_acc_type) {
                        case '1': // Admin
                            header("Location: dashboard.php");
                            break;
                        case '2': // Manager
                            header("Location: manager/dashboard.php");
                            break;
                        case '3': // Supervisor
                            header("Location: supervisor/dashboard.php");
                            break;
                        case '4': // Staff
                            header("Location: staff/dashboard.php");
                            break;
                    }
                    exit;
                } else {
                    $passwordErr = "Incorrect password";
                }
            } else {
                $emailErr = "Your email is unregistered";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCS LOGIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <style>
      body {
        background: url(img/bgg.png) no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .login-container {
        background: rgba(255, 255, 255, 0.76);
        border-radius: 1rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        padding: 2.5rem 2rem;
        max-width: 1200px;
        width: 100%;
      }
      .login-logo {
        width: 170px;
        margin-bottom: 1rem;
      }
      .form-title {
        font-weight: 700;
        margin-bottom: 1rem;
        color: #2c3e50;
      }
      .form-label {
        font-weight: 500;
        text-align: left;
        display: block;
        max-width: 300px;
        margin: 0 auto;
      }
      .form-control {
        max-width: 300px;
        margin: 0 auto;
      }
      .input-group {
        max-width: 300px;
        margin: 0 auto;
      }
      .btn-primary {
        width: 200px;
        font-weight: 600;
        letter-spacing: 1px;
        margin: 0 auto;
        display: block;
      }
      .btn-outline-primary {
        width: 200px;
        font-weight: 600;
        letter-spacing: 1px;
        margin: 10px auto 0 auto;
        display: block;
      }
      .error {
        font-size: 0.95em;
        color: #e74c3c;
        text-align: center;
      }
      .input-group-text {
        background: #f8f9fa;
      }
    </style>
</head>
<body>
  <div class="login-container mx-auto">
    <div class="text-center">
      <img src="img/yakap.jpg" alt="System Logo" class="login-logo rounded-circle shadow-sm">
      <h2 class="form-title">Early Childhood Care & Development System</h2>
      <h6>LOGIN</h6>
    </div>
    <!-- Message Display -->
    <?php if($message): ?>
      <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Step 1: Email Verification -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off" id="emailForm">
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-envelope"></i></span>
          <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
        </div>
        <?php if($emailErr): ?>
          <div class="error"><?php echo $emailErr; ?></div>
        <?php endif; ?>
      </div>
      <div class="d-grid mt-4">
        <button type="submit" name="send_otp" class="btn btn-primary">
          <i class="fa fa-shield-alt"></i> Send OTP
        </button>
      </div>
    </form>

    <!-- Step 2: Password Login (shown after OTP verification) -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off" id="loginForm" style="display: none;">
      <div class="mb-3">
        <label for="login_email" class="form-label">Email address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-envelope"></i></span>
          <input type="email" class="form-control" id="login_email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
        </div>
      </div>
      <div class="mb-3">
        <label for="login_password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-lock"></i></span>
          <input type="password" class="form-control" id="login_password" name="password" placeholder="Enter your password" required>
        </div>
        <?php if($passwordErr): ?>
          <div class="error"><?php echo $passwordErr; ?></div>
        <?php endif; ?>
      </div>
      <div class="d-grid mt-4">
        <button type="submit" name="login" class="btn btn-success">
          <i class="fa fa-sign-in-alt"></i> Complete Login
        </button>
      </div>
    </form>
  </div>
  <script src="https://kit.fontawesome.com/12b6c6f773.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Show login form if OTP is already verified
    <?php if(isset($_SESSION['otp_verified']) && $_SESSION['otp_verified']): ?>
      document.getElementById('emailForm').style.display = 'none';
      document.getElementById('loginForm').style.display = 'block';
    <?php endif; ?>
  </script>
</body>
</html>