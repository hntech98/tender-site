<?php
/**
 * صفحه تنظیمات
 * Settings Page
 */

require_once __DIR__ . '/../config.php';
requireAdminLogin();

$conn = getDBConnection();

$message = '';
$error = '';

// بررسی ارسال فرم تغییر رمز
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'لطفاً تمام فیلدها را پر کنید.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'رمز عبور جدید و تکرار آن مطابقت ندارند.';
    } elseif (strlen($new_password) < 4) {
        $error = 'رمز عبور باید حداقل 4 کاراکتر باشد.';
    } else {
        // بررسی رمز فعلی
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT * FROM admins WHERE id = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $admin_id, $current_password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // تغییر رمز
            $sql = "UPDATE admins SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $new_password, $admin_id);
            
            if ($stmt->execute()) {
                $message = 'رمز عبور با موفقیت تغییر کرد.';
            } else {
                $error = 'خطا در تغییر رمز عبور.';
            }
        } else {
            $error = 'رمز عبور فعلی اشتباه است.';
        }
    }
}

// دریافت اطلاعات ادمین
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- هدر ساده برای مدیریت -->
    <header class="site-header" style="padding: 10px 0;">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <a href="index.php">
                        <div class="site-title">
                            <span class="company-name">شرکت لوتوس</span>
                            <span class="unit-name"><?php echo SITE_NAME; ?></span>
                        </div>
                    </a>
                </div>
                <nav class="header-nav">
                    <a href="../index.php" class="nav-link">مشاهده سایت</a>
                    <a href="logout.php" class="nav-link" style="background: rgba(235, 51, 73, 0.8);">خروج</a>
                </nav>
            </div>
        </div>
    </header>
    
    <div class="admin-container">
        <!-- منوی جانبی -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>پنل مدیریت</h2>
                <p>خوش آمدید، <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php">
                        <span class="menu-icon">📊</span>
                        <span>داشبورد</span>
                    </a>
                </li>
                <li>
                    <a href="../index.php">
                        <span class="menu-icon">🌐</span>
                        <span>مشاهده سایت</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="openModal('addTenderModal')">
                        <span class="menu-icon">➕</span>
                        <span>اضافه کردن مناقصه</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="active">
                        <span class="menu-icon">⚙️</span>
                        <span>تنظیمات</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="menu-icon">🚪</span>
                        <span>خروج</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- محتوای اصلی -->
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="admin-page-title">
                    <span>⚙️</span>
                    تنظیمات
                </h1>
            </div>
            
            <?php if ($message): ?>
                <div class="success-box" style="margin-bottom: 20px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-box" style="margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- بخش اطلاعات کاربری -->
            <div class="settings-section">
                <h3>👤 اطلاعات کاربری</h3>
                <table style="width: 100%; max-width: 500px;">
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">نام کاربری:</td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($admin['username']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">تاریخ ایجاد:</td>
                        <td style="padding: 10px;"><?php echo $admin['created_at']; ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- بخش تغییر رمز عبور -->
            <div class="settings-section">
                <h3>🔐 تغییر رمز عبور</h3>
                <form method="POST" action="" style="max-width: 500px;">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label>رمز عبور فعلی:</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>رمز عبور جدید:</label>
                        <input type="password" name="new_password" required minlength="4">
                    </div>
                    
                    <div class="form-group">
                        <label>تکرار رمز عبور جدید:</label>
                        <input type="password" name="confirm_password" required minlength="4">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        💾 ذخیره تغییرات
                    </button>
                </form>
            </div>
            
            <!-- بخش اطلاعات سیستم -->
            <div class="settings-section">
                <h3>📊 اطلاعات سیستم</h3>
                <table style="width: 100%; max-width: 500px;">
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">نسخه PHP:</td>
                        <td style="padding: 10px;"><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">پوشه آپلود:</td>
                        <td style="padding: 10px;"><?php echo UPLOAD_DIR; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">حداکثر حجم آپلود:</td>
                        <td style="padding: 10px;"><?php echo formatFileSize(MAX_FILE_SIZE); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">منطقه زمانی:</td>
                        <td style="padding: 10px;">Asia/Tehran</td>
                    </tr>
                </table>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>
