<?php
/**
 * DEBUG HOSTING - Check Database Connection & Data
 * Upload ke hosting dan akses: https://adfsystem.online/debug-hosting.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Debug Hosting</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#eee;} 
.ok{color:#0f0;} .err{color:#f00;} .warn{color:#ff0;} 
table{border-collapse:collapse;margin:10px 0;} 
th,td{border:1px solid #444;padding:8px;text-align:left;}
h2{color:#00d4ff;border-bottom:1px solid #444;padding-bottom:10px;}</style></head><body>";

echo "<h1>🔍 Debug Hosting - ADF System</h1>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

// 1. Environment Check
echo "<h2>1. Environment</h2>";
echo "<p>HTTP_HOST: <b>" . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</b></p>";
echo "<p>DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</p>";
echo "<p>SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$isProduction = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false && 
                strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') === false);
echo "<p>Is Production: <b class='" . ($isProduction ? 'ok' : 'warn') . "'>" . ($isProduction ? 'YES' : 'NO') . "</b></p>";

// 2. Config Check
echo "<h2>2. Config Loading</h2>";
try {
    define('APP_ACCESS', true);
    require_once __DIR__ . '/config/config.php';
    echo "<p class='ok'>✓ config.php loaded</p>";
    echo "<p>DB_HOST: " . DB_HOST . "</p>";
    echo "<p>DB_NAME: <b>" . DB_NAME . "</b></p>";
    echo "<p>DB_USER: " . DB_USER . "</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
} catch (Exception $e) {
    echo "<p class='err'>✗ Config Error: " . $e->getMessage() . "</p>";
}

// 3. Database Connection Test
echo "<h2>3. Database Connection</h2>";

// Test main database
$databases = [
    'Main (adf)' => DB_NAME,
    'Narayana Hotel' => function_exists('getDbName') ? getDbName('adf_narayana_hotel') : 'adfb2574_narayana_hotel',
    'Bens Cafe' => function_exists('getDbName') ? getDbName('adf_benscafe') : 'adfb2574_Adf_Bens'
];

foreach ($databases as $label => $dbName) {
    echo "<h3>$label: $dbName</h3>";
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . $dbName, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='ok'>✓ Connected to $dbName</p>";
        
        // Count tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables: " . count($tables) . "</p>";
        
        // Check specific tables
        $checkTables = ['users', 'businesses', 'cash_book', 'divisions', 'rooms', 'categories', 'guests', 'reservations'];
        echo "<table><tr><th>Table</th><th>Count</th></tr>";
        foreach ($checkTables as $table) {
            if (in_array($table, $tables)) {
                try {
                    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    $class = $count > 0 ? 'ok' : 'warn';
                    echo "<tr><td>$table</td><td class='$class'>$count</td></tr>";
                } catch (Exception $e) {
                    echo "<tr><td>$table</td><td class='err'>Error</td></tr>";
                }
            }
        }
        echo "</table>";
        
        // Special check for cash_book
        if (in_array('cash_book', $tables)) {
            echo "<h4>Cash Book Details:</h4>";
            try {
                $result = $pdo->query("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
                FROM cash_book")->fetch(PDO::FETCH_ASSOC);
                echo "<p>Total Records: <b>" . $result['total'] . "</b></p>";
                echo "<p>Total Income: <b class='ok'>Rp " . number_format($result['total_income'], 0, ',', '.') . "</b></p>";
                echo "<p>Total Expense: <b class='err'>Rp " . number_format($result['total_expense'], 0, ',', '.') . "</b></p>";
                
                // Show last 5 records
                echo "<h4>Last 5 Cash Book Records:</h4>";
                $records = $pdo->query("SELECT id, date, description, type, amount FROM cash_book ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                if ($records) {
                    echo "<table><tr><th>ID</th><th>Date</th><th>Description</th><th>Type</th><th>Amount</th></tr>";
                    foreach ($records as $row) {
                        echo "<tr><td>{$row['id']}</td><td>{$row['date']}</td><td>{$row['description']}</td><td>{$row['type']}</td><td>" . number_format($row['amount']) . "</td></tr>";
                    }
                    echo "</table>";
                }
            } catch (Exception $e) {
                echo "<p class='err'>Error: " . $e->getMessage() . "</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p class='err'>✗ Connection Failed: " . $e->getMessage() . "</p>";
    }
}

// 4. getDbName function check
echo "<h2>4. getDbName() Function</h2>";
if (function_exists('getDbName')) {
    echo "<p class='ok'>✓ Function exists</p>";
    echo "<table><tr><th>Local Name</th><th>Returns</th></tr>";
    echo "<tr><td>adf_system</td><td>" . getDbName('adf_system') . "</td></tr>";
    echo "<tr><td>adf_narayana_hotel</td><td>" . getDbName('adf_narayana_hotel') . "</td></tr>";
    echo "<tr><td>adf_benscafe</td><td>" . getDbName('adf_benscafe') . "</td></tr>";
    echo "</table>";
} else {
    echo "<p class='err'>✗ Function NOT defined!</p>";
}

// 5. File Check
echo "<h2>5. Critical Files</h2>";
$files = [
    'config/config.php',
    'config/database.php',
    'includes/auth.php',
    'api/owner-stats.php',
    'modules/owner/dashboard.php'
];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "<p class='ok'>✓ $file ($size bytes, modified: $modified)</p>";
    } else {
        echo "<p class='err'>✗ $file - NOT FOUND</p>";
    }
}

echo "<hr><p><a href='login.php' style='color:#00d4ff;'>← Back to Login</a></p>";
echo "</body></html>";
