<?php
/**
 * عملیات مدیریت مناقصات
 * Tender Management Actions
 */

require_once __DIR__ . '/../config.php';
requireAdminLogin();

// اتصال به دیتابیس
$conn = getDBConnection();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        handleAddTender($conn);
        break;
    case 'edit':
        handleEditTender($conn);
        break;
    case 'delete':
        handleDeleteTender($conn);
        break;
    default:
        $_SESSION['error'] = 'عملیات نامعتبر!';
        header('Location: index.php');
        exit;
}

/**
 * اضافه کردن مناقصه جدید
 */
function handleAddTender($conn) {
    $project_name = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline_jalali = trim($_POST['deadline'] ?? '');
    
    // اعتبارسنجی
    if (empty($project_name)) {
        $_SESSION['error'] = 'نام پروژه الزامی است!';
        header('Location: index.php');
        exit;
    }
    
    // تبدیل تاریخ شمسی به میلادی
    $deadline_gregorian = null;
    if (!empty($deadline_jalali)) {
        $deadline_gregorian = jalaliToGregorian($deadline_jalali);
    }
    
    // پردازش فایل‌های آپلود شده
    $files = [];
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $files = handleFileUpload($_FILES['files']);
    }
    
    // ذخیره در دیتابیس
    $files_json = json_encode($files, JSON_UNESCAPED_UNICODE);
    
    $sql = "INSERT INTO tenders (project_name, description, deadline, files) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $project_name, $description, $deadline_gregorian, $files_json);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'مناقصه با موفقیت اضافه شد.';
    } else {
        $_SESSION['error'] = 'خطا در ذخیره اطلاعات: ' . $conn->error;
    }
    
    header('Location: index.php');
    exit;
}

/**
 * ویرایش مناقصه
 */
function handleEditTender($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $project_name = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline_jalali = trim($_POST['deadline'] ?? '');
    
    // اعتبارسنجی
    if ($id <= 0 || empty($project_name)) {
        $_SESSION['error'] = 'اطلاعات نامعتبر!';
        header('Location: index.php');
        exit;
    }
    
    // دریافت فایل‌های موجود
    $sql = "SELECT files FROM tenders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tender = $result->fetch_assoc();
    
    $existing_files = !empty($tender['files']) ? json_decode($tender['files'], true) : [];
    
    // حذف فایل‌های انتخاب شده
    $delete_files = $_POST['delete_files'] ?? [];
    foreach ($delete_files as $file_to_delete) {
        $file_path = UPLOAD_DIR . $file_to_delete;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $existing_files = array_filter($existing_files, function($f) use ($file_to_delete) {
            return $f['name'] !== $file_to_delete;
        });
    }
    
    // اضافه کردن فایل‌های جدید
    if (isset($_FILES['new_files']) && is_array($_FILES['new_files']['name'])) {
        $new_files = handleFileUpload($_FILES['new_files']);
        $existing_files = array_merge($existing_files, $new_files);
    }
    
    // تبدیل تاریخ شمسی به میلادی
    $deadline_gregorian = null;
    if (!empty($deadline_jalali)) {
        $deadline_gregorian = jalaliToGregorian($deadline_jalali);
    }
    
    // به‌روزرسانی دیتابیس
    $files_json = json_encode(array_values($existing_files), JSON_UNESCAPED_UNICODE);
    
    $sql = "UPDATE tenders SET project_name = ?, description = ?, deadline = ?, files = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $project_name, $description, $deadline_gregorian, $files_json, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'اطلاعات با موفقیت به‌روزرسانی شد.';
    } else {
        $_SESSION['error'] = 'خطا در به‌روزرسانی: ' . $conn->error;
    }
    
    header('Location: index.php');
    exit;
}

/**
 * حذف مناقصه
 */
function handleDeleteTender($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        $_SESSION['error'] = 'شناسه نامعتبر!';
        header('Location: index.php');
        exit;
    }
    
    // دریافت فایل‌های مناقصه برای حذف
    $sql = "SELECT files FROM tenders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tender = $result->fetch_assoc();
    
    if ($tender) {
        $files = !empty($tender['files']) ? json_decode($tender['files'], true) : [];
        
        // حذف فایل‌ها
        foreach ($files as $file) {
            $file_path = UPLOAD_DIR . $file['name'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // حذف رکورد از دیتابیس
        $sql = "DELETE FROM tenders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'مناقصه با موفقیت حذف شد.';
        } else {
            $_SESSION['error'] = 'خطا در حذف: ' . $conn->error;
        }
    } else {
        $_SESSION['error'] = 'مناقصه یافت نشد!';
    }
    
    header('Location: index.php');
    exit;
}

/**
 * مدیریت آپلود فایل
 */
function handleFileUpload($files_array) {
    $uploaded_files = [];
    
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png'];
    
    for ($i = 0; $i < count($files_array['name']); $i++) {
        $file_name = $files_array['name'][$i];
        $file_tmp = $files_array['tmp_name'][$i];
        $file_size = $files_array['size'][$i];
        $file_error = $files_array['error'][$i];
        
        // بررسی خطا
        if ($file_error !== UPLOAD_ERR_OK) {
            continue;
        }
        
        // بررسی حجم
        if ($file_size > MAX_FILE_SIZE) {
            continue;
        }
        
        // بررسی پسوند
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_extensions)) {
            continue;
        }
        
        // تولید نام یکتا
        $new_name = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $file_name);
        $destination = UPLOAD_DIR . $new_name;
        
        // انتقال فایل
        if (move_uploaded_file($file_tmp, $destination)) {
            $uploaded_files[] = [
                'name' => $new_name,
                'original_name' => $file_name,
                'size' => $file_size
            ];
        }
    }
    
    return $uploaded_files;
}

$conn->close();
