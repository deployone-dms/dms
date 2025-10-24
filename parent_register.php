<?php
include 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
// Linking to a child is removed for privacy; school staff can link later

    if (!$full_name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO parents (email, password_hash, full_name, phone) VALUES (?,?,?,?)");
        if ($stmt) {
            $stmt->bind_param('ssss', $email, $hash, $full_name, $phone);
            if ($stmt->execute()) {
                $parent_id = $stmt->insert_id;
                $stmt->close();
                $success = 'Account created. Please login.';
            } else {
                $error = $conn->errno === 1062 ? 'Email already registered.' : 'Failed to create account.';
                $stmt->close();
            }
        } else {
            $error = 'Failed to prepare statement.';
        }
    }
}
$embed = isset($_GET['embed']) && $_GET['embed'] == '1';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f7f7f7; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .card { max-width: 520px; width:100%; margin: 0 auto; background:#fff; border-radius: 12px; box-shadow:0 8px 24px rgba(0,0,0,.08); overflow:hidden; }
        .card h2 { margin:0; padding:18px 22px; background: linear-gradient(135deg, #1B5E20, #2E7D32); color:#fff; }
        .card .content { padding: 20px 22px 28px 22px; }
        .row { display:flex; gap:16px; }
        .row.password-row { gap:20px; flex-wrap: wrap; }
        .row.password-row > div { flex:1; min-width: 220px; }
        .input { width:100%; padding:12px 14px; border:2px solid #E9ECEF; border-radius:10px; font-size:15px; }
        .btn { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg, #28A745, #20C997); color:#fff; border:0; padding:12px 18px; border-radius:10px; font-weight:700; cursor:pointer; }
        .note { font-size: 12px; color:#6c757d; margin-top:8px; }
        .alert { padding:12px 14px; border-radius:10px; margin-bottom:12px; font-weight:600; }
        .alert.error { background:#F8D7DA; color:#842029; border:1px solid #F5C2C7; }
        .alert.success { background:#D1E7DD; color:#0F5132; border:1px solid #BADBCC; }
        label { font-weight:600; color:#1B5E20; display:block; margin:8px 0 6px; }
        select { width:100%; padding:12px 14px; border:2px solid #E9ECEF; border-radius:10px; }
        <?php if ($embed): ?>
        body { background:#fff; padding:10px; }
        .card { margin: 0 auto; box-shadow:none; }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-user-shield"></i> Parent Registration</h2>
        <div class="content">
            <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <form method="post">
                <label>Full Name</label>
                <input class="input" type="text" name="full_name" placeholder="e.g., Maria D. Santos" required>

                <label>Email</label>
                <input class="input" type="email" name="email" placeholder="you@example.com" required>

                <label>Phone (optional)</label>
                <input class="input" type="text" name="phone" placeholder="09XXXXXXXXX">

                <div class="row password-row">
                    <div style="flex:1">
                        <label>Password</label>
                        <input class="input" type="password" name="password" required>
                    </div>
                    <div style="flex:1">
                        <label>Confirm Password</label>
                        <input class="input" type="password" name="confirm" required>
                    </div>
                </div>

                <!-- Linking to a child is handled by the school to protect student privacy -->

                <div style="margin-top:18px; padding-bottom:4px; display:flex; gap:12px; align-items:center; justify-content:space-between;">
                    <button class="btn" type="submit"><i class="fas fa-user-plus"></i> Create Account</button>
                    <a href="parent_login.php<?php echo $embed ? '?embed=1' : '' ?>" style="text-decoration:none; font-weight:600; color:#0d6efd;">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>


