<?php
/**
 * خروج از پنل مدیریت
 * Admin Logout
 */

require_once __DIR__ . '/../config.php';

// حذف سشن
session_unset();
session_destroy();

// هدایت به صفحه لاگین
header('Location: ../login.php');
exit;
