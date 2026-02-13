<?php
/**
 * فایل تنظیمات نمونه - برای گیت‌هاب
 * Sample Configuration File for GitHub
 * 
 * دستورالعمل:
 * 1. این فایل را به config.php تغییر نام دهید
 * 2. مقادیر دیتابیس خود را وارد کنید
 */

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');        // آدرس سرور دیتابیس
define('DB_USER', 'your_username');    // نام کاربری دیتابیس
define('DB_PASS', 'your_password');    // رمز عبور دیتابیس
define('DB_NAME', 'lotus_tender');     // نام دیتابیس
define('DB_CHARSET', 'utf8mb4');

// تنظیمات سایت
define('SITE_NAME', 'واحد مناقصات لوتوس');
define('SITE_URL', 'http://localhost/lotus-tender');

// تنظیمات آپلود
define('UPLOAD_DIR', __DIR__ . '/tender/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50 مگابایت

// تنظیمات منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * اتصال به دیتابیس
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
    }
    
    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * تبدیل تاریخ میلادی به شمسی
 */
function gregorianToJalali($gregorian_date) {
    if (empty($gregorian_date) || $gregorian_date == '0000-00-00') {
        return '';
    }
    
    $parts = explode('-', $gregorian_date);
    if (count($parts) != 3) {
        return $gregorian_date;
    }
    
    $g_y = (int)$parts[0];
    $g_m = (int)$parts[1];
    $g_d = (int)$parts[2];
    
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    
    $gy = $g_y - 1600;
    $gm = $g_m - 1;
    $gd = $g_d - 1;
    
    $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);
    
    for ($i = 0; $i < $gm; ++$i)
        $g_day_no += $g_days_in_month[$i];
    
    if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
        ++$g_day_no;
    
    $g_day_no += $gd;
    
    $j_day_no = $g_day_no - 79;
    
    $j_np = floor($j_day_no / 12053);
    $j_day_no %= 12053;
    
    $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461);
    $j_day_no %= 1461;
    
    if ($j_day_no >= 366) {
        $jy += floor(($j_day_no - 1) / 365);
        $j_day_no = ($j_day_no - 1) % 365;
    }
    
    for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
        $j_day_no -= $j_days_in_month[$i];
    
    $jm = $i + 1;
    $jd = $j_day_no + 1;
    
    return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
}

/**
 * تبدیل تاریخ شمسی به میلادی
 */
function jalaliToGregorian($jalali_date) {
    if (empty($jalali_date)) {
        return '';
    }
    
    $parts = explode('/', $jalali_date);
    if (count($parts) != 3) {
        return $jalali_date;
    }
    
    $j_y = (int)$parts[0];
    $j_m = (int)$parts[1];
    $j_d = (int)$parts[2];
    
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    
    $jy = $j_y - 979;
    $jm = $j_m - 1;
    $jd = $j_d - 1;
    
    $j_day_no = 365 * $jy + floor($jy / 33) * 8 + floor(($jy % 33 + 3) / 4);
    
    for ($i = 0; $i < $jm; ++$i)
        $j_day_no += $j_days_in_month[$i];
    
    $j_day_no += $jd;
    
    $g_day_no = $j_day_no + 79;
    
    $gy = 1600 + 400 * floor($g_day_no / 146097);
    $g_day_no %= 146097;
    
    $leap = true;
    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100 * floor($g_day_no / 36524);
        $g_day_no %= 36524;
        if ($g_day_no >= 365)
            $g_day_no++;
        else
            $leap = false;
    }
    
    $gy += 4 * floor($g_day_no / 1461);
    $g_day_no %= 1461;
    
    if ($g_day_no >= 366) {
        $leap = false;
        $g_day_no--;
        $gy += floor($g_day_no / 365);
        $g_day_no %= 365;
    }
    
    for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); ++$i)
        $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
    
    $gm = $i + 1;
    $gd = $g_day_no + 1;
    
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}

/**
 * بررسی تاریخ انقضا
 */
function isExpired($deadline_date) {
    if (empty($deadline_date)) {
        return false;
    }
    
    $today = date('Y-m-d');
    return strtotime($deadline_date) < strtotime($today);
}

/**
 * فرمت کردن حجم فایل
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * پاکسازی ورودی
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * بررسی ورود مدیر
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * محافظت از صفحه مدیریت
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
