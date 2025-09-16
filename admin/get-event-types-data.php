<?php
// Include configuration
require_once '../config/config.php';

// Include auth functions
require_once '../includes/auth.php';

// Include admin functions
require_once '../includes/admin_functions.php';

// Check site management permissions
require_admin_permission('site');

// Set content type to JSON
header('Content-Type: application/json');

// Get event types data
$stmt = $pdo->query("
    SELECT type, COUNT(*) as count
    FROM events
    GROUP BY type
    ORDER BY count DESC
    LIMIT 5
");
$eventTypes = $stmt->fetchAll();

// Prepare data for chart
$labels = [];
$values = [];

foreach ($eventTypes as $type) {
    $labels[] = $type['type'];
    $values[] = (int)$type['count'];
}

// Return JSON response
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
exit;
