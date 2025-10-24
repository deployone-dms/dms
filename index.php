<?php
session_start();
include("db.php");

// Security headers to prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Simplified session management - avoid constant session destruction
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Only clear specific session variables, not the entire session
    unset($_SESSION['user_logged_in'], $_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_name']);
}

// Include email service
require_once 'email_service.php';

$email = $password = $otp = "";
$emailErr = $passwordErr = $otpErr = "";

// Handle OTP session clearing
if (isset($_GET['clear_otp'])) {
    unset($_SESSION['otp'], $_SESSION['email'], $_SESSION['account_type']);
    // Remove the redirect to prevent loop
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

// Handle secure logout clearing
if (isset($_GET['clear_secure_logout'])) {
    unset($_SESSION['secure_logout'], $_SESSION['secure_logout_time']);
    exit;
}

// Handle logout messages and security checks
$logout_message = "";

// Check if this is a fresh logout
if (isset($_GET['logout'])) {
    $logout_message = "You have been successfully logged out.";
    // Set a flag to prevent back navigation
    $_SESSION['fresh_logout'] = true;
    $_SESSION['logout_timestamp'] = time();
    
    // If this is a secure logout from dashboard, add extra security
    if (isset($_GET['secure'])) {
        $_SESSION['secure_logout'] = true;
        $_SESSION['secure_logout_time'] = time();
    }
} elseif (isset($_GET['timeout'])) {
    $logout_message = "Your session has expired. Please login again.";
} elseif (isset($_GET['unauthorized'])) {
    $logout_message = "Access denied. Please login to continue.";
} elseif (isset($_GET['blocked'])) {
    $logout_message = "Too many login attempts. Please try again later.";
}

// Additional security: Check if user is trying to access after logout
if (isset($_SESSION['fresh_logout']) && (time() - $_SESSION['logout_timestamp']) < 300) {
    // Within 5 minutes of logout, show warning
    if (!isset($_GET['logout'])) {
        $logout_message = "You have been logged out. Please login again to continue.";
    }
}

// OTP Verification
if (isset($_SESSION['otp'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
        $otp = $_POST['otp'];
        if (empty($otp)) {
            $otpErr = "OTP is required";
        } elseif ($otp == $_SESSION['otp']) {
            // OTP correct, login successful
            $db_acc_type = $_SESSION['account_type'] ?? '';
            $user_data = $_SESSION['user_data'] ?? [];
            $email = $_SESSION['email'] ?? '';
            
            // Validate session data
            if (empty($user_data) || empty($email) || empty($db_acc_type)) {
                $otpErr = "Session expired. Please login again.";
            } else {
                unset($_SESSION['otp'], $_SESSION['email'], $_SESSION['account_type'], $_SESSION['user_data']);
            
            // Set proper session flag for security
            $_SESSION['user_logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $email; // You can modify this to get actual name from database
            $_SESSION['account_type'] = $db_acc_type;
            
            // Clear recent logout flags so guards don't bounce an authenticated user
            unset($_SESSION['fresh_logout'], $_SESSION['logout_timestamp'], $_SESSION['secure_logout']);
            
            switch($db_acc_type) {
                case '1': // Admin
                    header("Location: admin_dashboard.php");
                    break;
                case '2': // User/Parent
                    // Set parent-specific session variables
                    $_SESSION['parent_id'] = $user_data['id'];
                    $_SESSION['parent_name'] = $email; // You can modify this to get actual name
                    header("Location: parent_dashboard.php");
                    break;
                case '3': // Supervisor
                    header("Location: admin_dashboard.php");
                    break;
                case '4': // Staff/Teacher
                    header("Location: index2.php");
                    break;
            }
            exit;
            }
        } else {
            $otpErr = "Incorrect OTP";
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Only process login if OTP is NOT set
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
        $check_email = mysqli_query($conn, "SELECT * FROM login_table WHERE email = '".mysqli_real_escape_string($conn, $email)."'");
        $check_email_row = mysqli_num_rows($check_email);
        if ($check_email_row > 0) {
            $row = mysqli_fetch_assoc($check_email);
            $db_pass = $row["password"];
            if ($password == $db_pass) {
                // Generate OTP and send to email
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['email'] = $email;
                $_SESSION['account_type'] = $row["account_type"];
                $_SESSION['user_data'] = $row; // Store user data for OTP verification

                // Send OTP using Brevo email service
                $emailService = new EmailService();
                $result = $emailService->sendOTP($email, $otp);
                
                if (!$result['success']) {
                    $otpErr = $result['message'];
                }
                // Show OTP form after sending - no redirect needed
                // The form will be shown below
            } else {
                $passwordErr = "Incorrect password";
            }
        } else {
            $emailErr = "Your email is unregistered";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Yakap Daycare Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { max-width: 100%; overflow-x: hidden; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1B2A2F;
            /* Background image with soft overlay for readability */
            background: linear-gradient(rgba(255, 255, 255, 0.21) rgba(255, 255, 255, 0.32)), url('image.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 28px; position: sticky; top: 0; background: rgba(255,255,255,0.9);
            backdrop-filter: blur(8px); box-shadow: 0 2px 12px rgba(0,0,0,0.05); z-index: 10;
        }
        .brand { display:flex; align-items:center; gap:10px; font-weight:800; color:#1B5E20; }
        .brand img { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #FFD23C; }
        .nav-actions { display:flex; gap:12px; }
        .btn {
            display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer;
            padding: 12px 18px; border-radius: 12px; font-weight: 600; transition: .25s all ease;
        }
        .btn-login { background: linear-gradient(135deg,#1B5E20,#2E7D32); color:#fff; box-shadow: 0 6px 18px rgba(27, 94, 32, .25); }
        .btn-login:hover { transform: translateY(-2px); }
        .btn-outline { border:2px solid #1B5E20; color:#1B5E20; background:#fff; }
        .btn-outline:hover { background:#1B5E20; color:#fff; }

        .hero {
            display:grid; grid-template-columns: 1.2fr 1fr; align-items:center; gap: 30px;
            padding: 60px 28px; max-width:1100px; margin: 0 auto;
        }
        .hero h1 { font-size: 40px; line-height: 1.15; margin: 0 0 10px; color:#1B5E20; }
        .hero p { font-size: 18px; color:#35534B; margin: 0 0 22px; }
        .hero-cta { display:flex; gap:12px; flex-wrap:wrap; }
        .hero-card {
            background:#fff; border-radius: 20px; padding: 22px; box-shadow: 0 12px 36px rgba(0,0,0,.08);
            display:grid; grid-template-columns: 1fr 1fr; gap:16px;
        }
        .hero-card .tile {
            background: linear-gradient(135deg, #F8F9FA, #E9ECEF);
            border:1px solid #E9ECEF; border-radius: 14px; padding:16px; display:flex; gap:12px; align-items:center;
        }
        .tile i { color:#FFD23C; font-size: 22px; }
        .tile b { color:#1B5E20; }

        .features { padding: 10px 28px 60px; max-width:1100px; margin: 0 auto; }
        .features h2 { color:#1B5E20; margin-bottom: 16px; }
        .grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:18px; }
        .card { background:#fff; border-radius:16px; padding:18px; box-shadow: 0 10px 28px rgba(0,0,0,.06); }
        .card h3 { margin:0 0 6px; color:#1B5E20; }
        .card p { margin:0; color:#4A5D57; }

        .footer { text-align:center; padding: 30px; color:#6C757D; }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 50; overflow-x: hidden; }
        .modal { width: 92vw; max-width: 640px; background: #fff; border-radius: 16px; box-shadow: 0 24px 60px rgba(0,0,0,.25); overflow: hidden; transform: translateY(10px); opacity: 0; transition: .2s ease; overflow-x: hidden; }
        .modal-header { padding: 18px 20px; background: linear-gradient(135deg,#1B5E20,#2E7D32); color: #fff; display:flex; align-items:center; justify-content: space-between; }
        .modal-title { font-weight: 700; }
        .modal-close { background: transparent; border: 0; color: #fff; font-size: 18px; cursor: pointer; }
        .modal-body { padding: 20px; max-width: 100%; overflow-x: hidden; overflow-y: auto; }
        .field { display:flex; flex-direction: column; gap:8px; margin-bottom: 14px; }
        .field label { font-weight: 600; color:#1B5E20; }
        .input { padding: 12px 14px; border-radius: 10px; border: 2px solid #E9ECEF; font-size: 15px; }
        .input:focus { outline: none; border-color:#1B5E20; box-shadow: 0 0 0 3px rgba(27,94,32,.12); }
        .actions { display:flex; gap:10px; justify-content:flex-end; margin-top: 6px; }
        .modal-overlay.show { display:flex; }
        .modal-overlay.show .modal { transform: translateY(0); opacity: 1; }

        /* Enrollment Modal (large) */
        .modal--wide .modal { max-width: min(1100px, 98vw); width: 98vw; overflow-x: hidden; }
        .modal--wide .modal-body { padding: 0; max-width: 100%; overflow-x: hidden; overflow-y: auto; }
        .enroll-frame { width: 100%; height: 85vh; border: none; display:block; }

        /* Prevent sideways scroll inside modal content */
        .modal * { max-width: 100%; box-sizing: border-box; }

        @media (max-width: 900px) {
            .hero { grid-template-columns: 1fr; }
            .hero-card { grid-template-columns: 1fr; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="icon" href="logo.png">
    <meta name="theme-color" content="#1B5E20"/>
</head>
<body>
    <header class="nav">
        <div class="brand">
            <img src="logo.png" alt="Yakap Daycare" onerror="this.src='yakaplogopo.jpg'">
            <span>Yakap Daycare Center</span>
        </div>
        <div class="nav-actions"></div>
    </header>

    <section class="hero">
        <div>
            <h1>Manage Students, Assess Progress, Empower Teachers</h1>
            <p>Streamlined enrollment, progress assessments, attendance tracking, and teacher management for Early Childhood Care & Development.</p>
            
            <?php if ($logout_message): ?>
                <div style="background: linear-gradient(135deg, #D4EDDA, #C3E6CB); color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #C3E6CB; text-align: center;">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($logout_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="hero-cta">
                <button type="button" class="btn btn-login" id="openLogin"><i class="fas fa-right-to-bracket"></i> Log In</button>
                <button type="button" class="btn btn-outline" id="openParent"><i class="fas fa-user"></i> Parent Portal</button>
                <button type="button" class="btn btn-outline" id="openEnroll"><i class="fas fa-user-plus"></i> Enroll</button>
            </div>
        </div>
        <div class="hero-card">
            <div class="tile"><i class="fas fa-user-plus"></i><div><b>Enrollment</b><div>Quickly add and manage student info</div></div></div>
            <div class="tile"><i class="fas fa-chart-line"></i><div><b>Progress</b><div>Capture developmental milestones</div></div></div>
            <div class="tile"><i class="fas fa-calendar-check"></i><div><b>Attendance</b><div>Track daily attendance</div></div></div>
            <div class="tile"><i class="fas fa-chalkboard-teacher"></i><div><b>Teachers</b><div>Maintain teacher profiles</div></div></div>
        </div>
    </section>

    <section class="features">
        <h2>Why Yakap?</h2>
        <div class="grid">
            <div class="card">
                <h3><i class="fas fa-lock"></i> Secure</h3>
                <p>Role-based access with protected dashboards.</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-bolt"></i> Efficient</h3>
                <p>Fast data entry and clear reporting tools.</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-heart"></i> Child-centered</h3>
                <p>Built to support ECCD assessments and outcomes.</p>
            </div>
        </div>
    </section>

    <footer class="footer">© <?php echo date('Y'); ?> Yakap Daycare Center</footer>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
            <div class="modal-header">
                <div class="modal-title" id="loginTitle">
                    <i class="fas fa-<?php echo isset($_SESSION['otp']) ? 'shield-alt' : 'right-to-bracket'; ?>"></i> 
                    <?php echo isset($_SESSION['otp']) ? 'Verify OTP' : 'Login'; ?>
                </div>
                <button class="modal-close" id="closeLogin" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['otp'])): ?>
                    <!-- OTP Verification Form -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="background: #E8F5E8; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <i class="fas fa-envelope" style="color: #1B5E20; margin-right: 8px;"></i>
                            <strong>OTP sent to:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?>
                        </div>
                        <p style="color: #666; font-size: 14px;">Enter the 6-digit code sent to your email</p>
                    </div>
                    <form method="post" action="index.php">
                        <div class="field">
                            <label for="otp">OTP Code</label>
                            <input class="input" type="text" id="otp" name="otp" placeholder="123456" maxlength="6" style="text-align: center; font-size: 18px; letter-spacing: 3px;" required>
                            <?php if($otpErr): ?>
                                <div style="color: #e74c3c; font-size: 14px; margin-top: 5px;"><?php echo $otpErr; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <button type="button" class="btn btn-outline" id="cancelLogin">Cancel</button>
                            <button type="submit" class="btn btn-login">Verify OTP</button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Regular Login Form -->
                    <form method="post" action="index.php">
                        <div class="field">
                            <label for="email">Email</label>
                            <input class="input" type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            <?php if($emailErr): ?>
                            <div style="color:#E83E6B; font-size: 13px; margin-top:4px;"><?php echo $emailErr; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="password">Password</label>
                            <input class="input" type="password" id="password" name="password" placeholder="Your password" value="<?php echo htmlspecialchars($password); ?>" required>
                            <?php if($passwordErr): ?>
                            <div style="color:#E83E6B; font-size: 13px; margin-top:4px;"><?php echo $passwordErr; ?></div>
                            <?php endif; ?>
                        </div>
                    <div class="actions">
                        <button type="button" class="btn btn-outline" id="cancelLogin">Cancel</button>
                        <button type="submit" class="btn btn-login" id="loginBtn">
                            <span id="loginText">Login</span>
                            <span id="loginSpinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Sending OTP...
                            </span>
                        </button>
                    </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <div class="modal-overlay modal--wide" id="enrollModal">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="enrollTitle">
            <div class="modal-header">
                <div class="modal-title" id="enrollTitle"><i class="fas fa-user-plus"></i> Enroll Student</div>
                <button class="modal-close" id="closeEnroll" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <iframe class="enroll-frame" src="add_stud.php?embed=1" title="Enroll Student"></iframe>
            </div>
        </div>
    </div>

    <!-- Parent Portal Modal -->
    <div class="modal-overlay" id="parentModal">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="parentTitle">
            <div class="modal-header">
                <div class="modal-title" id="parentTitle"><i class="fas fa-user"></i> Parent Portal</div>
                <button class="modal-close" id="closeParent" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <iframe class="enroll-frame" src="parent_login.php?embed=1" title="Parent Portal"></iframe>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const modal = document.getElementById('loginModal');
        const openBtn = document.getElementById('openLogin');
        const closeBtn = document.getElementById('closeLogin');
        const cancelBtn = document.getElementById('cancelLogin');
        const enrollModal = document.getElementById('enrollModal');
        const openEnroll = document.getElementById('openEnroll');
        const closeEnroll = document.getElementById('closeEnroll');
        const parentModal = document.getElementById('parentModal');
        const openParent = document.getElementById('openParent');
        const closeParent = document.getElementById('closeParent');

        function openModal(){ 
            modal.classList.add('show'); 
            setTimeout(()=>{ 
                const email = document.getElementById('email');
                const otp = document.getElementById('otp');
                if (otp) {
                    otp.focus();
                    // Only allow numbers in OTP input
                    otp.addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                } else if (email) {
                    email.focus();
                }
            }, 50); 
        }
        function closeModal(){ 
            modal.classList.remove('show'); 
            // Clear OTP session if user cancels
            <?php if (isset($_SESSION['otp'])): ?>
                if (confirm('Are you sure you want to cancel? This will clear your OTP verification.')) {
                    window.location.href = 'landing.php?clear_otp=1';
                }
            <?php endif; ?>
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
        document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeModal(); });

        // Auto-open modal if there were validation errors from POST or OTP is in session
        <?php if($_SERVER["REQUEST_METHOD"] === 'POST' && ($emailErr || $passwordErr || $otpErr)) { echo 'openModal();'; } ?>
        <?php if(isset($_SESSION['otp'])) { echo 'openModal();'; } ?>

        // Comprehensive back button prevention
        function preventBackButton() {
            // Method 1: Fill history with current page
            for (let i = 0; i < 50; i++) {
                history.pushState({page: 'landing'}, '', location.href);
            }
            
            // Method 2: Override popstate event - CRITICAL for back button
            window.onpopstate = function(event) {
                // Immediately push more states
                for (let i = 0; i < 50; i++) {
                    history.pushState({page: 'landing'}, '', location.href);
                }
                
                // Show warning and prevent navigation
                alert('You are logged out. Back navigation is disabled for security.');
                
                // Force reload to ensure clean state
                window.location.reload();
            };
            
            // Method 3: Disable ALL navigation keys
            document.addEventListener('keydown', function(e) {
                // Disable Left Arrow key (37) - PRIMARY TARGET
                if (e.keyCode === 37) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    alert('Back navigation is disabled. You are logged out.');
                    return false;
                }
                // Disable Alt+Left Arrow
                if (e.altKey && e.keyCode === 37) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    alert('Back navigation is disabled. You are logged out.');
                    return false;
                }
                // Disable Backspace key when not in input fields
                if (e.keyCode === 8 && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                // Disable F5 refresh
                if (e.keyCode === 116) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                // Disable Ctrl+R (refresh)
                if (e.ctrlKey && e.keyCode === 82) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
                // Disable Ctrl+L (address bar)
                if (e.ctrlKey && e.keyCode === 76) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true); // Use capture phase
            
            // Method 4: Disable mouse back button
            document.addEventListener('mouseup', function(e) {
                if (e.button === 3) { // Back button
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    alert('Back navigation is disabled. You are logged out.');
                    return false;
                }
            }, true);
            
            // Method 5: Disable right-click context menu
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }, true);
            
            // Method 6: Override browser navigation
            window.addEventListener('beforeunload', function(e) {
                e.preventDefault();
                e.returnValue = 'You are logged out. Any unsaved changes will be lost.';
                return 'You are logged out. Any unsaved changes will be lost.';
            });
            
            // Method 7: Optimized history manipulation (disabled to prevent refresh loops)
            // setInterval(function() {
            //     // Keep pushing states to prevent back navigation
            //     history.pushState({page: 'landing'}, '', location.href);
            // }, 2000); // Disabled to prevent refresh loops
            
            // Method 8: Override history methods
            const originalBack = history.back;
            const originalGo = history.go;
            
            history.back = function() {
                alert('Back navigation is disabled. You are logged out.');
                return false;
            };
            
            history.go = function(delta) {
                if (delta < 0) {
                    alert('Back navigation is disabled. You are logged out.');
                    return false;
                }
                return originalGo.apply(history, arguments);
            };
        }

        // Initialize back button prevention
        preventBackButton();
        
        // Enhanced back button prevention for secure logouts (optimized)
        <?php if (isset($_SESSION['secure_logout'])): ?>
        (function() {
            console.log('Secure logout detected - optimized back button prevention activated');
            
            // Store original methods
            const originalBack = history.back;
            const originalGo = history.go;
            
            // Override history methods once
            history.back = function() {
                alert('Back navigation is completely disabled. You are logged out.');
                return false;
            };
            
            history.go = function(delta) {
                if (delta < 0) {
                    alert('Back navigation is completely disabled. You are logged out.');
                    return false;
                }
                return originalGo.apply(history, arguments);
            };
            
            // Add event listeners once (not repeatedly)
            document.addEventListener('keydown', function(e) {
                if ([37, 38, 39, 40].includes(e.keyCode)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    alert('Navigation is disabled. You are logged out.');
                    return false;
                }
            }, true);
            
            document.addEventListener('mouseup', function(e) {
                if (e.button === 3 || e.button === 4) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    alert('Navigation is disabled. You are logged out.');
                    return false;
                }
            }, true);
            
            // Lightweight history manipulation (disabled to prevent refresh loops)
            // setInterval(function() {
            //     history.pushState({page: 'landing', secure: true}, '', location.href);
            // }, 1000); // Disabled to prevent refresh loops
            
            // Clear secure logout flag after 2 minutes (reduced time)
            setTimeout(function() {
                fetch('landing.php?clear_secure_logout=1', {method: 'POST'});
            }, 120000);
        })();
        <?php endif; ?>
        
        // Additional aggressive back button prevention
        (function() {
            // Override the history object completely
            const originalPushState = history.pushState;
            const originalReplaceState = history.replaceState;
            
            history.pushState = function() {
                originalPushState.apply(history, arguments);
                // Push additional states to prevent back navigation
                for (let i = 0; i < 10; i++) {
                    originalPushState.call(history, {page: 'landing'}, '', location.href);
                }
            };
            
            history.replaceState = function() {
                originalReplaceState.apply(history, arguments);
                // Push additional states after replace
                for (let i = 0; i < 10; i++) {
                    originalPushState.call(history, {page: 'landing'}, '', location.href);
                }
            };
            
            // Override the back method
            const originalBack = history.back;
            history.back = function() {
                alert('Back navigation is disabled. You are logged out.');
                return false;
            };
            
            // Override the go method
            const originalGo = history.go;
            history.go = function(delta) {
                if (delta < 0) {
                    alert('Back navigation is disabled. You are logged out.');
                    return false;
                }
                return originalGo.apply(history, arguments);
            };
        })();
        
        // Page visibility API - detect if user tries to go back
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden, user might be navigating away
                setTimeout(function() {
                    if (!document.hidden) {
                        // Page is visible again, check if we're still on landing page
                        if (location.pathname !== '/DMS/landing.php' && !location.href.includes('landing.php')) {
                            alert('You are logged out. Redirecting to login page.');
                            window.location.href = 'landing.php';
                        }
                    }
                }, 100);
            }
        });
        
        // Additional event listeners for all possible back navigation
        window.addEventListener('popstate', function(e) {
            // This should already be handled by the main function, but add extra protection
            alert('Back navigation is disabled. You are logged out.');
            history.pushState({page: 'landing'}, '', location.href);
        });
        
        // Disable all arrow keys
        document.addEventListener('keydown', function(e) {
            if ([37, 38, 39, 40].includes(e.keyCode)) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (e.keyCode === 37) {
                    alert('Back navigation is disabled. You are logged out.');
                }
                return false;
            }
        }, true);
        
        // Final layer: Optimized URL monitoring (much less frequent)
        let currentUrl = location.href;
        setInterval(function() {
            if (location.href !== currentUrl) {
                // URL changed, check if it's not the landing page
                if (!location.href.includes('landing.php')) {
                    alert('You are logged out. Redirecting to login page.');
                    window.location.href = 'landing.php';
                }
                currentUrl = location.href;
            }
        }, 3000); // Much reduced frequency for better performance
        
        // Debug: Log when left arrow is pressed
        document.addEventListener('keydown', function(e) {
            if (e.keyCode === 37) {
                console.log('Left arrow pressed - should be blocked');
                console.log('Event prevented:', e.defaultPrevented);
            }
        });
        
        // Additional test: Check if back button is actually working
        console.log('Back button prevention initialized');
        console.log('History length:', history.length);
        
        // Add loading indicator for login form
        const loginForm = document.querySelector('form[action="index.php"]');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                const loginBtn = document.getElementById('loginBtn');
                const loginText = document.getElementById('loginText');
                const loginSpinner = document.getElementById('loginSpinner');
                
                if (loginBtn && loginText && loginSpinner) {
                    loginText.style.display = 'none';
                    loginSpinner.style.display = 'inline';
                    loginBtn.disabled = true;
                }
            });
        }

        // Clear any cached data
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            });
        }

        // Force reload if user tries to navigate back
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache, force reload
                window.location.reload();
            }
        });

        // Enroll modal controls
        function openEnrollModal(){ enrollModal.classList.add('show'); }
        function closeEnrollModal(){ enrollModal.classList.remove('show'); }
        if (openEnroll) openEnroll.addEventListener('click', openEnrollModal);
        if (closeEnroll) closeEnroll.addEventListener('click', closeEnrollModal);
        enrollModal.addEventListener('click', (e)=>{ if(e.target === enrollModal) closeEnrollModal(); });

        function openParentModal(){ parentModal.classList.add('show'); }
        function closeParentModal(){ parentModal.classList.remove('show'); }
        if (openParent) openParent.addEventListener('click', openParentModal);
        if (closeParent) closeParent.addEventListener('click', closeParentModal);
        parentModal.addEventListener('click', (e)=>{ if(e.target === parentModal) closeParentModal(); });
    })();
    </script>
</body>
</html>


