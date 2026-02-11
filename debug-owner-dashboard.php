<?php
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

echo "<h2>Debug Owner Dashboard</h2>";
echo "<pre>";

echo "=== USER INFO ===\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'N/A') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'N/A') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'N/A') . "\n";
echo "\n";

echo "=== BUSINESSES ===\n";
$businesses = $db->fetchAll("SELECT * FROM businesses ORDER BY id");
foreach ($businesses as $b) {
    echo "ID: {$b['id']}, Name: {$b['business_name']}\n";
}
echo "\n";

echo "=== TEST API: owner-branches.php ===\n";
$apiUrl = BASE_URL . '/api/owner-branches.php';
echo "URL: $apiUrl\n";

// Use file_get_contents with session cookie
$opts = array(
    'http' => array(
        'method' => 'GET',
        'header' => 'Cookie: PHPSESSID=' . session_id() . "\r\n"
    )
);
$context = stream_context_create($opts);
$result = @file_get_contents($apiUrl, false, $context);
echo "Response: " . ($result ?: 'FAILED') . "\n\n";

echo "=== FRONTDESK BOOKINGS ===\n";
$bookings = $db->fetchAll("SELECT COUNT(*) as cnt FROM frontdesk_bookings");
echo "Total bookings: " . ($bookings[0]['cnt'] ?? 0) . "\n";

echo "=== SALES INVOICES ===\n";
try {
    $invoices = $db->fetchAll("SELECT COUNT(*) as cnt FROM sales_invoices");
    echo "Total invoices: " . ($invoices[0]['cnt'] ?? 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== CASHBOOK ===\n";
try {
    $cashbook = $db->fetchAll("SELECT COUNT(*) as cnt FROM cashbook");
    echo "Total cashbook: " . ($cashbook[0]['cnt'] ?? 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
