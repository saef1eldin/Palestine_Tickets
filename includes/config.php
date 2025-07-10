<?php
// إعدادات التطبيق الأساسية
define('APP_NAME', 'نظام تذاكر الحفلات');
// Dynamically determine the base URL
function getBaseUrl() {
    // Get protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';

    // Get host
    $host = $_SERVER['HTTP_HOST'];

    // First try: Use REQUEST_URI to determine the path
    $request_uri = $_SERVER['REQUEST_URI'];
    $base_path = '';

    // Check if we're in the project directory
    if (strpos($request_uri, '/new1/') !== false) {
        $base_path = '/new1/';
    } else {
        // Second try: Use SCRIPT_NAME
        $script_name = $_SERVER['SCRIPT_NAME'];

        // If we're in a specific file in the root directory
        if (strpos($script_name, '/index.php') !== false ||
            strpos($script_name, '/login.php') !== false ||
            strpos($script_name, '/register.php') !== false) {

            $dir = dirname($script_name);
            if ($dir === '/' || $dir === '\\') {
                $base_path = '/';
            } else {
                $base_path = $dir . '/';
            }
        } else {
            // Third try: Use document root and current directory
            $doc_root = $_SERVER['DOCUMENT_ROOT'];
            $current_dir = dirname(__FILE__);
            $parent_dir = dirname($current_dir);

            $relative_path = str_replace($doc_root, '', $parent_dir);
            $relative_path = str_replace('\\', '/', $relative_path);

            if (substr($relative_path, 0, 1) !== '/') {
                $relative_path = '/' . $relative_path;
            }

            if (substr($relative_path, -1) !== '/') {
                $relative_path .= '/';
            }

            $base_path = $relative_path;
        }
    }

    return $protocol . $host . $base_path;
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