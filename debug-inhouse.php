<?php
/**
 * Debug Inhouse Guest - Check what data the API returns
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

echo "<h2>Debug Inhouse Guest Data</h2>";

$auth = new Auth();
echo "<p>Logged in: " . ($auth->isLoggedIn() ? 'Yes' : 'No') . "</p>";

try {
    // Get master database
    $masterDb = getDbName('adf_system');
    echo "<h3>1. Master Database: " . htmlspecialchars($masterDb) . "</h3>";
    
    $mainPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . $masterDb . ";charset=utf8mb4", DB_USER, DB_PASS);
    $mainPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get businesses
    $stmt = $mainPdo->query("SELECT id, business_name, database_name, business_type FROM businesses WHERE is_active = 1");
    $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>2. Active Businesses:</h3><ul>";
    foreach ($businesses as $b) {
        echo "<li>" . htmlspecialchars($b['business_name']) . " - DB: " . htmlspecialchars($b['database_name']) . "</li>";
    }
    echo "</ul>";
    
    // Check each business for inhouse guests
    echo "<h3>3. Inhouse Guests per Business:</h3>";
    
    foreach ($businesses as $business) {
        $dbName = getDbName($business['database_name']);
        echo "<h4>" . htmlspecialchars($business['business_name']) . " (DB: " . htmlspecialchars($dbName) . ")</h4>";
        
        try {
            $bizPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8mb4", DB_USER, DB_PASS);
            $bizPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if bookings table exists
            $tables = $bizPdo->query("SHOW TABLES LIKE 'bookings'")->fetchAll();
            if (empty($tables)) {
                echo "<p style='color:orange;'>⚠️ No bookings table</p>";
                continue;
            }
            
            // Count inhouse
            $stmt = $bizPdo->query("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'checked_in'");
            $count = $stmt->fetch()['cnt'];
            echo "<p>Inhouse count: <strong>" . $count . "</strong></p>";
            
            // Get inhouse list
            $stmt = $bizPdo->query(
                "SELECT 
                    g.guest_name,
                    r.room_number,
                    b.check_in_date,
                    b.check_out_date,
                    b.status,
                    (COALESCE(b.adults, 1) + COALESCE(b.children, 0)) as total_guests
                 FROM bookings b
                 LEFT JOIN guests g ON b.guest_id = g.id
                 LEFT JOIN rooms r ON b.room_id = r.id
                 WHERE b.status = 'checked_in'
                 ORDER BY r.room_number ASC
                 LIMIT 10"
            );
            $inhouse = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($inhouse)) {
                echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
                echo "<tr><th>Room</th><th>Guest</th><th>Check In</th><th>Check Out</th><th>Guests</th></tr>";
                foreach ($inhouse as $g) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($g['room_number'] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($g['guest_name'] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($g['check_in_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($g['check_out_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($g['total_guests']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color:gray;'>No inhouse guests</p>";
            }
            
            // Also show all booking statuses
            echo "<p style='font-size:0.8em;'>All booking statuses:</p><pre>";
            $stmt = $bizPdo->query("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status");
            print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "<p style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<hr><h3>4. API Response:</h3>";
    echo "<p>Check API URL: <a href='api/owner-guest-overview.php' target='_blank'>api/owner-guest-overview.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><a href='modules/owner/dashboard.php'>← Back to Owner Dashboard</a></p>";
