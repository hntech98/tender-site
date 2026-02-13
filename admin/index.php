<?php
/**
 * پنل مدیریت - داشبورد
 * Admin Panel - Dashboard
 */

require_once __DIR__ . '/../config.php';
requireAdminLogin();

// اتصال به دیتابیس
$conn = getDBConnection();

// دریافت آمار
$total_tenders = 0;
$total_downloads = 0;
$active_tenders = 0;

// تعداد کل مناقصات
$result = $conn->query("SELECT COUNT(*) as count FROM tenders");
if ($result) {
    $total_tenders = $result->fetch_assoc()['count'];
}

// تعداد کل دانلودها
$result = $conn->query("SELECT SUM(download_count) as total FROM tenders");
if ($result) {
    $total_downloads = $result->fetch_assoc()['total'] ?? 0;
}

// تعداد مناقصات فعال
$today = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) as count FROM tenders WHERE deadline >= '$today' OR deadline IS NULL");
if ($result) {
    $active_tenders = $result->fetch_assoc()['count'];
}

// دریافت لیست مناقصات
$sql = "SELECT * FROM tenders ORDER BY created_at DESC";
$result = $conn->query($sql);
$tenders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tenders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت - <?php echo SITE_NAME; ?></title>
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
                    <a href="index.php" class="active">
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
                    <a href="settings.php">
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
                    <span>📊</span>
                    داشبورد مدیریت
                </h1>
                <div class="admin-actions">
                    <button onclick="openModal('addTenderModal')" class="btn btn-success">
                        <span>➕</span>
                        اضافه کردن مناقصه جدید
                    </button>
                </div>
            </div>
            
            <!-- کارت‌های آمار -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?php echo $total_tenders; ?></div>
                    <div class="stat-label">کل مناقصات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo $active_tenders; ?></div>
                    <div class="stat-label">مناقصات فعال</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📥</div>
                    <div class="stat-value"><?php echo $total_downloads; ?></div>
                    <div class="stat-label">کل دانلودها</div>
                </div>
            </div>
            
            <!-- جدول مناقصات -->
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">ردیف</th>
                            <th>نام پروژه</th>
                            <th>شرح</th>
                            <th>مهلت ارسال</th>
                            <th>فایل‌ها</th>
                            <th>دانلودها</th>
                            <th style="width: 180px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tenders) > 0): ?>
                            <?php $row_num = 1; foreach ($tenders as $tender): ?>
                                <?php 
                                $expired = isExpired($tender['deadline']);
                                $deadline_shamsi = gregorianToJalali($tender['deadline']);
                                $files = !empty($tender['files']) ? json_decode($tender['files'], true) : [];
                                ?>
                                <tr>
                                    <td><?php echo $row_num++; ?></td>
                                    <td class="project-name"><?php echo htmlspecialchars($tender['project_name']); ?></td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars(mb_substr($tender['description'], 0, 50)); ?>...
                                    </td>
                                    <td>
                                        <?php if (!empty($deadline_shamsi)): ?>
                                            <span class="deadline <?php echo $expired ? 'deadline-expired' : 'deadline-active'; ?>">
                                                <?php echo $deadline_shamsi; ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo count($files); ?> فایل</td>
                                    <td>
                                        <span class="download-count">
                                            📥 <?php echo $tender['download_count']; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <button onclick="openEditModal(<?php echo $tender['id']; ?>)" class="btn btn-warning btn-sm">
                                            ✏️ اصلاح
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $tender['id']; ?>)" class="btn btn-danger btn-sm">
                                            🗑️ حذف
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    هنوز مناقصه‌ای ثبت نشده است.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- مودال اضافه کردن مناقصه -->
    <div id="addTenderModal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>➕ اضافه کردن مناقصه جدید</h3>
                <button class="modal-close" onclick="closeModal('addTenderModal')">&times;</button>
            </div>
            <form action="actions.php" method="POST" enctype="multipart/form-data" id="addTenderForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>نام پروژه: *</label>
                        <input type="text" name="project_name" required placeholder="نام پروژه را وارد کنید">
                    </div>
                    
                    <div class="form-group">
                        <label>شرح مناقصه:</label>
                        <textarea name="description" rows="4" placeholder="شرح کامل مناقصه را وارد کنید"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>مهلت ارسال پاکت دربسته (تاریخ شمسی):</label>
                        <input type="text" name="deadline" class="jalali-datepicker" placeholder="مثال: 1403/01/15">
                    </div>
                    
                    <div class="form-group">
                        <label>فایل‌های ضمیمه:</label>
                        <div class="file-upload-area">
                            <div class="upload-icon">📁</div>
                            <p>فایل‌ها را اینجا بکشید یا کلیک کنید</p>
                            <p style="font-size: 12px; color: #999;">فرمت‌های مجاز: PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR</p>
                        </div>
                        <input type="file" id="files" name="files[]" multiple style="display: none;">
                        <div class="file-list"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">💾 ثبت</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addTenderModal')">❌ انصراف</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- مودال ویرایش -->
    <div id="editTenderModal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>✏️ ویرایش مناقصه</h3>
                <button class="modal-close" onclick="closeModal('editTenderModal')">&times;</button>
            </div>
            <form action="actions.php" method="POST" enctype="multipart/form-data" id="editTenderForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label>نام پروژه: *</label>
                        <input type="text" name="project_name" id="edit_project_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>شرح مناقصه:</label>
                        <textarea name="description" id="edit_description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>مهلت ارسال پاکت دربسته (تاریخ شمسی):</label>
                        <input type="text" name="deadline" id="edit_deadline" class="jalali-datepicker">
                    </div>
                    
                    <div class="form-group">
                        <label>فایل‌های موجود:</label>
                        <div id="existing_files"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>افزودن فایل جدید:</label>
                        <input type="file" name="new_files[]" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">💾 ذخیره تغییرات</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editTenderModal')">❌ انصراف</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- مودال تایید حذف -->
    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 400px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
                <h3>🗑️ تایید حذف</h3>
                <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirm-message">
                    <div class="icon">⚠️</div>
                    <h4>آیا از حذف این مناقصه مطمئن هستید؟</h4>
                    <p>این عملیات قابل بازگشت نیست.</p>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <form action="actions.php" method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="submit" class="btn btn-danger">🗑️ بله، حذف شود</button>
                </form>
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">❌ انصراف</button>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // باز کردن مودال ویرایش
        function openEditModal(id) {
            // دریافت اطلاعات مناقصه با AJAX
            fetch('get_tender.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_id').value = data.tender.id;
                        document.getElementById('edit_project_name').value = data.tender.project_name;
                        document.getElementById('edit_description').value = data.tender.description || '';
                        document.getElementById('edit_deadline').value = data.tender.deadline_jalali || '';
                        
                        // نمایش فایل‌های موجود
                        const filesDiv = document.getElementById('existing_files');
                        if (data.files.length > 0) {
                            filesDiv.innerHTML = data.files.map(f => `
                                <div class="file-item">
                                    <span class="file-name">📄 ${f.name}</span>
                                    <label>
                                        <input type="checkbox" name="delete_files[]" value="${f.name}">
                                        حذف
                                    </label>
                                </div>
                            `).join('');
                        } else {
                            filesDiv.innerHTML = '<p style="color: #999;">فایلی وجود ندارد</p>';
                        }
                        
                        openModal('editTenderModal');
                    } else {
                        alert('خطا در دریافت اطلاعات');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('خطا در ارتباط با سرور');
                });
        }
        
        // تایید حذف
        function confirmDelete(id) {
            document.getElementById('delete_id').value = id;
            openModal('deleteModal');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
