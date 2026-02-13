<div align="center">

# 🌸 سیستم مدیریت مناقصات لوتوس
## Lotus Tender Management System

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Ubuntu](https://img.shields.io/badge/Ubuntu-20.04%20|%2022.04-E95420?style=flat-square&logo=ubuntu&logoColor=white)](https://ubuntu.com)

<img src="https://img.shields.io/badge/status-active-success?style=flat-square" alt="Status">
<img src="https://img.shields.io/badge/version-2.3-blue?style=flat-square" alt="Version">

**سیستم جامع مدیریت مناقصات با پشتیبانی از تاریخ شمسی**

[🔧 نصب سریع](#-نصب-سریع) • [📖 مستندات](#-مستندات) • [🤏 مشارکت](#-مشارکت) • [📞 پشتیبانی](#-پشتیبانی)

</div>

---

## ✨ ویژگی‌ها

| ویژگی | توضیحات |
|:-----:|:--------|
| 📋 | **جدول مناقصات** - نمایش لیست مناقصات با جزئیات کامل |
| 📅 | **تاریخ شمسی** - پشتیبانی کامل از تقویم هجری شمسی |
| 🎨 | **رنگ‌بندی مهلت** - سبز (فعال) / قرمز (منقضی) |
| 📥 | **مدیریت فایل** - آپلود و دانلود مستندات |
| 👥 | **چند کاربره** - پشتیبانی از چند مدیر |
| 🔐 | **امنیت** - محافظت از فایل‌ها و پوشه‌ها |
| 📱 | **ریسپانسیو** - سازگار با موبایل و تبلت |
| 🐧 | **نصب خودکار** - اسکریپت نصب اوبونتو |

---

## 🚀 نصب سریع

### روش 1: اوبونتو (خودکار) ⚡

```bash
# کلون کردن پروژه
git clone https://github.com/hntech98/tender-site.git

# ورود به پوشه و اجرای نصب
cd tender-site/scripts
chmod +x install-ubuntu.sh
sudo ./install-ubuntu.sh
```
نصب سریع در ابونتو 
```bash
bash <(curl -Ls https://raw.githubusercontent.com/hntech98/tender-site/master/scripts/install-ubuntu.sh)
```
### روش 2: XAMPP (ویندوز) 🪟

```bash
# کلون کردن پروژه
git clone https://github.com/hntech98/tender-site.git

# کپی به htdocs (در ویندوز)
# xcopy tender-site C:\xampp\htdocs\tender-site /E /I

# تنظیم config
copy config.example.php config.php
# ویرایش config.php با اطلاعات دیتابیس

# باز کردن در مرورگر
# http://localhost/tender-site/install.php
```

---

## 💻 پیش‌نیازها

| نرم‌افزار | نسخه | نماد |
|----------|------|------|
| PHP | 7.4+ | ![PHP](https://img.shields.io/badge/required-7.4%2B-777BB4?style=flat-square) |
| MySQL | 5.7+ | ![MySQL](https://img.shields.io/badge/required-5.7%2B-4479A1?style=flat-square) |
| Apache | 2.4+ | ![Apache](https://img.shields.io/badge/required-2.4%2B-D22128?style=flat-square) |

---

## 📖 مستندات

<details>
<summary><b>🐧 نصب روی اوبونتو 20.04/22.04</b></summary>

### روش 1: نصب خودکار (پیشنهادی)

```bash
# کلون کردن
git clone https://github.com/hntech98/tender-site.git
cd tender-site/scripts

# اجرای نصب
chmod +x install-ubuntu.sh
sudo ./install-ubuntu.sh
```

### روش 2: نصب دستی

```bash
# 1. به‌روزرسانی سیستم
sudo apt update && sudo apt upgrade -y

# 2. نصب نرم‌افزارها
sudo apt install -y apache2 mysql-server php libapache2-mod-php \
    php-mysql php-json php-mbstring php-xml php-curl php-zip

# 3. تنظیم MySQL
sudo mysql -u root
```

```sql
CREATE DATABASE lotus_tender CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
CREATE USER 'lotus_user'@'localhost' IDENTIFIED BY 'YourPassword123!';
GRANT ALL PRIVILEGES ON lotus_tender.* TO 'lotus_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# 4. کلون و تنظیم پروژه
git clone https://github.com/hntech98/tender-site.git /var/www/html/tender-site

# 5. تنظیم config
sudo cp /var/www/html/tender-site/config.example.php /var/www/html/tender-site/config.php
sudo nano /var/www/html/tender-site/config.php

# 6. مجوزها
sudo chown -R www-data:www-data /var/www/html/tender-site
sudo chmod -R 755 /var/www/html/tender-site
sudo chmod -R 777 /var/www/html/tender-site/tender

# 7. Virtual Host
sudo cp /var/www/html/tender-site/scripts/apache-vhost.conf /etc/apache2/sites-available/tender-site.conf
sudo a2ensite tender-site.conf
sudo a2enmod rewrite
sudo systemctl reload apache2

# 8. اجرای نصب در مرورگر
# http://your-ip/install.php
```

</details>

<details>
<summary><b>🪟 نصب روی XAMPP</b></summary>

### مرحله 1: نصب XAMPP

1. دانلود از [apachefriends.org](https://www.apachefriends.org/)
2. نصب با تنظیمات پیش‌فرض
3. فعال‌سازی Apache و MySQL

### مرحله 2: کلون پروژه

```bash
git clone https://github.com/hntech98/tender-site.git C:\xampp\htdocs\tender-site
```

### مرحله 3: ایجاد دیتابیس

1. مراجعه به `http://localhost/phpmyadmin`
2. ایجاد دیتابیس `lotus_tender`

### مرحله 4: تنظیم config

```bash
copy C:\xampp\htdocs\tender-site\config.example.php C:\xampp\htdocs\tender-site\config.php
```

ویرایش `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lotus_tender');
```

### مرحله 5: نصب

1. مراجعه به `http://localhost/tender-site/install.php`
2. تکمیل مراحل
3. **حذف `install.php`**

</details>

<details>
<summary><b>👤 کاربران پیش‌فرض</b></summary>

| نقش | نام کاربری | رمز عبور |
|:---:|:----------:|:--------:|
| مدیر اصلی | `admin` | `admin123` |
| مدیر دوم | `manager` | `manager123` |

⚠️ **حتماً پس از نصب رمز عبور را تغییر دهید!**

</details>

---

## 📂 ساختار پروژه

```
tender-site/
├── 📄 index.php          # صفحه اصلی
├── 📄 login.php          # ورود
├── 📄 download.php       # دانلود فایل
├── 📄 config.php         # تنظیمات (نباید آپلود شود)
├── 📄 config.example.php # نمونه تنظیمات
├── 📄 install.php        # نصب
│
├── 📁 admin/             # پنل مدیریت
│   ├── index.php
│   ├── actions.php
│   ├── settings.php
│   └── logout.php
│
├── 📁 includes/          # فایل‌های مشترک
│   ├── header.php
│   └── footer.php
│
├── 📁 assets/            # منابع
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
│
├── 📁 scripts/           # اسکریپت‌های نصب
│   ├── install-ubuntu.sh
│   └── apache-vhost.conf
│
└── 📁 tender/            # فایل‌های آپلود شده
```

---

## 🔒 امنیت

```bash
# حذف فایل نصب
sudo rm /var/www/html/tender-site/install.php

# محافظت از config
sudo chmod 600 /var/www/html/tender-site/config.php

# فایروال
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# SSL رایگان
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

---

## 💾 پشتیبان‌گیری

```bash
# بکاپ دیتابیس
mysqldump -u lotus_user -p lotus_tender > backup_$(date +%Y%m%d).sql

# بکاپ فایل‌ها
tar -czvf tender-files-$(date +%Y%m%d).tar.gz /var/www/html/tender-site/tender/

# بازیابی
mysql -u lotus_user -p lotus_tender < backup_20240115.sql
```

---

## 🤏 مشارکت

مشارکت‌ها همیشه خوشایند هستند!

1. Fork کنید
2. Branch جدید بسازید (`git checkout -b feature/AmazingFeature`)
3. Commit کنید (`git commit -m 'Add some AmazingFeature'`)
4. Push کنید (`git push origin feature/AmazingFeature`)
5. Pull Request باز کنید

---

## 📞 پشتیبانی

| مورد | اطلاعات |
|------|---------|
| 📧 ایمیل | hn.tech95@gmail.com |
| 📞 تلفن | 0910123456789 |
| 📍 آدرس | خیابان مقدس اردبیلی – خیابان شادآور – کوچه شادی پلاک 8 |
| 🔗 گیت‌هاب | [github.com/hntech98/tender-site](https://github.com/hntech98/tender-site) |

---

## 📄 مجوز

این پروژه تحت مجوز [MIT](LICENSE) منتشر شده است.

---

<div align="center">

**ساخته شده با ❤️ توسط شرکت لوتوس**

⭐ اگر این پروژه برایتان مفید بود، لطفاً ستاره بدهید! ⭐

</div>
