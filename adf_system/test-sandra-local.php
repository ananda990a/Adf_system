<?php
/**
 * Test Sandra Login di LOKAL
 * Verify semua data Sandra correct di local database
 */

// Test 1: Check MASTER database
echo "<h2>🔍 TEST 1: Check Master Database (adf_system)</h2>";

$masterPdo = new PDO("mysql:host=localhost;dbname=adf_system", "root", "");
$masterPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $masterPdo->prepare("SELECT * FROM users WHERE username = 'sandra'");
$stmt->execute();
$sandra = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sandra) {
    echo "✅ Sandra EXISTS in master database<br>";
    echo "ID: " . $sandra['id'] . "<br>";
    echo "Username: " . $sandra['username'] . "<br>";
    echo "Full Name: " . $sandra['full_name'] . "<br>";
    echo "Email: " . $sandra['email'] . "<br>";
    echo "Role ID: " . $sandra['role_id'] . "<br>";
    echo "Is Active: " . ($sandra['is_active'] ? 'YES ✅' : 'NO ❌') . "<br>";
    echo "Password Hash: " . substr($sandra['password'], 0, 20) . "...<br><br>";
    
    // Test password verification
    $testPassword = 'admin123';
    if (password_verify($testPassword, $sandra['password'])) {
        echo "✅ Password 'admin123' VERIFIED - password correct!<br><br>";
    } else {
        echo "❌ Password 'admin123' FAILED - password incorrect!<br><br>";
    }
} else {
    echo "❌ Sandra NOT FOUND in master database<br><br>";
}

// Test 2: Check permissions
echo "<h2>🔍 TEST 2: Check User Permissions</h2>";

if ($sandra) {
    $permStmt = $masterPdo->prepare("
        SELECT b.business_name, b.business_code, COUNT(p.menu_id) as menu_count
        FROM user_menu_permissions p
        JOIN businesses b ON p.business_id = b.id
        WHERE p.user_id = ?
        GROUP BY b.id
    ");
    $permStmt->execute([$sandra['id']]);
    $permissions = $permStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($permissions)) {
        echo "✅ Permissions FOUND:<br>";
        foreach ($permissions as $perm) {
            echo "  - <strong>{$perm['business_name']}</strong> ({$perm['business_code']}): {$perm['menu_count']} menus<br>";
        }
    } else {
        echo "❌ NO permissions found for Sandra<br>";
    }
    echo "<br>";
}

// Test 3: Check businesses assigned
echo "<h2>🔍 TEST 3: Check Business Assignment</h2>";

if ($sandra) {
    $bizStmt = $masterPdo->prepare("
        SELECT b.business_name, b.business_code, uba.assigned_at
        FROM user_business_assignment uba
        JOIN businesses b ON uba.business_id = b.id
        WHERE uba.user_id = ?
    ");
    $bizStmt->execute([$sandra['id']]);
    $businesses = $bizStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($businesses)) {
        echo "✅ Business assignments FOUND:<br>";
        foreach ($businesses as $biz) {
            echo "  - <strong>{$biz['business_name']}</strong> ({$biz['business_code']}) - Assigned: {$biz['assigned_at']}<br>";
        }
    } else {
        echo "❌ NO business assignments found for Sandra<br>";
    }
    echo "<br>";
}

// Test 4: Simulate Login Process
echo "<h2>🔍 TEST 4: Simulate Login Process</h2>";

if ($sandra && password_verify('admin123', $sandra['password'])) {
    echo "✅ LOGIN would SUCCEED<br>";
    echo "Username: sandra<br>";
    echo "Password: admin123<br>";
    
    // Get accessible businesses (login.php logic)
    $bizStmt = $masterPdo->prepare("
        SELECT DISTINCT b.id, b.business_code, b.business_name
        FROM businesses b
        JOIN user_menu_permissions p ON b.id = p.business_id
        WHERE p.user_id = ?
        ORDER BY b.business_name
    ");
    $bizStmt->execute([$sandra['id']]);
    $userBusinesses = $bizStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($userBusinesses)) {
        echo "<br><strong>Sandra can access these businesses:</strong><br>";
        foreach ($userBusinesses as $biz) {
            echo "  ✅ {$biz['business_name']} ({$biz['business_code']})<br>";
        }
    } else {
        echo "<br>❌ Sandra has NO accessible businesses - login would show error!<br>";
    }
} else {
    echo "❌ LOGIN would FAIL<br>";
}

echo "<br><hr><br>";
echo "<h2>📋 Summary</h2>";
echo "<p><strong>Try login at:</strong> <a href='login.php' style='color: blue;'>login.php</a></p>";
echo "<p><strong>Username:</strong> sandra</p>";
echo "<p><strong>Password:</strong> admin123</p>";
?>
