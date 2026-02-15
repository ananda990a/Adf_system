<?php
/**
 * QUICK FIX - Update Database Name untuk Narayana Hotel
 * Run once untuk fix businesses.database_name di hosting
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Fix Database Name - Narayana Hotel</title>
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background: #f0f2f5; }
.container { max-width: 800px; margin: 0 auto; }
.box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
h1 { color: #1e40af; margin-bottom: 10px; }
.status { padding: 15px; border-radius: 8px; margin: 15px 0; }
.success { background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; }
.error { background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; }
.info { background: #dbeafe; border-left: 4px solid #3b82f6; color: #1e40af; }
.warning { background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
th { background: #f9fafb; font-weight: 600; color: #374151; }
code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; color: #dc2626; font-family: monospace; }
.btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 15px; }
.btn:hover { background: #2563eb; }
.btn-success { background: #10b981; }
.btn-success:hover { background: #059669; }
</style>
</head>
<body>

<div class="container">
    <div class="box">
        <h1>🔧 Fix Database Name - Narayana Hotel</h1>
        <p>Tool ini akan update <code>database_name</code> di tabel <code>businesses</code></p>
        
        <?php
        // Get current database name
        $current = $db->fetchOne("SELECT id, business_name, business_identifier, database_name FROM businesses WHERE id = 1");
        
        if (!$current) {
            echo '<div class="status error">❌ <strong>Error:</strong> Business ID 1 tidak ditemukan!</div>';
            exit;
        }
        
        echo '<div class="status info">';
        echo '<strong>📊 Current Data:</strong><br>';
        echo 'Business ID: <code>' . $current['id'] . '</code><br>';
        echo 'Business Name: <code>' . $current['business_name'] . '</code><br>';
        echo 'Identifier: <code>' . $current['business_identifier'] . '</code><br>';
        echo 'Current Database Name: <code>' . $current['database_name'] . '</code>';
        echo '</div>';
        
        // Detect correct database name
        $isLocal = strpos(DB_HOST, 'localhost') !== false || DB_HOST === '127.0.0.1';
        
        if ($isLocal) {
            $correctDbName = 'adf_narayana_hotel';
        } else {
            // Hosting - use prefix from DB_NAME
            $prefix = '';
            if (preg_match('/^([a-z0-9]+)_/', DB_NAME, $matches)) {
                $prefix = $matches[1] . '_';
            }
            $correctDbName = $prefix . 'narayana_hotel';
        }
        
        echo '<div class="status warning">';
        echo '<strong>🎯 Detected Environment:</strong> ' . ($isLocal ? 'LOCAL' : 'HOSTING') . '<br>';
        echo '<strong>Correct Database Name:</strong> <code>' . $correctDbName . '</code>';
        echo '</div>';
        
        // Check if update needed
        if ($current['database_name'] === $correctDbName) {
            echo '<div class="status success">';
            echo '✅ <strong>Already Correct!</strong> Database name sudah sesuai.<br>';
            echo 'Tidak perlu update.';
            echo '</div>';
        } else {
            // Execute update if requested
            if (isset($_GET['execute']) && $_GET['execute'] === 'yes') {
                try {
                    $db->query(
                        "UPDATE businesses SET database_name = ? WHERE id = 1",
                        [$correctDbName]
                    );
                    
                    echo '<div class="status success">';
                    echo '✅ <strong>SUCCESS!</strong> Database name berhasil diupdate.<br><br>';
                    echo '<strong>Before:</strong> <code>' . $current['database_name'] . '</code><br>';
                    echo '<strong>After:</strong> <code>' . $correctDbName . '</code><br><br>';
                    echo '<a href="modules/cashbook/add.php" class="btn btn-success">🧪 Test Save Transaksi</a>';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="status error">';
                    echo '❌ <strong>ERROR:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
            } else {
                // Show preview
                echo '<div class="status warning">';
                echo '<strong>⚠️ Preview Update:</strong><br><br>';
                echo '<table>';
                echo '<tr><th>Field</th><th>Old Value</th><th>New Value</th></tr>';
                echo '<tr><td>database_name</td><td><code>' . $current['database_name'] . '</code></td><td><code>' . $correctDbName . '</code></td></tr>';
                echo '</table>';
                echo '<br>';
                echo '<strong>SQL yang akan dijalankan:</strong><br>';
                echo '<code>UPDATE businesses SET database_name = \'' . $correctDbName . '\' WHERE id = 1;</code>';
                echo '</div>';
                
                echo '<a href="?execute=yes" class="btn btn-success">✅ Execute Update</a>';
                echo ' <a href="index.php" class="btn">← Cancel</a>';
            }
        }
        ?>
        
    </div>
    
    <div class="box">
        <h3>📖 Cara Pakai:</h3>
        <ol>
            <li><strong>Review</strong> data current dan new value di atas</li>
            <li>Klik <strong>"Execute Update"</strong> untuk menjalankan SQL</li>
            <li>Setelah sukses, klik <strong>"Test Save Transaksi"</strong></li>
            <li>Pastikan dropdown akun kas terisi dan save berhasil</li>
        </ol>
        
        <h3>🔍 Troubleshooting:</h3>
        <ul>
            <li>Kalau masih error, cek <code>debug-cash-accounts.php</code></li>
            <li>Verify database name dengan <code>SHOW DATABASES;</code> di phpMyAdmin</li>
            <li>Pastikan user database punya akses ke database yang benar</li>
        </ul>
    </div>
</div>

</body>
</html>
