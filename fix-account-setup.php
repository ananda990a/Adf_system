<?php
/**
 * Fix Account Setup - Swap/Fix Petty Cash and Modal Owner
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

// Connect to master DB
try {
    $masterDb = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $masterDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $businessMapping = [
        'narayana-hotel' => 1,
        'bens-cafe' => 2
    ];
    
    $businessId = $businessMapping[ACTIVE_BUSINESS_ID] ?? 1;
    
    echo "<h2>Current Cash Accounts for Business ID: $businessId (" . ACTIVE_BUSINESS_ID . ")</h2>";
    
    // Get all accounts
    $stmt = $masterDb->prepare("SELECT * FROM cash_accounts WHERE business_id = ? ORDER BY id");
    $stmt->execute([$businessId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; font-family: monospace; margin-bottom: 30px;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>ID</th>
            <th>Account Name</th>
            <th>Account Type</th>
            <th>Balance</th>
            <th>Default</th>
            <th>Problem?</th>
          </tr>";
    
    foreach ($accounts as $acc) {
        $problem = '';
        $bgColor = '#fff';
        
        // Check for problems
        if (stripos($acc['account_name'], 'modal') !== false && $acc['account_type'] !== 'owner_capital') {
            $problem = '⚠️ WRONG TYPE! Nama "Modal" tapi type bukan owner_capital';
            $bgColor = '#ffcccc';
        }
        
        if (stripos($acc['account_name'], 'owner') !== false && $acc['account_type'] !== 'owner_capital') {
            $problem = '⚠️ WRONG TYPE! Nama "Owner" tapi type bukan owner_capital';
            $bgColor = '#ffcccc';
        }
        
        if (stripos($acc['account_name'], 'petty') !== false && $acc['account_type'] !== 'cash') {
            $problem = '⚠️ WRONG TYPE! Nama "Petty Cash" tapi type bukan cash';
            $bgColor = '#ffcccc';
        }
        
        if (stripos($acc['account_name'], 'bank') !== false && $acc['account_type'] !== 'bank') {
            $problem = '⚠️ WRONG TYPE! Nama "Bank" tapi type bukan bank';
            $bgColor = '#ffcccc';
        }
        
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$acc['id']}</td>";
        echo "<td><strong>{$acc['account_name']}</strong></td>";
        echo "<td>{$acc['account_type']}</td>";
        echo "<td>Rp " . number_format($acc['current_balance'], 0, ',', '.') . "</td>";
        echo "<td>" . ($acc['is_default_account'] ? 'YES' : 'No') . "</td>";
        echo "<td>{$problem}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // RECOMMENDED SETUP
    echo "<div style='background: #e3f2fd; padding: 20px; border-left: 4px solid #2196f3; margin: 20px 0;'>";
    echo "<h3 style='color: #1565c0; margin-top: 0;'>📋 SETUP YANG BENAR:</h3>";
    echo "<ul style='line-height: 1.8;'>";
    echo "<li><strong>Petty Cash</strong> (account_type = 'cash') → Untuk <u>hasil penjualan hotel</u> (uang masuk dari tamu)</li>";
    echo "<li><strong>Kas Modal Owner</strong> (account_type = 'owner_capital') → Untuk <u>modal/suntikan dari owner</u> (owner transfer uang untuk operasional)</li>";
    echo "<li><strong>Bank</strong> (account_type = 'bank') → Untuk <u>hasil penjualan yang masuk ke rekening</u></li>";
    echo "</ul>";
    echo "</div>";
    
    // AUTO FIX BUTTON
    echo "<form method='POST' style='margin: 30px 0;'>";
    echo "<h3>🔧 Auto Fix Options:</h3>";
    
    // Find problematic accounts
    $needsFix = false;
    foreach ($accounts as $acc) {
        if ((stripos($acc['account_name'], 'modal') !== false || stripos($acc['account_name'], 'owner') !== false) 
            && $acc['account_type'] !== 'owner_capital') {
            $needsFix = true;
            echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
            echo "<strong>Found:</strong> Account ID {$acc['id']} - <strong>{$acc['account_name']}</strong> (type: {$acc['account_type']})<br>";
            echo "<label style='margin-top: 10px; display: block;'>";
            echo "<input type='checkbox' name='fix_account[]' value='{$acc['id']}' checked> ";
            echo "Ubah account_type dari <code>{$acc['account_type']}</code> menjadi <code>owner_capital</code>";
            echo "</label>";
            echo "</div>";
        }
    }
    
    if ($needsFix) {
        echo "<button type='submit' name='action' value='fix_types' style='padding: 15px 30px; background: #f44336; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px;'>";
        echo "🔧 FIX ACCOUNT TYPES NOW";
        echo "</button>";
    } else {
        echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745;'>";
        echo "✅ <strong>All accounts are correctly configured!</strong>";
        echo "</div>";
    }
    
    echo "</form>";
    
    // PROCESS FIX
    if (isset($_POST['action']) && $_POST['action'] === 'fix_types') {
        echo "<hr style='margin: 40px 0;'>";
        echo "<h2>🔧 Fixing Account Types...</h2>";
        
        if (isset($_POST['fix_account']) && is_array($_POST['fix_account'])) {
            foreach ($_POST['fix_account'] as $accountId) {
                $stmt = $masterDb->prepare("UPDATE cash_accounts SET account_type = 'owner_capital' WHERE id = ? AND business_id = ?");
                $stmt->execute([$accountId, $businessId]);
                
                echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745;'>";
                echo "✅ Account ID $accountId updated to account_type = 'owner_capital'";
                echo "</div>";
            }
            
            echo "<div style='background: #d1ecf1; padding: 20px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
            echo "<strong>✅ FIX COMPLETED!</strong><br>";
            echo "Sekarang exclusion filter akan bekerja dengan benar.<br>";
            echo "Income/expense ke account ini TIDAK akan masuk ke pendapatan hotel.<br><br>";
            echo "<a href='fix-account-setup.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>🔄 Refresh Page</a> ";
            echo "<a href='index.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;'>📊 Lihat Dashboard</a>";
            echo "</div>";
        }
    }
    
    // MANUAL RENAME FORM
    echo "<hr style='margin: 40px 0;'>";
    echo "<h3>✏️ Manual Rename Account (Optional)</h3>";
    echo "<form method='POST'>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Account</th><th>New Name</th><th>Action</th></tr>";
    
    foreach ($accounts as $acc) {
        echo "<tr>";
        echo "<td><strong>{$acc['account_name']}</strong> (ID: {$acc['id']}, Type: {$acc['account_type']})</td>";
        echo "<td><input type='text' name='new_name[{$acc['id']}]' value='{$acc['account_name']}' style='width: 300px; padding: 5px;'></td>";
        echo "<td><button type='submit' name='rename_account' value='{$acc['id']}' style='padding: 5px 15px; background: #ff9800; color: white; border: none; border-radius: 4px; cursor: pointer;'>Rename</button></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</form>";
    
    // PROCESS RENAME
    if (isset($_POST['rename_account'])) {
        $accountId = $_POST['rename_account'];
        $newName = $_POST['new_name'][$accountId] ?? '';
        
        if (!empty($newName)) {
            $stmt = $masterDb->prepare("UPDATE cash_accounts SET account_name = ? WHERE id = ? AND business_id = ?");
            $stmt->execute([$newName, $accountId, $businessId]);
            
            echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-left: 4px solid #28a745;'>";
            echo "✅ Account ID $accountId renamed to: <strong>$newName</strong><br>";
            echo "<a href='fix-account-setup.php'>Refresh page to see changes</a>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<style>
    body { 
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
        padding: 30px;
        background: #f5f5f5;
    }
    h2, h3 { color: #333; }
    table { background: white; }
    code { 
        background: #f4f4f4; 
        padding: 2px 6px; 
        border-radius: 3px; 
        font-family: 'Courier New', monospace;
    }
</style>
