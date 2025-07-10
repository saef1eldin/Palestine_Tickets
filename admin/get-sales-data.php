<?php
// Include configuration
require_once '../config/config.php';

// Include auth functions
require_once '../includes/auth.php';

// Require admin
requireAdmin();

// Set content type to JSON
header('Content-Type: application/json');

// Get monthly sales data for the last 12 months
$stmt = $pdo->query("
    SELECT DATE_FORMAT(purchase_date, '%Y-%m') as month, SUM(total_price) as total
    FROM tickets
    WHERE payment_status = 'completed'
    GROUP BY DATE_FORMAT(purchase_date, '%Y-%m')
    ORDER BY month ASC
    LIMIT 12
");
$monthlySales = $stmt->fetchAll();

// Prepare data for chart
$labels = [];
$values = [];

foreach ($monthlySales as $sale) {
    $labels[] = date('M Y', strtotime($sale['month'] . '-01'));
    $values[] = (float)$sale['total'];
}

// Return JSON response
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
exit;
