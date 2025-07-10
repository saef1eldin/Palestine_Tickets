<?php
// تضمين ملف التهيئة
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// إنشاء كائن Auth
$auth = new Auth();

// تسجيل الخروج
$auth->logout();

// إعادة التوجيه إلى صفحة تسجيل الدخول
redirect('../login.php');
?>
