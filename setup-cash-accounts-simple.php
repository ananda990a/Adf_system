<?php
/**
 * Simple Cash Accounts Setup Tool
 * Creates cash_accounts table and default accounts for all active businesses
 */

require_once 'config/config.php';
require_once 'config/database.php';

// Security: only allow logged in admin/developer
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'developer', 'owner'])) {
    die('Access denied. Login as admin/developer/owner first.');
}

$masterDb = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$masterDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_GET['action'] ?? 'check';
$confirmed = $_GET['confirm'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Cash Accounts</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { color: #333; margin-top: 0; }
        .step { 
            margin: 20px 0; 
            padding: 20px; 
            border-radius: 8px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
        }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
            background: white;
        }
        th, td { 
            border: 1px solid #dee2e6; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: #667eea; 
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) { background: #f8f9fa; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
        pre { 
            background: #2d3748; 
            color: #68d391; 
            padding: 15px; 
            border-radius: 6px; 
            overflow-x: auto;
            font-size: 12px;
        }
        .code { 
            background: #e9ecef; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace;
            color: #e83e8c;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>💰 Cash Accounts Setup Tool</h1>
    <p style="color: #6c757d; margin-bottom: 30px;">
        Tool ini akan membuat tabel <span class="code">cash_accounts</span> dan akun kas default untuk semua bisnis aktif.
    </p>

<?php

if ($action === 'check') {
    // =====================================================
    // STEP 1: CHECK CURRENT STATUS
    // =====================================================
    
    echo '<div class="step info">';
    echo '<h2>📊 Step 1: Checking Current Status</h2>';
    
    // Check if cash_accounts table exists
    try {
        $stmt = $masterDb->query("SHOW TABLES LIKE 'cash_accounts'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo '<p>✅ Table <span class="code">cash_accounts</span> sudah ada</p>';
            
            // Check existing accounts
            $stmt = $masterDb->query("SELECT ca.*, b.business_name 
                                      FROM cash_accounts ca 
                                      LEFT JOIN businesses b ON ca.business_id = b.id 
                                      ORDER BY ca.business_id, ca.account_type");
            $existingAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($existingAccounts)) {
                echo '<p class="warning">⚠️ Tabel ada tapi KOSONG - belum ada akun kas</p>';
            } else {
                echo '<p>✅ Ditemukan ' . count($existingAccounts) . ' akun kas:</p>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Business</th><th>Account Name</th><th>Type</th><th>Balance</th><th>Default</th></tr>';
                foreach ($existingAccounts as $acc) {
                    echo '<tr>';
                    echo '<td>' . $acc['id'] . '</td>';
                    echo '<td>' . ($acc['business_name'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($acc['account_name']) . '</td>';
                    echo '<td>' . $acc['account_type'] . '</td>';
                    echo '<td>Rp ' . number_format($acc['current_balance'], 0, ',', '.') . '</td>';
                    echo '<td>' . ($acc['is_default_account'] ? '✅' : '-') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } else {
            echo '<p class="warning">⚠️ Table <span class="code">cash_accounts</span> BELUM ADA</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    // =====================================================
    // STEP 2: GET ACTIVE BUSINESSES
    // =====================================================
    
    echo '<div class="step info">';
    echo '<h2>🏢 Step 2: Active Businesses</h2>';
    
    try {
        $stmt = $masterDb->query("SELECT * FROM businesses WHERE is_active = 1 ORDER BY id");
        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($businesses)) {
            echo '<p class="error">❌ Tidak ada bisnis aktif ditemukan!</p>';
        } else {
            echo '<p>✅ Ditemukan ' . count($businesses) . ' bisnis aktif:</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Business Name</th><th>Database Name</th></tr>';
            foreach ($businesses as $biz) {
                echo '<tr>';
                echo '<td>' . $biz['id'] . '</td>';
                echo '<td>' . $biz['business_name'] . '</td>';
                echo '<td>' . $biz['database_name'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    } catch (Exception $e) {
        echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    // =====================================================
    // STEP 3: WHAT WILL BE CREATED
    // =====================================================
    
    echo '<div class="step warning">';
    echo '<h2>🔨 Step 3: Yang Akan Dibuat</h2>';
    
    if (!empty($businesses)) {
        echo '<p>Untuk setiap bisnis, akan dibuat <strong>3 akun kas</strong>:</p>';
        echo '<ol>';
        echo '<li><strong>Kas Operasional (Petty Cash)</strong> - Untuk cash dari tamu</li>';
        echo '<li><strong>Rekening Bank</strong> - Untuk transfer/OTA dari tamu</li>';
        echo '<li><strong>Kas Modal Owner</strong> - Untuk modal dari owner</li>';
        echo '</ol>';
        
        echo '<table>';
        echo '<tr><th>Business</th><th>Accounts to Create</th></tr>';
        foreach ($businesses as $biz) {
            echo '<tr>';
            echo '<td>' . $biz['business_name'] . '</td>';
            echo '<td>';
            echo '• Kas Operasional<br>';
            echo '• Rekening Bank<br>';
            echo '• Kas Modal Owner';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<p><strong>Total:</strong> ' . (count($businesses) * 3) . ' akun kas akan dibuat</p>';
    }
    
    echo '</div>';
    
    // =====================================================
    // ACTION BUTTONS
    // =====================================================
    
    if (!empty($businesses)) {
        echo '<div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">';
        
        if (!$tableExists || empty($existingAccounts)) {
            echo '<p style="font-size: 18px; font-weight: bold; color: #dc3545; margin-bottom: 20px;">⚠️ PERHATIAN: Aksi ini akan membuat perubahan di database!</p>';
            echo '<a href="?action=execute&confirm=yes" class="btn btn-success" onclick="return confirm(\'Yakin ingin membuat cash accounts?\')">✅ JALANKAN SETUP</a>';
        } else {
            echo '<p style="color: #28a745; font-weight: bold;">✅ Cash accounts sudah tersedia</p>';
            echo '<p>Jika ingin reset/recreate, hapus tabel cash_accounts dulu dari database</p>';
        }
        
        echo '<a href="debug-cash-accounts.php" class="btn btn-primary">🔍 Cek Status Detail</a>';
        echo '<a href="modules/cashbook/add.php" class="btn btn-secondary">← Back to Cashbook</a>';
        echo '</div>';
    }
    
} elseif ($action === 'execute' && $confirmed === 'yes') {
    
    // =====================================================
    // EXECUTE: CREATE TABLE AND ACCOUNTS
    // =====================================================
    
    echo '<div class="step info">';
    echo '<h2>⚙️ Executing Setup...</h2>';
    
    try {
        $masterDb->beginTransaction();
        
        // Create table if not exists
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `cash_accounts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `business_id` int(11) NOT NULL COMMENT 'Link to businesses table',
            `account_name` varchar(100) NOT NULL,
            `account_type` enum('cash','bank','owner_capital') NOT NULL DEFAULT 'cash',
            `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
            `is_default_account` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `business_id` (`business_id`),
            KEY `account_type` (`account_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $masterDb->exec($createTableSQL);
        echo '<p>✅ Table <span class="code">cash_accounts</span> created/verified</p>';
        
        // Get active businesses
        $stmt = $masterDb->query("SELECT * FROM businesses WHERE is_active = 1 ORDER BY id");
        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $accountsCreated = 0;
        
        foreach ($businesses as $biz) {
            echo '<p><strong>Creating accounts for: ' . $biz['business_name'] . '</strong></p>';
            
            // Check if accounts already exist for this business
            $stmt = $masterDb->prepare("SELECT COUNT(*) as count FROM cash_accounts WHERE business_id = ?");
            $stmt->execute([$biz['id']]);
            $existingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($existingCount > 0) {
                echo '<p style="color: #ffc107;">⚠️ Already has ' . $existingCount . ' account(s), skipping...</p>';
                continue;
            }
            
            // Account 1: Kas Operasional (Petty Cash)
            $stmt = $masterDb->prepare("INSERT INTO cash_accounts 
                (business_id, account_name, account_type, current_balance, is_default_account) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$biz['id'], 'Kas Operasional', 'cash', 0, 1]);
            echo '<p>✅ Created: <span class="code">Kas Operasional</span> (Petty Cash)</p>';
            $accountsCreated++;
            
            // Account 2: Rekening Bank
            $stmt = $masterDb->prepare("INSERT INTO cash_accounts 
                (business_id, account_name, account_type, current_balance, is_default_account) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$biz['id'], 'Rekening Bank', 'bank', 0, 0]);
            echo '<p>✅ Created: <span class="code">Rekening Bank</span> (Bank Transfer)</p>';
            $accountsCreated++;
            
            // Account 3: Kas Modal Owner
            $stmt = $masterDb->prepare("INSERT INTO cash_accounts 
                (business_id, account_name, account_type, current_balance, is_default_account) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$biz['id'], 'Kas Modal Owner', 'owner_capital', 0, 0]);
            echo '<p>✅ Created: <span class="code">Kas Modal Owner</span> (Owner Capital)</p>';
            $accountsCreated++;
            
            echo '<hr style="border: none; border-top: 1px dashed #dee2e6; margin: 15px 0;">';
        }
        
        $masterDb->commit();
        
        echo '</div>';
        
        echo '<div class="step success">';
        echo '<h2>🎉 Setup Complete!</h2>';
        echo '<p><strong>Total accounts created: ' . $accountsCreated . '</strong></p>';
        echo '<p>Dropdown akun kas di form cashbook seharusnya sudah terisi sekarang.</p>';
        echo '</div>';
        
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<a href="debug-cash-accounts.php" class="btn btn-primary">🔍 Verify Setup</a>';
        echo '<a href="modules/cashbook/add.php" class="btn btn-success">➡️ Go to Cashbook</a>';
        echo '<a href="?action=check" class="btn btn-secondary">🔄 Check Status Again</a>';
        echo '</div>';
        
    } catch (Exception $e) {
        $masterDb->rollBack();
        
        echo '</div>';
        
        echo '<div class="step error">';
        echo '<h2>❌ Setup Failed!</h2>';
        echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        echo '</div>';
        
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<a href="?action=check" class="btn btn-secondary">← Back to Check</a>';
        echo '</div>';
    }
}

?>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-top: 3px solid #667eea;">
        <p style="margin: 0; color: #6c757d; font-size: 14px;">
            <strong>Info:</strong> Script ini aman dijalankan berkali-kali. Tidak akan duplicate accounts yang sudah ada.
        </p>
        <p style="margin: 10px 0 0 0; color: #6c757d; font-size: 12px;">
            Generated: <?php echo date('Y-m-d H:i:s'); ?> | User: <?php echo $_SESSION['username'] ?? 'Unknown'; ?>
        </p>
    </div>

</div>

</body>
</html>
