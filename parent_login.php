<?php
include 'db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, password_hash, full_name FROM parents WHERE email=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                if (password_verify($password, $row['password_hash'])) {
                    $_SESSION['parent_id'] = intval($row['id']);
                    $_SESSION['parent_name'] = $row['full_name'];
                    $embed = isset($_GET['embed']) && $_GET['embed'] == '1';
                    if ($embed) {
                        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script>window.top.location.href = "parent_dashboard.php";</script></head><body></body></html>';
                        exit;
                    } else {
                        header('Location: parent_dashboard.php');
                        exit;
                    }
                }
            }
            $stmt->close();
        }
        $error = 'Invalid credentials.';
    }
}
$embed = isset($_GET['embed']) && $_GET['embed'] == '1';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f7f7f7; }
        .card { max-width: 420px; margin: 60px auto; background:#fff; border-radius: 12px; box-shadow:0 8px 24px rgba(0,0,0,.08); overflow:hidden; }
        .card h2 { margin:0; padding:18px 22px; background: linear-gradient(135deg, #1B5E20, #2E7D32); color:#fff; }
        .card .content { padding: 20px 22px; }
        .input { width:100%; padding:12px 14px; border:2px solid #E9ECEF; border-radius:10px; font-size:15px; }
        .btn { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg, #28A745, #20C997); color:#fff; border:0; padding:12px 18px; border-radius:10px; font-weight:700; cursor:pointer; }
        .alert { padding:12px 14px; border-radius:10px; margin-bottom:12px; font-weight:600; background:#F8D7DA; color:#842029; border:1px solid #F5C2C7; }
        label { font-weight:600; color:#1B5E20; display:block; margin:8px 0 6px; }
        <?php if ($embed): ?>
        body { background:#fff; }
        .card { margin: 10px auto; box-shadow:none; }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-right-to-bracket"></i> Parent Login</h2>
        <div class="content">
            <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="post">
                <label>Email</label>
                <input class="input" type="email" name="email" placeholder="you@example.com" required>
                <label>Password</label>
                <input class="input" type="password" name="password" required>
                <div style="margin-top:14px; display:flex; gap:10px; align-items:center; justify-content:space-between;">
                    <button class="btn" type="submit"><i class="fas fa-right-to-bracket"></i> Login</button>
                    <a href="parent_register.php<?php echo $embed ? '?embed=1' : '' ?>" style="text-decoration:none; font-weight:600; color:#0d6efd;">Create account</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>


