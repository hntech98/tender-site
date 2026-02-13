#!/bin/bash

#####################################################################
# اسکریپت نصب خودکار سیستم مناقصات لوتوس
# Lotus Tender Management System - Ubuntu 20.04/22.04 Auto Installer
# نسخه 2.3
# 
# repository: https://github.com/hntech98/tender-site
# 
# نحوه اجرا:
# git clone https://github.com/hntech98/tender-site.git
# cd tender-site/scripts
# chmod +x install-ubuntu.sh
# sudo ./install-ubuntu.sh
#####################################################################

# رنگ‌ها برای نمایش پیام‌ها
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[1;33m'
NC='\033[0m' # No Color

# لوگوی سیستم
echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                                                            ║"
echo "║          سیستم مدیریت مناقصات شرکت لوتوس                  ║"
echo "║          Lotus Tender Management System                    ║"
echo "║                                                            ║"
echo "║          نسخه: 2.3                                         ║"
echo "║          نصب‌کننده خودکار اوبونتو                          ║"
echo "║                                                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# بررسی اجرا با sudo
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}خطا: این اسکریپت باید با sudo اجرا شود!${NC}"
   echo "مثال: sudo ./install-ubuntu.sh"
   exit 1
fi

# تشخیص نسخه اوبونتو
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
    echo -e "${GREEN}سیستم عامل تشخیص داده شده: $OS $VER${NC}"
else
    echo -e "${RED}خطا: تشخیص سیستم عامل ناممکن!${NC}"
    exit 1
fi

# تنظیمات پیش‌فرض
DB_NAME="lotus_tender"
DB_USER="lotus_user"
DB_PASS="Lotus@2024!Secure"
ADMIN1_USER="admin"
ADMIN1_PASS="admin123"
ADMIN2_USER="manager"
ADMIN2_PASS="manager123"
SITE_DOMAIN="lotus.local"
INSTALL_DIR="/var/www/html/"

