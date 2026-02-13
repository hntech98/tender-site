/**
 * اسکریپت‌های اصلی سیستم مناقصات لوتوس
 * Lotus Tender Management System - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // فعال‌سازی مودال‌ها
    initModals();
    
    // فعال‌سازی حذف با تایید
    initDeleteButtons();
    
    // فعال‌سازی فرم آپلود فایل
    initFileUpload();
    
    // فعال‌سازی انتخاب تاریخ شمسی
    initDatePickers();
});

/**
 * مدیریت مودال‌ها
 */
function initModals() {
    // بستن مودال با کلیک خارج از آن
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
    
    // بستن با دکمه Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                closeModal(modal);
            });
        }
    });
}

/**
 * باز کردن مودال
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * بستن مودال
 */
function closeModal(modal) {
    if (typeof modal === 'string') {
        modal = document.getElementById(modal);
    }
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * مدیریت دکمه‌های حذف
 */
function initDeleteButtons() {
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm-delete') || 'آیا از حذف مطمئن هستید؟';
            if (confirm(message)) {
                // ادامه عملیات حذف
                const form = this.closest('form');
                if (form) {
                    form.submit();
                } else {
                    const href = this.getAttribute('href');
                    if (href) {
                        window.location.href = href;
                    }
                }
            }
        });
    });
}

/**
 * مدیریت آپلود فایل
 */
function initFileUpload() {
    const uploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.querySelector('#files');
    const fileList = document.querySelector('.file-list');
    
    if (!uploadArea || !fileInput) return;
    
    // کلیک روی ناحیه آپلود
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Drag and Drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });
    
    // انتخاب فایل
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        if (!fileList) return;
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileSize = formatFileSize(file.size);
            
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item fade-in';
            fileItem.innerHTML = `
                <span class="file-name">
                    <span>📄</span>
                    <span>${file.name}</span>
                    <span class="file-size">(${fileSize})</span>
                </span>
                <span class="remove-file" onclick="this.parentElement.remove()">✕</span>
            `;
            
            fileList.appendChild(fileItem);
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }
}

/**
 * انتخاب تاریخ شمسی
 */
function initDatePickers() {
    const dateInputs = document.querySelectorAll('.jalali-datepicker');
    
    dateInputs.forEach(input => {
        input.setAttribute('placeholder', 'مثال: 1403/01/15');
        
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            
            if (value.length > 4) {
                value = value.substring(0, 4) + '/' + value.substring(4);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + '/' + value.substring(7, 9);
            }
            
            this.value = value;
        });
    });
}

/**
 * نمایش پیام
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} fade-in`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

/**
 * تایید قبل از اقدام
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
