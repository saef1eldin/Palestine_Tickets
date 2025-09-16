<?php
// الجلسة تم بدؤها بالفعل في init.php

// Include database connection
require_once dirname(__DIR__, 2) . '/config/database.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();
$pdo = $db->getConnection();

// Include functions
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/auth_functions.php';
require_once dirname(__DIR__, 2) . '/includes/formatPrice.php';

// التحقق من صلاحيات الإدارة يتم في الصفحة الرئيسية
// requireAdmin(); // تم تعطيل هذا الاستدعاء لتجنب التضارب

// Include language files
$lang_dir = dirname(__DIR__, 2) . '/lang/';
$default_lang = 'en';
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : $default_lang;

// Load language file
if (file_exists($lang_dir . $current_lang . '.php')) {
    $lang = require_once $lang_dir . $current_lang . '.php';
} else {
    $lang = require_once $lang_dir . $default_lang . '.php';
}

// Check for messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear messages
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

// Set direction based on language
$dir = ($current_lang == 'ar' || $current_lang == 'he') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/custom.css">

    <?php if ($dir === 'rtl'): ?>
    <!-- RTL Support -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php endif; ?>

    <style>
        .dashboard-card {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card i {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .dashboard-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .dashboard-card p {
            margin-bottom: 0;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> <?php echo $lang['dashboard'] ?? 'Dashboard'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php"><i class="fas fa-calendar-alt"></i> <?php echo $lang['manage_events'] ?? 'Events'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users"></i> <?php echo $lang['manage_users'] ?? 'Users'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tickets.php"><i class="fas fa-ticket-alt"></i> <?php echo $lang['manage_tickets'] ?? 'Tickets'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="discounts.php"><i class="fas fa-percent"></i> <?php echo $lang['manage_discounts'] ?? 'Discounts'; ?></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="login_logs.php"><i class="fas fa-sign-in-alt"></i> <?php echo $lang['login_logs'] ?? 'Login Logs'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php"><i class="fas fa-envelope"></i> <?php echo $lang['contact_messages'] ?? 'Contact Messages'; ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Account'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../index.php"><i class="fas fa-home"></i> <?php echo $lang['back_to_site'] ?? 'Back to Site'; ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo $lang['logout'] ?? 'Logout'; ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe"></i> <?php echo strtoupper($current_lang); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="../change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">English</a></li>
                            <li><a class="dropdown-item" href="../change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">العربية</a></li>
                            <li><a class="dropdown-item" href="../change-language.php?lang=he&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">עברית</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
     <main class="py-4">