echo ""
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}                   تنظیمات نصب${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""

# دریافت اطلاعات از کاربر
read -p "$(echo -e ${BLUE}'نام دیتابیس ['$DB_NAME']: '${NC})" input
DB_NAME=${input:-$DB_NAME}

read -p "$(echo -e ${BLUE}'نام کاربری دیتابیس ['$DB_USER']: '${NC})" input
DB_USER=${input:-$DB_USER}

read -p "$(echo -e ${BLUE}'رمز عبور دیتابیس ['$DB_PASS']: '${NC})" input
DB_PASS=${input:-$DB_PASS}

read -p "$(echo -e ${BLUE}'نام کاربری مدیر اصلی ['$ADMIN1_USER']: '${NC})" input
ADMIN1_USER=${input:-$ADMIN1_USER}

read -p "$(echo -e ${BLUE}'رمز عبور مدیر اصلی ['$ADMIN1_PASS']: '${NC})" input
ADMIN1_PASS=${input:-$ADMIN1_PASS}

read -p "$(echo -e ${BLUE}'نام کاربری مدیر دوم ['$ADMIN2_USER']: '${NC})" input
ADMIN2_USER=${input:-$ADMIN2_USER}

read -p "$(echo -e ${BLUE}'رمز عبور مدیر دوم ['$ADMIN2_PASS']: '${NC})" input
ADMIN2_PASS=${input:-$ADMIN2_PASS}

read -p "$(echo -e ${BLUE}'دامنه سایت ['$SITE_DOMAIN']: '${NC})" input
SITE_DOMAIN=${input:-$SITE_DOMAIN}

echo ""
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}                   شروع نصب${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
echo ""

# تابع برای نمایش وضعیت
show_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

show_error() {
    echo -e "${RED}[✗]${NC} $1"
}

show_progress() {
    echo -e "${BLUE}[...]${NC} $1"
}

# مرحله 1: به‌روزرسانی سیستم
echo ""
show_progress "به‌روزرسانی مخازن بسته‌ها..."
apt update -qq

# مرحله 2: نصب Apache
echo ""
show_progress "نصب وب‌سرور Apache..."
apt install -y apache2 -qq
systemctl enable apache2
systemctl start apache2
show_status "Apache نصب و فعال شد"

# مرحله 3: نصب MySQL
echo ""
show_progress "نصب سرور پایگاه داده MySQL..."
export DEBIAN_FRONTEND=noninteractive
apt install -y mysql-server -qq
systemctl enable mysql
systemctl start mysql
show_status "MySQL نصب و فعال شد"

# مرحله 4: نصب PHP
echo ""
show_progress "نصب PHP و پسوندهای مورد نیاز..."
apt install -y php libapache2-mod-php php-mysql php-json php-mbstring php-xml php-curl php-zip -qq
show_status "PHP نصب شد"

# نمایش نسخه PHP
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
show_status "نسخه PHP: $PHP_VERSION"

# مرحله 5: تنظیم MySQL
echo ""
show_progress "تنظیم امنیتی MySQL..."
mysql --user=root <<EOF
-- ایجاد دیتابیس
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;

-- ایجاد کاربر
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';

-- اعطای دسترسی‌ها
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
show_status "دیتابیس و کاربر ایجاد شد"

# مرحله 6: یافتن و کپی فایل‌های پروژه
echo ""
show_progress "جستجوی فایل‌های پروژه..."

# مسیر اسکریپت فعلی
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SOURCE_DIR=""

# بررسی مسیرهای مختلف (وقتی از git clone می‌آید)
if [ -f "$SCRIPT_DIR/../index.php" ]; then
    SOURCE_DIR="$SCRIPT_DIR/.."
    show_status "فایل‌ها یافت شد: $SOURCE_DIR"
elif [ -f "$SCRIPT_DIR/index.php" ]; then
    SOURCE_DIR="$SCRIPT_DIR"
    show_status "فایل‌ها یافت شد: $SOURCE_DIR"
elif [ -f "$(pwd)/../index.php" ]; then
    SOURCE_DIR="$(pwd)/.."
    show_status "فایل‌ها یافت شد: $SOURCE_DIR"
elif [ -f "$(pwd)/index.php" ]; then
    SOURCE_DIR="$(pwd)"
    show_status "فایل‌ها یافت شد: $SOURCE_DIR"
fi

# ایجاد پوشه نصب
mkdir -p "$INSTALL_DIR"

if [ -n "$SOURCE_DIR" ] && [ -f "$SOURCE_DIR/index.php" ]; then
    show_progress "کپی فایل‌های پروژه به $INSTALL_DIR ..."
    cp -r "$SOURCE_DIR"/* "$INSTALL_DIR/" 2>/dev/null
    cp "$SOURCE_DIR/.gitignore" "$INSTALL_DIR/" 2>/dev/null
    show_status "فایل‌های پروژه کپی شد"
else
    show_error "فایل‌های پروژه یافت نشد!"
    echo ""
    echo -e "${YELLOW}مسیرهای جستجو شده:${NC}"
    echo "   - $SCRIPT_DIR/.."
    echo "   - $SCRIPT_DIR"
    echo "   - $(pwd)/.."
    echo "   - $(pwd)"
    echo ""
    echo -e "${YELLOW}لطفاً مسیر صحیح را وارد کنید:${NC}"
    read -p "مسیر پروژه: " custom_path
    
    if [ -f "$custom_path/index.php" ]; then
        cp -r "$custom_path"/* "$INSTALL_DIR/"
        show_status "فایل‌ها از $custom_path کپی شد"
    else
        show_error "فایل index.php در مسیر مشخص شده یافت نشد!"
        echo "نصب متوقف شد."
        exit 1
    fi
fi

# مرحله 7: ایجاد پوشه‌های مورد نیاز
echo ""
show_progress "ایجاد ساختار پوشه‌ها..."
mkdir -p "$INSTALL_DIR/tender"
mkdir -p "$INSTALL_DIR/admin"
mkdir -p "$INSTALL_DIR/includes"
mkdir -p "$INSTALL_DIR/assets/css"
mkdir -p "$INSTALL_DIR/assets/js"
mkdir -p "$INSTALL_DIR/assets/images"
show_status "ساختار پوشه‌ها آماده شد"

# مرحله 8: ایجاد/به‌روزرسانی config.php
echo ""
show_progress "تنظیم فایل کانفیگ..."

if [ -f "$INSTALL_DIR/config.example.php" ] && [ ! -f "$INSTALL_DIR/config.php" ]; then
    cp "$INSTALL_DIR/config.example.php" "$INSTALL_DIR/config.php"
fi

if [ ! -f "$INSTALL_DIR/config.php" ]; then
    # ایجاد config.php کامل
    cat > "$INSTALL_DIR/config.php" <<PHPCONFIG
<?php
/**
 * تنظیمات سیستم مناقصات لوتوس
 * Generated by install script
 */

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('DB_NAME', '$DB_NAME');
define('DB_CHARSET', 'utf8mb4');

// تنظیمات سایت
define('SITE_NAME', 'واحد مناقصات لوتوس');
define('SITE_URL', 'http://$SITE_DOMAIN');

// تنظیمات آپلود
define('UPLOAD_DIR', __DIR__ . '/tender/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024);

date_default_timezone_set('Asia/Tehran');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDBConnection() {
    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (\$conn->connect_error) {
        die("خطا در اتصال به دیتابیس: " . \$conn->connect_error);
    }
    \$conn->set_charset(DB_CHARSET);
    return \$conn;
}

function gregorianToJalali(\$gregorian_date) {
    if (empty(\$gregorian_date) || \$gregorian_date == '0000-00-00') return '';
    \$parts = explode('-', \$gregorian_date);
    if (count(\$parts) != 3) return \$gregorian_date;
    \$g_y = (int)\$parts[0]; \$g_m = (int)\$parts[1]; \$g_d = (int)\$parts[2];
    \$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    \$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    \$gy = \$g_y - 1600; \$gm = \$g_m - 1; \$gd = \$g_d - 1;
    \$g_day_no = 365 * \$gy + floor((\$gy + 3) / 4) - floor((\$gy + 99) / 100) + floor((\$gy + 399) / 400);
    for (\$i = 0; \$i < \$gm; ++\$i) \$g_day_no += \$g_days_in_month[\$i];
    if (\$gm > 1 && ((\$gy % 4 == 0 && \$gy % 100 != 0) || (\$gy % 400 == 0))) ++\$g_day_no;
    \$g_day_no += \$gd;
    \$j_day_no = \$g_day_no - 79;
    \$j_np = floor(\$j_day_no / 12053); \$j_day_no %= 12053;
    \$jy = 979 + 33 * \$j_np + 4 * floor(\$j_day_no / 1461); \$j_day_no %= 1461;
    if (\$j_day_no >= 366) { \$jy += floor((\$j_day_no - 1) / 365); \$j_day_no = (\$j_day_no - 1) % 365; }
    for (\$i = 0; \$i < 11 && \$j_day_no >= \$j_days_in_month[\$i]; ++\$i) \$j_day_no -= \$j_days_in_month[\$i];
    \$jm = \$i + 1; \$jd = \$j_day_no + 1;
    return sprintf('%04d/%02d/%02d', \$jy, \$jm, \$jd);
}

function jalaliToGregorian(\$jalali_date) {
    if (empty(\$jalali_date)) return '';
    \$parts = explode('/', \$jalali_date);
    if (count(\$parts) != 3) return \$jalali_date;
    \$j_y = (int)\$parts[0]; \$j_m = (int)\$parts[1]; \$j_d = (int)\$parts[2];
    \$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
    \$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    \$jy = \$j_y - 979; \$jm = \$j_m - 1; \$jd = \$j_d - 1;
    \$j_day_no = 365 * \$jy + floor(\$jy / 33) * 8 + floor((\$jy % 33 + 3) / 4);
    for (\$i = 0; \$i < \$jm; ++\$i) \$j_day_no += \$j_days_in_month[\$i];
    \$j_day_no += \$jd;
    \$g_day_no = \$j_day_no + 79;
    \$gy = 1600 + 400 * floor(\$g_day_no / 146097); \$g_day_no %= 146097;
    \$leap = true;
    if (\$g_day_no >= 36525) { \$g_day_no--; \$gy += 100 * floor(\$g_day_no / 36524); \$g_day_no %= 36524; if (\$g_day_no >= 365) \$g_day_no++; else \$leap = false; }
    \$gy += 4 * floor(\$g_day_no / 1461); \$g_day_no %= 1461;
    if (\$g_day_no >= 366) { \$leap = false; \$g_day_no--; \$gy += floor(\$g_day_no / 365); \$g_day_no %= 365; }
    for (\$i = 0; \$g_day_no >= \$g_days_in_month[\$i] + (\$i == 1 && \$leap); ++\$i) \$g_day_no -= \$g_days_in_month[\$i] + (\$i == 1 && \$leap);
    \$gm = \$i + 1; \$gd = \$g_day_no + 1;
    return sprintf('%04d-%02d-%02d', \$gy, \$gm, \$gd);
}

function isExpired(\$deadline_date) {
    if (empty(\$deadline_date)) return false;
    return strtotime(\$deadline_date) < strtotime(date('Y-m-d'));
}

function formatFileSize(\$bytes) {
    if (\$bytes >= 1073741824) return number_format(\$bytes / 1073741824, 2) . ' GB';
    elseif (\$bytes >= 1048576) return number_format(\$bytes / 1048576, 2) . ' MB';
    elseif (\$bytes >= 1024) return number_format(\$bytes / 1024, 2) . ' KB';
    else return \$bytes . ' bytes';
}

function sanitize(\$data) {
    \$data = trim(\$data); \$data = stripslashes(\$data); \$data = htmlspecialchars(\$data, ENT_QUOTES, 'UTF-8');
    return \$data;
}

function isAdminLoggedIn() {
    return isset(\$_SESSION['admin_logged_in']) && \$_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) { header('Location: login.php'); exit; }
}
PHPCONFIG
    show_status "فایل config.php ایجاد شد"
else
    # به‌روزرسانی config.php موجود
    sed -i "s/define('DB_HOST', '.*');/define('DB_HOST', 'localhost');/" "$INSTALL_DIR/config.php" 2>/dev/null
    sed -i "s/define('DB_USER', '.*');/define('DB_USER', '$DB_USER');/" "$INSTALL_DIR/config.php" 2>/dev/null
    sed -i "s/define('DB_PASS', '.*');/define('DB_PASS', '$DB_PASS');/" "$INSTALL_DIR/config.php" 2>/dev/null
    sed -i "s/define('DB_NAME', '.*');/define('DB_NAME', '$DB_NAME');/" "$INSTALL_DIR/config.php" 2>/dev/null
    show_status "فایل config.php به‌روزرسانی شد"
fi

# مرحله 9: تنظیم مجوزها
echo ""
show_progress "تنظیم مجوزهای دسترسی..."
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 777 "$INSTALL_DIR/tender"
chmod -R 777 "$INSTALL_DIR/assets"
show_status "مجوزها تنظیم شد"

# مرحله 10: ایجاد فایل‌های امنیتی
echo ""
show_progress "ایجاد فایل‌های امنیتی..."

# .htaccess اصلی
cat > "$INSTALL_DIR/.htaccess" <<EOF
# محافظت از فایل‌های حساس
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

Options -Indexes
Options +FollowSymLinks

<IfModule mod_php.c>
    php_value upload_max_filesize 50M
    php_value post_max_size 50M
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 week"
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>
EOF

# .htaccess برای پوشه tender
cat > "$INSTALL_DIR/tender/.htaccess" <<EOF
Options -Indexes
<FilesMatch "\.(pdf|doc|docx|xls|xlsx|zip|rar|jpg|jpeg|png|gif)$">
    Order allow,deny
    Allow from all
</FilesMatch>
EOF

# .htaccess برای پوشه admin
cat > "$INSTALL_DIR/admin/.htaccess" <<EOF
<IfModule mod_expires.c>
    ExpiresActive Off
</IfModule>
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
EOF

show_status "فایل‌های امنیتی ایجاد شد"

# مرحله 11: تنظیم Apache Virtual Host
echo ""
show_progress "تنظیم Virtual Host آپاچی..."
cat > /etc/apache2/sites-available/tender-site.conf <<EOF
<VirtualHost *:80>
    ServerName $SITE_DOMAIN
    ServerAlias www.$SITE_DOMAIN
    DocumentRoot $INSTALL_DIR
    
    <Directory $INSTALL_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        <FilesMatch "\.(htaccess|htpasswd|ini|log|sh|sql)$">
            Require all denied
        </FilesMatch>
    </Directory>
    
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
    
    php_value upload_max_filesize 50M
    php_value post_max_size 50M
    php_value max_execution_time 300
    
    ErrorLog \${APACHE_LOG_DIR}/tender-error.log
    CustomLog \${APACHE_LOG_DIR}/tender-access.log combined
</VirtualHost>
EOF

a2ensite tender-site.conf
a2enmod rewrite
systemctl reload apache2
show_status "Virtual Host تنظیم شد"

# مرحله 12: ایجاد جداول دیتابیس
echo ""
show_progress "ایجاد جداول دیتابیس..."
mysql --user="$DB_USER" --password="$DB_PASS" "$DB_NAME" <<EOSQL
-- ایجاد جدول مناقصات
CREATE TABLE IF NOT EXISTS tenders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    deadline DATE,
    files TEXT,
    download_count INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ایجاد جدول مدیران
CREATE TABLE IF NOT EXISTS admins (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin', 'manager') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ایجاد جدول دانلودها
CREATE TABLE IF NOT EXISTS downloads (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tender_id INT(11) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    download_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    PRIMARY KEY (id),
    KEY idx_tender_id (tender_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ایجاد جدول تنظیمات
CREATE TABLE IF NOT EXISTS settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- ایجاد کاربران مدیر
INSERT INTO admins (username, password, full_name, role) VALUES ('$ADMIN1_USER', '$ADMIN1_PASS', 'مدیر اصلی', 'admin')
ON DUPLICATE KEY UPDATE password = '$ADMIN1_PASS';

INSERT INTO admins (username, password, full_name, role) VALUES ('$ADMIN2_USER', '$ADMIN2_PASS', 'مدیر دوم', 'manager')
ON DUPLICATE KEY UPDATE password = '$ADMIN2_PASS';
EOSQL
show_status "جداول دیتابیس ایجاد شد"

# مرحله 13: حذف فایل install.php
echo ""
show_progress "حذف فایل نصب..."
rm -f "$INSTALL_DIR/install.php"
show_status "فایل install.php حذف شد"

# مرحله 14: تنظیم فایروال
echo ""
show_progress "تنظیم فایروال..."
if command -v ufw &> /dev/null; then
    ufw allow 80/tcp 2>/dev/null
    ufw allow 443/tcp 2>/dev/null
    ufw --force enable 2>/dev/null
    show_status "فایروال تنظیم شد"
else
    apt install -y ufw -qq
    ufw allow 80/tcp 2>/dev/null
    ufw allow 443/tcp 2>/dev/null
    ufw --force enable 2>/dev/null
    show_status "فایروال نصب و تنظیم شد"
fi

# دریافت IP سرور
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}')

# نمایش خلاصه نصب
echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}              ✅ نصب با موفقیت انجام شد!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${BLUE}📁 مسیر نصب:${NC} $INSTALL_DIR"
echo ""
echo -e "${BLUE}📍 آدرس‌های دسترسی:${NC}"
echo "   سایت:        http://$SERVER_IP/"
echo "   پنل مدیریت:  http://$SERVER_IP/login.php"
echo ""
echo -e "${BLUE}📊 اطلاعات دیتابیس:${NC}"
echo "   نام دیتابیس: $DB_NAME"
echo "   کاربر:       $DB_USER"
echo "   رمز عبور:    $DB_PASS"
echo ""
echo -e "${BLUE}👤 اطلاعات کاربران مدیر:${NC}"
echo ""
echo -e "   ${YELLOW}مدیر اصلی:${NC}"
echo "   نام کاربری:  $ADMIN1_USER"
echo "   رمز عبور:   $ADMIN1_PASS"
echo ""
echo -e "   ${YELLOW}مدیر دوم:${NC}"
echo "   نام کاربری:  $ADMIN2_USER"
echo "   رمز عبور:   $ADMIN2_PASS"
echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}⚠️  نکات امنیتی:${NC}"
echo "   1. رمز عبور مدیران را تغییر دهید"
echo "   2. از HTTPS استفاده کنید (Let's Encrypt)"
echo "   3. از دیتابیس به طور منظم پشتیبان بگیرید"
echo -e "${GREEN}═══════════════════════════════════════════════════════════${NC}"
echo ""

# پرسش برای نصب SSL
echo ""
read -p "$(echo -e ${BLUE}'آیا می‌خواهید SSL رایگان Let\'s Encrypt نصب شود؟ (y/n): '${NC})" install_ssl

if [[ $install_ssl == "y" || $install_ssl == "Y" ]]; then
    echo ""
    show_progress "نصب Certbot برای SSL..."
    apt install -y certbot python3-certbot-apache -qq
    
    read -p "$(echo -e ${BLUE}'دامنه خود را وارد کنید (مثال: example.com): '${NC})" domain
    read -p "$(echo -e ${BLUE}'ایمیل خود را وارد کنید: '${NC})" email
    
    certbot --apache -d $domain -d www.$domain --non-interactive --agree-tos --email $email
    
    if [ $? -eq 0 ]; then
        show_status "SSL نصب شد"
        echo "سایت شما اکنون با HTTPS قابل دسترسی است: https://$domain"
    else
        show_error "خطا در نصب SSL. لطفاً به صورت دستی انجام دهید."
    fi
fi

echo ""
echo -e "${GREEN}🚀 سیستم مناقصات لوتوس آماده استفاده است!${NC}"
echo -e "${BLUE}📦 Repository: https://github.com/hntech98/tender-site${NC}"
echo ""
