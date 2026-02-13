<?php
/**
 * فایل نصب سیستم مناقصات لوتوس
 * Lotus Tender Management System - Installation Script
 * نسخه 2.1
 */

// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$install_complete = false;
$error_message = '';
$success_message = '';

// بررسی ارسال فرم نصب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? 'lotus_tender';
    
    // اتصال به MySQL بدون انتخاب دیتابیس
    $conn = @new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        $error_message = 'خطا در اتصال به MySQL: ' . $conn->connect_error;
    } else {
        // تنظیم کاراکتر ست
        $conn->set_charset('utf8mb4');
        
        // ایجاد دیتابیس
        $create_db = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci";
        
        if ($conn->query($create_db)) {
            // انتخاب دیتابیس
            $conn->select_db($db_name);
            
            // حذف جداول قدیمی اگر وجود دارند (برای نصب تازه)
            $conn->query("DROP TABLE IF EXISTS settings");
            $conn->query("DROP TABLE IF EXISTS downloads");
            $conn->query("DROP TABLE IF EXISTS admins");
            $conn->query("DROP TABLE IF EXISTS tenders");
            
            // ایجاد جدول مناقصات
            $create_tenders_table = "
                CREATE TABLE `tenders` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `project_name` VARCHAR(255) NOT NULL,
                    `description` TEXT,
                    `deadline` DATE,
                    `files` TEXT,
                    `download_count` INT(11) DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
            ";
            
            // ایجاد جدول مدیران (با ساختار جدید)
            $create_admins_table = "
                CREATE TABLE `admins` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `username` VARCHAR(50) NOT NULL UNIQUE,
                    `password` VARCHAR(255) NOT NULL,
                    `full_name` VARCHAR(100),
                    `email` VARCHAR(100),
                    `role` ENUM('admin', 'manager') DEFAULT 'admin',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
            ";
            
            // ایجاد جدول دانلودها
            $create_downloads_table = "
                CREATE TABLE `downloads` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `tender_id` INT(11) NOT NULL,
                    `file_name` VARCHAR(255) NOT NULL,
                    `download_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `ip_address` VARCHAR(45),
                    PRIMARY KEY (`id`),
                    KEY `idx_tender_id` (`tender_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
            ";
            
            // ایجاد جدول تنظیمات
            $create_settings_table = "
                CREATE TABLE `settings` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                    `setting_value` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
            ";
            
            if ($conn->query($create_tenders_table) && 
                $conn->query($create_admins_table) && 
                $conn->query($create_downloads_table) &&
                $conn->query($create_settings_table)) {
                
                // دریافت اطلاعات کاربران
                $admin1_user = $_POST['admin1_user'] ?? 'admin';
                $admin1_pass = $_POST['admin1_pass'] ?? 'admin123';
                $admin1_name = $_POST['admin1_name'] ?? 'مدیر اصلی';
                
                $admin2_user = $_POST['admin2_user'] ?? 'manager';
                $admin2_pass = $_POST['admin2_pass'] ?? 'manager123';
                $admin2_name = $_POST['admin2_name'] ?? 'مدیر دوم';
                
                // ایجاد کاربر اول (مدیر اصلی)
                $stmt1 = $conn->prepare("INSERT INTO admins (username, password, full_name, role) VALUES (?, ?, ?, 'admin')");
                $stmt1->bind_param('sss', $admin1_user, $admin1_pass, $admin1_name);
                $stmt1->execute();
                
                // ایجاد کاربر دوم (مدیر دوم)
                $stmt2 = $conn->prepare("INSERT INTO admins (username, password, full_name, role) VALUES (?, ?, ?, 'manager')");
                $stmt2->bind_param('sss', $admin2_user, $admin2_pass, $admin2_name);
                $stmt2->execute();
                
                // به‌روزرسانی فایل config.php
                $config_content = file_get_contents('config.php');
                $config_content = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$db_host');", $config_content);
                $config_content = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$db_user');", $config_content);
                $config_content = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$db_pass');", $config_content);
                $config_content = str_replace("define('DB_NAME', 'lotus_tender');", "define('DB_NAME', '$db_name');", $config_content);
                
                file_put_contents('config.php', $config_content);
                
                // ایجاد پوشه tender اگر وجود ندارد
                if (!is_dir('tender')) {
                    mkdir('tender', 0755, true);
                }
                
                $success_message = '
                    <div class="success-details">
                        <h3>🎉 نصب با موفقیت انجام شد!</h3>
                        <p>2 کاربر مدیر ایجاد شد.</p>
                        
                        <div class="users-info">
                            <div class="user-card">
                                <h4>👤 مدیر اصلی</h4>
                                <ul>
                                    <li>نام: <code>' . htmlspecialchars($admin1_name) . '</code></li>
                                    <li>نام کاربری: <code>' . htmlspecialchars($admin1_user) . '</code></li>
                                    <li>رمز عبور: <code>' . htmlspecialchars($admin1_pass) . '</code></li>
                                </ul>
                            </div>
                            
                            <div class="user-card">
                                <h4>👤 مدیر دوم</h4>
                                <ul>
                                    <li>نام: <code>' . htmlspecialchars($admin2_name) . '</code></li>
                                    <li>نام کاربری: <code>' . htmlspecialchars($admin2_user) . '</code></li>
                                    <li>رمز عبور: <code>' . htmlspecialchars($admin2_pass) . '</code></li>
                                </ul>
                            </div>
                        </div>
                        
                        <p class="warning"><strong>⚠️ هشدار امنیتی:</strong> لطفاً فایل install.php را حذف کنید!</p>
                        <div class="install-actions">
                            <a href="index.php" class="btn btn-primary">🌐 مشاهده سایت</a>
                            <a href="login.php" class="btn btn-success">🔐 ورود به پنل مدیریت</a>
                        </div>
                    </div>
                ';
                $install_complete = true;
            } else {
                $error_message = 'خطا در ایجاد جداول: ' . $conn->error;
            }
        } else {
            $error_message = 'خطا در ایجاد دیتابیس: ' . $conn->error;
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
    <title>نصب سیستم مناقصات لوتوس</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .install-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .install-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .install-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .install-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .install-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: inherit;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            color: #1e3c72;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(17, 153, 142, 0.4);
        }
        
        .error-message {
            background: #ffe6e6;
            color: #d32f2f;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-right: 4px solid #d32f2f;
        }
        
        .success-message {
            background: #e6ffe6;
            color: #2e7d32;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-right: 4px solid #2e7d32;
        }
        
        .success-details {
            text-align: center;
        }
        
        .success-details h3 {
            margin-bottom: 15px;
            color: #2e7d32;
        }
        
        .users-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .user-card {
            background: white;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            text-align: right;
        }
        
        .user-card h4 {
            color: #1e3c72;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .user-card ul {
            list-style: none;
        }
        
        .user-card li {
            padding: 5px 0;
            font-size: 13px;
        }
        
        .user-card code {
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 4px;
            color: #667eea;
            font-size: 12px;
        }
        
        .success-details .warning {
            color: #f57c00;
            background: #fff3e0;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .install-actions {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 18px;
        }
        
        .user-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .user-block {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }
        
        .user-block h4 {
            color: #667eea;
            margin-bottom: 15px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .warning-box {
            background: #fff3e0;
            border: 2px solid #ff9800;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #e65100;
        }
        
        @media (max-width: 600px) {
            .user-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🔧 نصب سیستم مناقصات لوتوس</h1>
            <p>مرحله نصب و پیکربندی اولیه - نسخه 2.1</p>
        </div>
        
        <div class="install-body">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$install_complete): ?>
                <div class="warning-box">
                    <strong>⚠️ توجه:</strong> اگر قبلاً نصب کرده‌اید، تمام داده‌های قبلی پاک خواهد شد!
                </div>
                
                <form method="POST" action="">
                    <div class="form-section">
                        <h3>📊 تنظیمات دیتابیس</h3>
                        <div class="form-group">
                            <label>آدرس سرور دیتابیس:</label>
                            <input type="text" name="db_host" value="localhost" required>
                        </div>
                        <div class="form-group">
                            <label>نام کاربری دیتابیس:</label>
                            <input type="text" name="db_user" value="root" required>
                        </div>
                        <div class="form-group">
                            <label>رمز عبور دیتابیس:</label>
                            <input type="text" name="db_pass" value="">
                        </div>
                        <div class="form-group">
                            <label>نام دیتابیس:</label>
                            <input type="text" name="db_name" value="lotus_tender" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>👤 مدیریت کاربران</h3>
                        <p style="margin-bottom: 20px; color: #666; font-size: 13px;">
                            دو کاربر مدیر به صورت پیش‌فرض ایجاد می‌شوند. می‌توانید اطلاعات را تغییر دهید.
                        </p>
                        
                        <div class="user-section">
                            <div class="user-block">
                                <h4>🔐 مدیر اصلی (admin)</h4>
                                <div class="form-group">
                                    <label>نام و نام خانوادگی:</label>
                                    <input type="text" name="admin1_name" value="مدیر اصلی">
                                </div>
                                <div class="form-group">
                                    <label>نام کاربری:</label>
                                    <input type="text" name="admin1_user" value="admin" required>
                                </div>
                                <div class="form-group">
                                    <label>رمز عبور:</label>
                                    <input type="text" name="admin1_pass" value="admin123" required>
                                </div>
                            </div>
                            
                            <div class="user-block">
                                <h4>👤 مدیر دوم (manager)</h4>
                                <div class="form-group">
                                    <label>نام و نام خانوادگی:</label>
                                    <input type="text" name="admin2_name" value="مدیر دوم">
                                </div>
                                <div class="form-group">
                                    <label>نام کاربری:</label>
                                    <input type="text" name="admin2_user" value="manager" required>
                                </div>
                                <div class="form-group">
                                    <label>رمز عبور:</label>
                                    <input type="text" name="admin2_pass" value="manager123" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary submit-btn">
                        🚀 شروع نصب
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
