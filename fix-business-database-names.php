<?php
/**
 * FIX BUSINESS DATABASE NAMES
 * Update businesses table with correct database names in hosting
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

// Security check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'developer', 'owner'])) {
    die("❌ Akses ditolak. Hanya admin/developer/owner yang bisa menjalankan script ini.");
}

$execute = isset($_GET['execute']) && $_GET['execute'] === 'yes';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Business Database Names</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #333;
            color: white;
            font-weight: 600;
        }
        .code {
            background: #2d3748;
            color: #68d391;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .old {
            color: #dc3545;
            text-decoration: line-through;
        }
        .new {
            color: #28a745;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 18px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .log {
            background: #2d3748;
            color: #a0aec0;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .log p {
            margin: 5px 0;
        }
        .log .success-log { color: #68d391; }
        .log .error-log { color: #fc8181; }
        .log .info-log { color: #63b3ed; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Fix Business Database Names</h1>
            <p>Update database names di tabel businesses untuk hosting</p>
        </div>
        
        <div class="content">
            <?php
            try {
                $masterDb = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Get all databases from server
                $allDbs = $masterDb->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                
                // Get all businesses
                $stmt = $masterDb->query("SELECT * FROM businesses ORDER BY id");
                $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!$execute) {
                    // PREVIEW MODE
                    echo '<div class="section">';
                    echo '<h2>📋 Preview Perubahan</h2>';
                    echo '<p>Database names yang akan diupdate:</p>';
                    echo '<table>';
                    echo '<tr><th>Business</th><th>Current DB Name</th><th>→</th><th>New DB Name</th><th>Status</th></tr>';
                    
                    $hasChanges = false;
                    
                    foreach ($businesses as $biz) {
                        $currentDb = $biz['database_name'];
                        
                        // Check if current DB exists
                        $currentExists = in_array($currentDb, $allDbs);
                        
                        // Try to find correct database name
                        $newDb = $currentDb;
                        if (!$currentExists) {
                            $identifier = $biz['business_identifier'] ?? str_replace('adf_', '', $currentDb);
                            $keywords = preg_split('/[-_]/', strtolower($identifier));
                            
                            $bestMatch = null;
                            $maxMatchCount = 0;
                            
                            foreach ($allDbs as $db) {
                                $matchCount = 0;
                                $dbLower = strtolower($db);
                                
                                foreach ($keywords as $keyword) {
                                    if (!empty($keyword) && stripos($dbLower, $keyword) !== false) {
                                        $matchCount++;
                                    }
                                }
                                
                                if ($matchCount > $maxMatchCount) {
                                    $maxMatchCount = $matchCount;
                                    $bestMatch = $db;
                                }
                            }
                            
                            if ($bestMatch && $maxMatchCount > 0) {
                                $newDb = $bestMatch;
                            }
                        }
                        
                        $status = '';
                        $needsUpdate = $currentDb !== $newDb;
                        
                        if ($needsUpdate) {
                            $hasChanges = true;
                            $status = '🔄 Will Update';
                        } else {
                            $status = $currentExists ? '✅ Already Correct' : '⚠️ Not Found';
                        }
                        
                        echo '<tr>';
                        echo '<td><strong>' . $biz['business_name'] . '</strong></td>';
                        echo '<td class="' . ($needsUpdate ? 'old' : '') . '">' . $currentDb . '</td>';
                        echo '<td>→</td>';
                        echo '<td class="' . ($needsUpdate ? 'new' : '') . '">' . $newDb . '</td>';
                        echo '<td>' . $status . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</table>';
                    echo '</div>';
                    
                    if ($hasChanges) {
                        echo '<div class="warning">';
                        echo '<h4>⚠️ PERHATIAN</h4>';
                        echo '<p>Script ini akan mengupdate kolom <code>database_name</code> di tabel <code>businesses</code>.</p>';
                        echo '<p>Pastikan nama database yang baru sudah benar!</p>';
                        echo '</div>';
                        
                        echo '<div style="text-align: center;">';
                        echo '<a href="?execute=yes" class="btn">✅ JALANKAN UPDATE</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="section success">';
                        echo '<h2>✅ Tidak Ada Perubahan</h2>';
                        echo '<p>Semua database names sudah benar.</p>';
                        echo '</div>';
                    }
                    
                } else {
                    // EXECUTION MODE
                    echo '<div class="section">';
                    echo '<h2>⚙️ Menjalankan Update...</h2>';
                    echo '</div>';
                    
                    echo '<div class="log">';
                    
                    $updateCount = 0;
                    
                    foreach ($businesses as $biz) {
                        $currentDb = $biz['database_name'];
                        echo '<p class="info-log">Processing: ' . $biz['business_name'] . '</p>';
                        
                        // Check if current DB exists
                        $currentExists = in_array($currentDb, $allDbs);
                        
                        // Try to find correct database name
                        $newDb = $currentDb;
                        if (!$currentExists) {
                            $identifier = $biz['business_identifier'] ?? str_replace('adf_', '', $currentDb);
                            $keywords = preg_split('/[-_]/', strtolower($identifier));
                            
                            $bestMatch = null;
                            $maxMatchCount = 0;
                            
                            foreach ($allDbs as $db) {
                                $matchCount = 0;
                                $dbLower = strtolower($db);
                                
                                foreach ($keywords as $keyword) {
                                    if (!empty($keyword) && stripos($dbLower, $keyword) !== false) {
                                        $matchCount++;
                                    }
                                }
                                
                                if ($matchCount > $maxMatchCount) {
                                    $maxMatchCount = $matchCount;
                                    $bestMatch = $db;
                                }
                            }
                            
                            if ($bestMatch && $maxMatchCount > 0) {
                                $newDb = $bestMatch;
                            }
                        }
                        
                        // Update if different
                        if ($currentDb !== $newDb) {
                            $stmt = $masterDb->prepare("UPDATE businesses SET database_name = ? WHERE id = ?");
                            $stmt->execute([$newDb, $biz['id']]);
                            
                            echo '<p class="success-log">   ✅ Updated: ' . $currentDb . ' → ' . $newDb . '</p>';
                            $updateCount++;
                        } else {
                            echo '<p>   ℹ️  No change needed</p>';
                        }
                    }
                    
                    echo '<p></p>';
                    echo '<p class="success-log">========================================</p>';
                    echo '<p class="success-log">🎉 UPDATE SELESAI!</p>';
                    echo '<p class="success-log">========================================</p>';
                    echo '<p class="info-log">Total updated: ' . $updateCount . ' business(es)</p>';
                    
                    echo '</div>';
                    
                    echo '<div style="text-align: center;">';
                    echo '<a href="?" class="btn">🔄 Kembali</a>';
                    echo '<a href="debug-cash-accounts.php" class="btn" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);">🔍 Cek Hasil</a>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="section" style="border-left-color: #dc3545;">';
                echo '<h2 style="color: #dc3545;">❌ Error</h2>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
