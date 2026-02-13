<?php
/**
 * صفحه ورود به پنل مدیریت
 * Admin Login Page
 */

require_once __DIR__ . '/config.php';

// اگر قبلاً وارد شده، هدایت به پنل مدیریت
if (isAdminLoggedIn()) {
    header('Location: admin/');
    exit;
}

$error = '';
$success = '';

// بررسی ارسال فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    } else {
        // اتصال به دیتابیس
        $conn = getDBConnection();
        
        // بررسی نام کاربری و رمز عبور (بدون هش)
        $sql = "SELECT * FROM admins WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // تنظیم سشن
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // هدایت به پنل مدیریت
            header('Location: admin/');
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box fade-in">
            <div class="login-header">
                <h1>🔐 ورود به پنل مدیریت</h1>
                <p>واحد مناقصات شرکت لوتوس</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-box">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-box">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" id="username" name="username" placeholder="نام کاربری خود را وارد کنید" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" name="password" placeholder="رمز عبور خود را وارد کنید" required>
                </div>
                
                <button type="submit" class="login-btn">
                    ورود به سیستم
                </button>
            </form>
            
            <div style="text-align: center; padding: 0 30px 30px;">
                <a href="index.php" style="color: #667eea;">← بازگشت به صفحه اصلی</a>
            </div>
        </div>
    </div>
</body>
</html>
