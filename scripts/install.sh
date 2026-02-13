#!/bin/bash

#####################################################################
# اسکریپت راهنمای نصب سیستم مناقصات لوتوس
# Lotus Tender Management System - Installation Guide
# 
# repository: https://github.com/hntech98/tender-site
#####################################################################

echo "╔════════════════════════════════════════════════════════════╗"
echo "║          سیستم مدیریت مناقصات شرکت لوتوس                  ║"
echo "║          Lotus Tender Management System                    ║"
echo "║                                                            ║"
echo "║          GitHub: github.com/hntech98/tender-site           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# تشخیص سیستم عامل
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" ]]; then
    OS="windows"
    echo "سیستم عامل تشخیص داده شده: ویندوز"
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    OS="linux"
    echo "سیستم عامل تشخیص داده شده: لینوکس"
else
    OS="unknown"
    echo "سیستم عامل: $OSTYPE"
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo "                    ویندوز (XAMPP)"
echo "═══════════════════════════════════════════════════════════"
echo ""
echo "1. XAMPP را دانلود و نصب کنید:"
echo "   https://www.apachefriends.org/download.html"
echo ""
echo "2. پروژه را کلون کنید:"
echo "   git clone https://github.com/hntech98/tender-site.git"
echo ""
echo "3. پوشه tender-site را در مسیر زیر کپی کنید:"
echo "   C:\xampp\htdocs\tender-site"
echo ""
echo "4. مرورگر را باز کنید و به آدرس زیر بروید:"
echo "   http://localhost/tender-site/install.php"
echo ""
echo "5. اطلاعات دیتابیس پیش‌فرض XAMPP:"
echo "   - آدرس سرور: localhost"
echo "   - نام کاربری: root"
echo "   - رمز عبور: (خالی)"
echo ""
echo "═══════════════════════════════════════════════════════════"
echo "                    لینوکس (اوبونتو)"
echo "═══════════════════════════════════════════════════════════"
echo ""
echo "1. پروژه را کلون کنید:"
echo "   git clone https://github.com/hntech98/tender-site.git"
echo ""
echo "2. اسکریپت نصب خودکار را اجرا کنید:"
echo "   cd tender-site/scripts"
echo "   chmod +x install-ubuntu.sh"
echo "   sudo ./install-ubuntu.sh"
echo ""
echo "═══════════════════════════════════════════════════════════"
echo ""

# اگر لینوکس است، پیشنهاد اجرای اسکریپت نصب
if [[ "$OS" == "linux" ]]; then
    read -p "آیا می‌خواهید اسکریپت نصب خودکار اجرا شود؟ (y/n): " run_install
    if [[ $run_install == "y" || $run_install == "Y" ]]; then
        if [ -f "./install-ubuntu.sh" ]; then
            sudo ./install-ubuntu.sh
        else
            echo "فایل install-ubuntu.sh یافت نشد!"
            echo "لطفاً به پوشه scripts بروید و دوباره تلاش کنید."
        fi
    fi
fi
