<?php
define('APP_ACCESS', true);
require_once 'config/config.php';

echo "<pre>";
echo "SESSION role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "SESSION user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "SESSION username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "</pre>";
