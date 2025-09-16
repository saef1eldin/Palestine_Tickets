<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions
require_once 'includes/functions.php';

// Get language and redirect parameters
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Validate language
$allowed_languages = ['en', 'ar', 'he'];
if (!in_array($lang, $allowed_languages)) {
    $lang = 'en';
}

// Set language in session
$_SESSION['lang'] = $lang;

// Clean redirect URL to prevent open redirect attacks
if (strpos($redirect, 'http') === 0) {
    // If redirect contains full URL, redirect to index
    $redirect = 'index.php';
}

// Remove leading slash if present
if (strpos($redirect, '/') === 0) {
    $redirect = substr($redirect, 1);
}

// Ensure redirect is within the application
if (strpos($redirect, '../') !== false || strpos($redirect, '..\\') !== false) {
    $redirect = 'index.php';
}

// Redirect to the requested page
redirect($redirect);
?>