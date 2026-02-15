<?php
/**
 * Debug Cash Accounts Dropdown Issue
 * Check business ID detection and cash accounts availability
 */

require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Cash Accounts</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #00ff00; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #00ff00; border-radius: 8px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        h2 { color: #00ffff; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #00ff00; padding: 8px; text-align: left; }
        th { background: #003300; }
        pre { background: #000; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

<h1>🔍 Debug Cash Accounts Dropdown</h1>

<div class="section">
    <h2>1️⃣ Session Information</h2>
    <pre><?php
    echo "Session ID: " . session_id() . "\n";
    echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
    echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
    echo "Selected Business ID: " . ($_SESSION['selected_business_id'] ?? 'NOT SET') . "\n";
    echo "Business ID: " . ($_SESSION['business_id'] ?? 'NOT SET') . "\n";
    ?></pre>
</div>

<div class="section">
    <h2>2️⃣ Constants</h2>
    <pre><?php
    echo "ACTIVE_BUSINESS_ID: " . (defined('ACTIVE_BUSINESS_ID') ? ACTIVE_BUSINESS_ID : 'NOT DEFINED') . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    ?></pre>
</div>

<div class="section">
    <h2>3️⃣ Business ID Detection Logic</h2>
    <?php
    try {
        $masterDb = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $masterDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p class='success'>✅ Master DB connection successful</p>";
        
        $businessId = null;
        $detectionMethod = 'NONE';
        
        // Method 1: From session
        if (isset($_SESSION['selected_business_id'])) {
            $stmt = $masterDb->prepare("SELECT id, business_name FROM businesses WHERE id = ?");
            $stmt->execute([$_SESSION['selected_business_id']]);
            $businessRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($businessRecord) {
                $businessId = $businessRecord['id'];
                $detectionMethod = 'Session (selected_business_id)';
                echo "<p class='success'>✅ Method 1: Found from session - Business ID: {$businessId} ({$businessRecord['business_name']})</p>";
            } else {
                echo "<p class='warning'>⚠️ Method 1: Session ID set but not found in database</p>";
            }
        } else {
            echo "<p class='warning'>⚠️ Method 1: selected_business_id not in session</p>";
        }
        
        // Method 2: From ACTIVE_BUSINESS_ID with hardcode mapping
        if (!$businessId && defined('ACTIVE_BUSINESS_ID')) {
            $businessMapping = [
                'narayana-hotel' => 1,
                'bens-cafe' => 2
            ];
            if (isset($businessMapping[ACTIVE_BUSINESS_ID])) {
                $businessId = $businessMapping[ACTIVE_BUSINESS_ID];
                $detectionMethod = 'Hardcoded mapping from ACTIVE_BUSINESS_ID';
                echo "<p class='success'>✅ Method 2: Hardcoded mapping - Business ID: {$businessId}</p>";
            } else {
                echo "<p class='warning'>⚠️ Method 2: ACTIVE_BUSINESS_ID not in hardcoded mapping</p>";
            }
        }
        
        // Method 3: Database query by identifier
        if (!$businessId && defined('ACTIVE_BUSINESS_ID')) {
            $stmt = $masterDb->prepare("SELECT id, business_name FROM businesses WHERE business_identifier = ? OR database_name LIKE ?");
            $stmt->execute([ACTIVE_BUSINESS_ID, '%' . ACTIVE_BUSINESS_ID . '%']);
            $businessRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($businessRecord) {
                $businessId = $businessRecord['id'];
                $detectionMethod = 'Database query by identifier';
                echo "<p class='success'>✅ Method 3: Database query - Business ID: {$businessId} ({$businessRecord['business_name']})</p>";
            } else {
                echo "<p class='warning'>⚠️ Method 3: No match in database by identifier</p>";
            }
        }
        
        // Method 4: First active business
        if (!$businessId) {
            $stmt = $masterDb->query("SELECT id, business_name FROM businesses WHERE is_active = 1 ORDER BY id LIMIT 1");
            $businessRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($businessRecord) {
                $businessId = $businessRecord['id'];
                $detectionMethod = 'First active business (fallback)';
                echo "<p class='warning'>⚠️ Method 4: Using first active business - Business ID: {$businessId} ({$businessRecord['business_name']})</p>";
            }
        }
        
        if ($businessId) {
            echo "<h3 class='success'>✅ FINAL DETECTED BUSINESS ID: {$businessId}</h3>";
            echo "<p>Detection Method: <strong>{$detectionMethod}</strong></p>";
        } else {
            echo "<h3 class='error'>❌ FAILED TO DETECT BUSINESS ID</h3>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    ?>
</div>

<div class="section">
    <h2>4️⃣ All Businesses in Database</h2>
    <?php
    try {
        $stmt = $masterDb->query("SELECT * FROM businesses ORDER BY id");
        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($businesses)) {
            echo "<p class='error'>❌ No businesses found in database!</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Business Name</th><th>Identifier</th><th>Database Name</th><th>Active</th></tr>";
            foreach ($businesses as $biz) {
                echo "<tr>";
                echo "<td>{$biz['id']}</td>";
                echo "<td>{$biz['business_name']}</td>";
                echo "<td>" . ($biz['business_identifier'] ?? 'N/A') . "</td>";
                echo "<td>{$biz['database_name']}</td>";
                echo "<td>" . ($biz['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    ?>
</div>

<div class="section">
    <h2>5️⃣ Cash Accounts for Detected Business</h2>
    <?php
    if (isset($businessId) && $businessId) {
        try {
            $stmt = $masterDb->prepare("SELECT * FROM cash_accounts WHERE business_id = ? ORDER BY account_type, account_name");
            $stmt->execute([$businessId]);
            $cashAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cashAccounts)) {
                echo "<p class='error'>❌ No cash accounts found for business ID {$businessId}!</p>";
                echo "<p class='warning'>⚠️ This is why dropdown is empty. You need to create cash accounts for this business.</p>";
            } else {
                echo "<p class='success'>✅ Found " . count($cashAccounts) . " cash account(s)</p>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Account Name</th><th>Type</th><th>Balance</th><th>Default</th></tr>";
                foreach ($cashAccounts as $acc) {
                    echo "<tr>";
                    echo "<td>{$acc['id']}</td>";
                    echo "<td>{$acc['account_name']}</td>";
                    echo "<td>{$acc['account_type']}</td>";
                    echo "<td>Rp " . number_format($acc['current_balance'], 0, ',', '.') . "</td>";
                    echo "<td>" . ($acc['is_default_account'] ? '✅ Yes' : 'No') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ Cannot check cash accounts - Business ID not detected</p>";
    }
    ?>
</div>

<div class="section">
    <h2>6️⃣ Recommended Action</h2>
    <?php
    if (!isset($businessId) || !$businessId) {
        echo "<p class='error'>🔧 <strong>FIX REQUIRED:</strong> Business ID detection failed. Check your session or config.</p>";
    } elseif (empty($cashAccounts)) {
        echo "<p class='warning'>🔧 <strong>ACTION NEEDED:</strong> Create cash accounts for business ID {$businessId}</p>";
        echo "<p>Run one of these setup scripts:</p>";
        echo "<ul>";
        echo "<li><a href='tools/setup-accounting-local-safe.php' target='_blank' style='color: #00ffff;'>Setup Accounting (Safe Mode)</a></li>";
        echo "<li><a href='auto-setup.php' target='_blank' style='color: #00ffff;'>Auto Setup</a></li>";
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ <strong>ALL GOOD!</strong> Business ID detected and cash accounts available.</p>";
        echo "<p>If dropdown still empty, clear browser cache and reload the cashbook add/edit page.</p>";
    }
    ?>
</div>

<div style="margin-top: 30px; padding: 15px; background: #003300; border-radius: 8px;">
    <p style="margin: 0;">Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p style="margin: 5px 0 0 0;"><a href="modules/cashbook/add.php" style="color: #00ffff;">← Back to Add Transaction</a></p>
</div>

</body>
</html>
