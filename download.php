<?php
/**
 * فایل دانلود اسناد مناقصه
 * Tender Documents Download Handler
 */

require_once __DIR__ . '/config.php';

// دریافت پارامترها
$tender_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$file_name = isset($_GET['file']) ? $_GET['file'] : '';

// اعتبارسنجی
if ($tender_id <= 0 || empty($file_name)) {
    die('پارامترهای نامعتبر!');
}

// جلوگیری از Directory Traversal
$file_name = basename($file_name);
$file_path = UPLOAD_DIR . $file_name;

// بررسی وجود فایل
if (!file_exists($file_path)) {
    die('فایل مورد نظر یافت نشد!');
}

// اتصال به دیتابیس
$conn = getDBConnection();

// افزایش تعداد دانلود
$update_sql = "UPDATE tenders SET download_count = download_count + 1 WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param('i', $tender_id);
$stmt->execute();

// ثبت دانلود در جدول downloads
$ip_address = $_SERVER['REMOTE_ADDR'];
$insert_sql = "INSERT INTO downloads (tender_id, file_name, ip_address) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param('iss', $tender_id, $file_name, $ip_address);
$stmt->execute();

$conn->close();

// تعیین نوع فایل
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$mime_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];

$mime_type = $mime_types[$file_extension] ?? 'application/octet-stream';

// ارسال هدرها
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

// خواندن و ارسال فایل
readfile($file_path);
exit;
