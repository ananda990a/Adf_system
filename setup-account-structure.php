<?php
/**
 * Setup Proper Account Structure
 * Based on actual business logic
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

$masterDb = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$masterDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$businessMapping = [
    'narayana-hotel' => 1,
    'bens-cafe' => 2
];

$businessId = $businessMapping[ACTIVE_BUSINESS_ID] ?? 1;

echo "<h1>🏨 Setup Account Structure - Narayana Hotel</h1>";
echo "<hr>";

// Show current accounts
echo "<h2>Current Accounts:</h2>";
$stmt = $masterDb->prepare("SELECT * FROM cash_accounts WHERE business_id = ? ORDER BY id");
$stmt->execute([$businessId]);
$currentAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Type</th><th>Balance</th><th>Action</th></tr>";

foreach ($currentAccounts as $acc) {
    echo "<tr>";
    echo "<td>{$acc['id']}</td>";
    echo "<td><strong>{$acc['account_name']}</strong></td>";
    echo "<td><code>{$acc['account_type']}</code></td>";
    echo "<td>Rp " . number_format($acc['current_balance'], 0, ',', '.') . "</td>";
    echo "<td>";
    echo "<form method='POST' style='display:inline;'>";
    echo "<input type='hidden' name='delete_account' value='{$acc['id']}'>";
    echo "<button type='submit' onclick=\"return confirm('Delete account {$acc['account_name']}?')\" style='padding: 5px 10px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;'>Delete</button>";
    echo "</form>";
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Process delete
if (isset($_POST['delete_account'])) {
    $deleteId = $_POST['delete_account'];
    $stmt = $masterDb->prepare("DELETE FROM cash_accounts WHERE id = ? AND business_id = ?");
    $stmt->execute([$deleteId, $businessId]);
    
    echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "✅ Account deleted! <a href='setup-account-structure.php'>Refresh page</a>";
    echo "</div>";
}

echo "<hr>";

// RECOMMENDED SETUP
echo "<h2>📋 Recommended Account Setup:</h2>";

echo "<div style='background: #e3f2fd; padding: 20px; border-left: 4px solid #2196f3; margin: 20px 0;'>";
echo "<h3>3 Account yang dibutuhkan:</h3>";

echo "<table border='1' cellpadding='12' style='border-collapse: collapse; width: 100%; background: white;'>";
echo "<tr style='background: #f0f0f0;'><th>Account Name</th><th>Type</th><th>Purpose</th><th>Behavior</th></tr>";

echo "<tr style='background: #fff3cd;'>";
echo "<td><strong>Petty Cash</strong></td>";
echo "<td><code>cash</code></td>";
echo "<td>💰 Pembayaran CASH dari tamu<br><small>Uang fisik yang langsung bisa pakai operasional</small></td>";
echo "<td>✅ Masuk ke Revenue Hotel<br>✅ Bisa langsung pakai operasional</td>";
echo "</tr>";

echo "<tr style='background: #d1ecf1;'>";
echo "<td><strong>Bank</strong></td>";
echo "<td><code>bank</code></td>";
echo "<td>🏦 Pembayaran TRANSFER dari tamu<br><small>EDC, QR Code, Transfer Bank</small></td>";
echo "<td>✅ Masuk ke Revenue Hotel<br>⚠️ Di bank, tidak bisa pakai langsung</td>";
echo "</tr>";

echo "<tr style='background: #d4edda;'>";
echo "<td><strong>Kas Modal Owner</strong></td>";
echo "<td><code>owner_capital</code></td>";
echo "<td>💵 Modal dari owner untuk operasional<br><small>Ketika semua transfer, gak ada cash fisik</small></td>";
echo "<td>❌ TIDAK masuk Revenue Hotel<br>✅ Bisa pakai operasional</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
echo "<h3>💡 Logika Bisnis:</h3>";
echo "<ul style='line-height: 2;'>";
echo "<li><strong>Total Revenue Hotel:</strong> Petty Cash + Bank (semua pembayaran dari tamu)</li>";
echo "<li><strong>Total Kas Operasional:</strong> Petty Cash + Kas Modal Owner (uang fisik yang bisa pakai)</li>";
echo "<li><strong>Dashboard Owner:</strong> Revenue - Modal Owner yang dikasih = Hasil Bersih</li>";
echo "</ul>";
echo "</div>";

// AUTO CREATE ACCOUNTS
echo "<hr>";
echo "<h2>🔧 Auto Setup Accounts:</h2>";

echo "<form method='POST'>";
echo "<div style='background: #e8f5e9; padding: 20px; border-left: 4px solid #4caf50;'>";
echo "<p><strong>Akan membuat 3 account:</strong></p>";
echo "<ol>";
echo "<li>Petty Cash (type: cash)</li>";
echo "<li>Bank (type: bank)</li>";
echo "<li>Kas Modal Owner (type: owner_capital)</li>";
echo "</ol>";

echo "<label style='display: block; margin: 15px 0;'>";
echo "<input type='checkbox' name='confirm' required> ";
echo "Saya mengerti dan ingin setup 3 account ini";
echo "</label>";

echo "<button type='submit' name='action' value='create_accounts' style='padding: 15px 30px; background: #4caf50; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;'>";
echo "✅ CREATE 3 ACCOUNTS NOW";
echo "</button>";
echo "</div>";
echo "</form>";

// Process create
if (isset($_POST['action']) && $_POST['action'] === 'create_accounts') {
    echo "<hr>";
    echo "<h2>Creating Accounts...</h2>";
    
    $accounts = [
        [
            'account_name' => 'Petty Cash',
            'account_type' => 'cash',
            'account_number' => null,
            'current_balance' => 0,
            'is_default_account' => 1,
            'is_active' => 1,
            'description' => 'Pembayaran cash dari tamu - langsung bisa pakai operasional'
        ],
        [
            'account_name' => 'Bank',
            'account_type' => 'bank',
            'account_number' => null,
            'current_balance' => 0,
            'is_default_account' => 0,
            'is_active' => 1,
            'description' => 'Pembayaran transfer dari tamu (EDC/QR/Transfer)'
        ],
        [
            'account_name' => 'Kas Modal Owner',
            'account_type' => 'owner_capital',
            'account_number' => null,
            'current_balance' => 0,
            'is_default_account' => 0,
            'is_active' => 1,
            'description' => 'Modal dari owner untuk operasional - BUKAN pendapatan hotel'
        ]
    ];
    
    foreach ($accounts as $acc) {
        try {
            $stmt = $masterDb->prepare("
                INSERT INTO cash_accounts 
                (business_id, account_name, account_type, account_number, current_balance, is_default_account, is_active, description, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $businessId,
                $acc['account_name'],
                $acc['account_type'],
                $acc['account_number'],
                $acc['current_balance'],
                $acc['is_default_account'],
                $acc['is_active'],
                $acc['description']
            ]);
            
            echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745;'>";
            echo "✅ Created: <strong>{$acc['account_name']}</strong> (type: {$acc['account_type']})";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #f44336;'>";
            echo "❌ Error creating {$acc['account_name']}: " . $e->getMessage();
            echo "</div>";
        }
    }
    
    echo "<div style='background: #d1ecf1; padding: 20px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
    echo "<h3>✅ Setup Complete!</h3>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol style='line-height: 2;'>";
    echo "<li><a href='setup-account-structure.php' style='color: #0056b3; font-weight: bold;'>Refresh halaman ini</a> untuk lihat account baru</li>";
    echo "<li><a href='modules/cashbook/add.php' style='color: #0056b3; font-weight: bold;'>Input transaksi</a> dengan memilih account yang tepat</li>";
    echo "<li><a href='index.php' style='color: #0056b3; font-weight: bold;'>Lihat Dashboard</a> - sekarang Revenue dan Modal Owner terpisah</li>";
    echo "</ol>";
    echo "</div>";
}

?>

<style>
    body { 
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
        padding: 30px;
        background: #f5f5f5;
        max-width: 1200px;
        margin: 0 auto;
    }
    h1, h2, h3 { color: #333; }
    table { background: white; margin: 10px 0; }
    code { 
        background: #f4f4f4; 
        padding: 3px 8px; 
        border-radius: 3px; 
        font-weight: bold;
    }
</style>
