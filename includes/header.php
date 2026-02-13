<?php
/**
 * فایل هدر سایت
 * Header File
 */
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <!-- هدر سایت -->
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <a href="index.php">
                        <img src="assets/images/logo.png" alt="لوگوی لوتوس" class="logo" onerror="this.style.display='none'">
                        <div class="site-title">
                            <span class="company-name">شرکت لوتوس</span>
                            <span class="unit-name"><?php echo SITE_NAME; ?></span>
                        </div>
                    </a>
                </div>
                <nav class="header-nav">
                    <a href="index.php" class="nav-link">صفحه اصلی</a>
                    <?php if (isAdminLoggedIn()): ?>
                        <a href="admin/" class="nav-link admin-link">پنل مدیریت</a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link admin-link">ورود به مدیریت</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
    
    <!-- محتوای اصلی -->
    <main class="main-content">
