<?php
/**
 * Debug script to check developer user
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Developer User Debug</h2>";
    echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
    
    // Check all users with developer role
    echo "<h3>All Users with 'developer' role:</h3>";
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.is_active, u.password, r.role_code 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE r.role_code = 'developer' OR u.username = 'developer'
        LIMIT 10
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p style='color:red;'><strong>No developer users found!</strong></p>";
        
        // Show all users
        echo "<h3>All users in database:</h3>";
        $stmt = $pdo->query("SELECT id, username, full_name, is_active, role_id FROM users LIMIT 20");
        $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . json_encode($allUsers, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Active</th><th>Role</th><th>Password Hash (First 30 chars)</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . htmlspecialchars($user['role_code'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(substr($user['password'], 0, 30)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check roles
    echo "<h3>All roles in database:</h3>";
    $stmt = $pdo->query("SELECT id, role_code, role_name FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . json_encode($roles, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<hr>";
    echo "<p><a href='developer/login.php'>Back to Login</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
