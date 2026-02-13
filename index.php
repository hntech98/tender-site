<?php
/**
 * صفحه اصلی سایت - نمایش لیست مناقصات
 * Homepage - Tender List Display
 */

$page_title = 'صفحه اصلی';
require_once __DIR__ . '/includes/header.php';

// اتصال به دیتابیس
$conn = getDBConnection();

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

<div class="container">
    <div class="tenders-section fade-in">
        <div class="section-header">
            <h1 class="section-title">
                <span>📋</span>
                لیست مناقصات فعال
            </h1>
            <span class="tenders-count">
                تعداد: <?php echo count($tenders); ?> مناقصه
            </span>
        </div>
        
        <?php if (count($tenders) > 0): ?>
            <table class="tenders-table">
                <thead>
                    <tr>
                        <th class="row-number">ردیف</th>
                        <th>نام پروژه</th>
                        <th>شرح مناقصه</th>
                        <th>مهلت ارسال پاکت</th>
                        <th>دانلود فایل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $row_num = 1;
                    foreach ($tenders as $tender): 
                        $expired = isExpired($tender['deadline']);
                        $deadline_shamsi = gregorianToJalali($tender['deadline']);
                        
                        // پردازش فایل‌ها
                        $files = [];
                        if (!empty($tender['files'])) {
                            $files = json_decode($tender['files'], true);
                        }
                    ?>
                        <tr>
                            <td class="row-number"><?php echo $row_num++; ?></td>
                            <td class="project-name"><?php echo htmlspecialchars($tender['project_name']); ?></td>
                            <td class="description"><?php echo nl2br(htmlspecialchars($tender['description'])); ?></td>
                            <td>
                                <?php if (!empty($deadline_shamsi)): ?>
                                    <span class="deadline <?php echo $expired ? 'deadline-expired' : 'deadline-active'; ?>">
                                        <?php echo $deadline_shamsi; ?>
                                        <?php echo $expired ? '(منقضی شده)' : ''; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="deadline">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (count($files) > 0): ?>
                                    <?php foreach ($files as $file): ?>
                                        <?php 
                                        $file_path = 'tender/' . $file['name'];
                                        $file_size = '';
                                        if (file_exists($file_path)) {
                                            $file_size = formatFileSize(filesize($file_path));
                                        }
                                        ?>
                                        <a href="download.php?id=<?php echo $tender['id']; ?>&file=<?php echo urlencode($file['name']); ?>" 
                                           class="download-btn">
                                            <span>📥</span>
                                            <span><?php echo htmlspecialchars($file['name']); ?></span>
                                            <?php if ($file_size): ?>
                                                <span class="file-size">(<?php echo $file_size; ?>)</span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: #999;">فایلی موجود نیست</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-message">
                <div class="icon">📋</div>
                <h3>مناقصه‌ای یافت نشد</h3>
                <p>در حال حاضر مناقصه فعالی وجود ندارد.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$conn->close();
require_once __DIR__ . '/includes/footer.php'; 
?>
