<?php
// إعدادات التطبيق الأساسية
define('APP_NAME', 'نظام تذاكر الحفلات');
// Dynamically determine the base URL
function getBaseUrl() {
    // دالة بسيطة لتحديد الرابط الأساسي
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/new1/';
}

define('APP_URL', getBaseUrl());
define('APP_LANG', 'ar');

// إعدادات البريد الإلكتروني
define('EMAIL_HOST', 'smtp.example.com');
define('EMAIL_USER', 'info@example.com');
define('EMAIL_PASS', 'password');
define('EMAIL_PORT', 587);

// إعدادات التشفير
define('ENCRYPTION_KEY', 'your-secret-key-here');

// تم حذف إعدادات التليجرام لأسباب أخلاقية
?>