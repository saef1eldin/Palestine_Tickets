<?php
// تعريف الأيقونات حسب اللغة
$icons = [
    'ar' => [
        'my_tickets' => 'fa-ticket-alt',
        'payment_methods' => 'fa-credit-card',
        'invoices' => 'fa-file-invoice',
        'notifications' => 'fa-bell',
        'account_preferences' => 'fa-cog',
        'security' => 'fa-shield-alt',
        'logout' => 'fa-sign-out-alt',
        'edit_profile' => 'fa-user-edit',
        'user' => 'fa-user',
        'globe' => 'fa-globe'
    ],
    'en' => [
        'my_tickets' => 'fa-tags',
        'payment_methods' => 'fa-money-bill-wave',
        'invoices' => 'fa-receipt',
        'notifications' => 'fa-envelope',
        'account_preferences' => 'fa-sliders-h',
        'security' => 'fa-lock',
        'logout' => 'fa-power-off',
        'edit_profile' => 'fa-pen',
        'user' => 'fa-user-circle',
        'globe' => 'fa-language'
    ],
    'he' => [
        'my_tickets' => 'fa-bookmark',
        'payment_methods' => 'fa-wallet',
        'invoices' => 'fa-file-alt',
        'notifications' => 'fa-comment',
        'account_preferences' => 'fa-tools',
        'security' => 'fa-user-shield',
        'logout' => 'fa-door-open',
        'edit_profile' => 'fa-id-card',
        'user' => 'fa-user-tie',
        'globe' => 'fa-globe-asia'
    ]
];

// الحصول على الأيقونة المناسبة للغة الحالية
function get_icon($icon_name, $lang = null) {
    global $icons, $selected_lang;

    // استخدام اللغة المحددة إذا لم يتم تحديد لغة
    if ($lang === null) {
        $lang = $selected_lang;
    }

    // التحقق من وجود الأيقونة للغة المحددة
    if (isset($icons[$lang]) && isset($icons[$lang][$icon_name])) {
        return $icons[$lang][$icon_name];
    }

    // استخدام الأيقونة الافتراضية إذا لم يتم العثور على الأيقونة للغة المحددة
    if (isset($icons['en'][$icon_name])) {
        return $icons['en'][$icon_name];
    }

    // إرجاع قيمة افتراضية إذا لم يتم العثور على الأيقونة
    return 'fa-question-circle';
}
