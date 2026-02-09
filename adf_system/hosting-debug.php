<?php
/**
 * ADF System - Hosting Debug
 * Check system errors and configuration
 */

echo "<h1>🔧 ADF System Hosting Debug</h1>";
echo "<hr>";

// 1. Check Database Connection
echo "<h3>1️⃣ Database Connection</h3>";
try {
    require_once 'config/config.php';
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div style='background:#4CAF50; color:white; padding:15px; border-radius:5px;'>";
    echo "✅ Database Connected: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "User: " . DB_USER . "";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background:#f44336; color:white; padding:15px; border-radius:5px;'>";
    echo "❌ Database Error: " . $e->getMessage() . "";
    echo "</div>";
    exit;
}

// 2. Check Files
echo "<h3>2️⃣ File Structure</h3>";
$files_to_check = [
    'config/config.php',
    'config/database.php',
    'includes/business_helper.php',
    'includes/language.php',
    'login.php',
    'index.php'
];

echo "<ul>";
foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<li>✅ $file</li>";
    } else {
        echo "<li>❌ $file (MISSING)</li>";
    }
}
echo "</ul>";

// 3. Check Session
echo "<h3>3️⃣ Session Status</h3>";
echo "<div style='background:#2196F3; color:white; padding:15px; border-radius:5px;'>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ ACTIVE" : "❌ NOT ACTIVE") . "<br>";
echo "Session Name: " . session_name() . "<br>";
echo "Session ID: " . session_id() . "";
echo "</div>";

// 4. Check Permissions
echo "<h3>4️⃣ Directory Permissions</h3>";
$directories = [
    'cache' => 'Cache',
    'logs' => 'Logs',
    'uploads' => 'Uploads',
    'tmp' => 'Temp'
];

echo "<ul>";
foreach ($directories as $dir => $label) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $writable = is_writable($path) ? '✅ Writable' : '❌ NOT Writable';
        echo "<li>$label ($dir): $writable</li>";
    } else {
        echo "<li>$label ($dir): ⚠️ Directory does not exist</li>";
    }
}
echo "</ul>";

// 5. Check Config Values
echo "<h3>5️⃣ Configuration Values</h3>";
echo "<div style='background:#f0f0f0; padding:15px; border-radius:5px;'>";
echo "<strong>Database:</strong><br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'Not defined') . "<br>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Check the results above. If database is OK but other errors, PHP code might have bugs.</strong></p>";
echo "<p><a href='login.php'>← Back to Login</a></p>";
?>
