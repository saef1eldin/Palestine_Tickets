<?php
/**
 * تنسيق السعر بإضافة رمز العملة
 *
 * @param float $price السعر
 * @return string السعر المنسق
 */
function formatPrice($price) {
    return number_format((float)$price, 2) . ' ₪';
}

/**
 * تنسيق التاريخ
 *
 * @param string $date التاريخ
 * @param string $format صيغة التاريخ
 * @return string التاريخ المنسق
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}
