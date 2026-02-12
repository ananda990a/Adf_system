<?php
/**
 * Debug Dashboard Data - Check cash_book and data integrity
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/business_helper.php';
require_once 'includes/functions.php';
require_once 'config/database.php';

$db = Database::getInstance();

echo "<h2>Database Debug - " . DB_NAME . "</h2>";

// Check CURRENCY constants
echo "<h3>0. Currency Constants:</h3>";
echo "<pre>";
echo "CURRENCY_SYMBOL: '" . CURRENCY_SYMBOL . "' (length: " . strlen(CURRENCY_SYMBOL) . ", ord: " . ord(CURRENCY_SYMBOL[0]) . ")\n";
echo "CURRENCY_DECIMAL: " . CURRENCY_DECIMAL . "\n";
echo "formatCurrency(1000000) = " . formatCurrency(1000000) . "\n";
echo "</pre>";

// Test number_format directly
echo "<h3>0b. Number Format Test:</h3>";
echo "<pre>";
echo "number_format(1450000, 0, ',', '.') = " . number_format(1450000, 0, ',', '.') . "\n";
echo "Rp . ' ' . number_format = Rp " . number_format(1450000, 0, ',', '.') . "\n";
echo "</pre>";

// Check ACTIVE_BUSINESS_ID
echo "<h3>0c. Active Business:</h3>";
echo "<pre>";
echo "ACTIVE_BUSINESS_ID: " . (defined('ACTIVE_BUSINESS_ID') ? ACTIVE_BUSINESS_ID : 'NOT DEFINED') . "\n";
echo "</pre>";

// Check cash_book table structure
echo "<h3>1. Cash Book Table Structure:</h3>";
try {
    $columns = $db->fetchAll("DESCRIBE cash_book");
    echo "<pre>";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . " (Null: " . $col['Null'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Check if transaction_type column exists
echo "<h3>2. Transaction Type Check:</h3>";
try {
    $hasTransType = $db->fetchOne("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'cash_book' AND COLUMN_NAME = 'transaction_type'", ['db' => DB_NAME]);
    if ($hasTransType['cnt'] > 0) {
        echo "<p style='color:green'>✅ transaction_type column EXISTS</p>";
    } else {
        echo "<p style='color:red'>❌ transaction_type column MISSING!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Check sample data
echo "<h3>3. Sample Cash Book Data (Last 10):</h3>";
try {
    $samples = $db->fetchAll("SELECT * FROM cash_book ORDER BY id DESC LIMIT 10");
    echo "<pre>";
    print_r($samples);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Check totals
echo "<h3>4. Monthly Summary (Feb 2026):</h3>";
try {
    $totals = $db->fetchOne("
        SELECT 
            COUNT(*) as total_records,
            SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM cash_book
        WHERE DATE_FORMAT(transaction_date, '%Y-%m') = '2026-02'
    ");
    echo "<pre>";
    echo "Total Records: " . $totals['total_records'] . "\n";
    echo "Total Income: Rp " . number_format($totals['total_income'], 0, ',', '.') . "\n";
    echo "Total Expense: Rp " . number_format($totals['total_expense'], 0, ',', '.') . "\n";
    echo "Net Balance: Rp " . number_format($totals['total_income'] - $totals['total_expense'], 0, ',', '.') . "\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Check database connection info
echo "<h3>5. Environment Info:</h3>";
echo "<pre>";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "Environment: " . ((strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false) ? 'PRODUCTION' : 'LOCAL') . "\n";
echo "</pre>";
