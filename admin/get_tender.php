<?php
/**
 * دریافت اطلاعات مناقصه برای ویرایش
 * Get Tender Data for Editing (AJAX)
 */

require_once __DIR__ . '/../config.php';
requireAdminLogin();

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'شناسه نامعتبر']);
    exit;
}

$conn = getDBConnection();

$sql = "SELECT * FROM tenders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $tender = $result->fetch_assoc();
    
    // تبدیل تاریخ میلادی به شمسی
    $tender['deadline_jalali'] = gregorianToJalali($tender['deadline']);
    
    // پردازش فایل‌ها
    $files = !empty($tender['files']) ? json_decode($tender['files'], true) : [];
    
    echo json_encode([
        'success' => true,
        'tender' => $tender,
        'files' => $files
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'مناقصه یافت نشد']);
}

$conn->close();
