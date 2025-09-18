<?php
function translate_static_text($text, $lang = 'ar') {
    global $static_translations;

    // إذا كانت اللغة هي العربية، أرجع النص كما هو
    if ($lang == 'ar') {
        return $text;
    }

    // إذا كان النص موجود في قاموس الترجمة واللغة المطلوبة متوفرة
    if (isset($static_translations[$text]) && isset($static_translations[$text][$lang])) {
        return $static_translations[$text][$lang];
    }

    // إذا لم يتم العثور على ترجمة، أرجع النص الأصلي
    return $text;
}

 
function translate_html_content($html, $lang = 'ar') {
    global $static_translations;

    // إذا كانت اللغة هي العربية، أرجع المحتوى كما هو
    if ($lang == 'ar') {
        return $html;
    }

    // استبدال كل النصوص الثابتة في المحتوى
    foreach ($static_translations as $arabic => $translations) {
        if (isset($translations[$lang])) {
            $html = str_replace($arabic, $translations[$lang], $html);
        }
    }

    return $html;
}

/**
 * وظيفة لبدء تخزين مؤقت للمخرجات
 */
function start_translation_buffer() {
    // تعطيل مؤقت لحل مشكلة العرض
    // ob_start();
}


function end_translation_buffer($lang = 'ar') {
    return '';
}
