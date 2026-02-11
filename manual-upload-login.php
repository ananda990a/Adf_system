<?php
/**
 * Manual FTP Upload - developer/login.php
 */

// FTP Credentials
$ftp_host = 'ftp.adfsystem.online';
$ftp_user = 'adfb2574';
$ftp_pass = ''; // mbQmbQtfK3P5xXe74
$ftp_port = 21;

if (empty($ftp_pass)) {
    die('<h2>❌ Isi password FTP di line 11!</h2>');
}

// File local dan remote
$local_file = __DIR__ . '/developer/login.php';
$remote_file = '/home/adfb2574/public_html/adf_system/developer/login.php';

echo "<h2>📤 Manual FTP Upload</h2>";
echo "<p>Local: <strong>$local_file</strong></p>";
echo "<p>Remote: <strong>$remote_file</strong></p>";

// Check local file exists
if (!file_exists($local_file)) {
    die("<p style='color:red;'>❌ File lokal tidak ditemukan!</p>");
}

// Connect
$conn = ftp_connect($ftp_host, $ftp_port, 10);
if (!$conn) {
    die("<p style='color:red;'>❌ Tidak bisa connect ke FTP</p>");
}

// Login
if (!ftp_login($conn, $ftp_user, $ftp_pass)) {
    die("<p style='color:red;'>❌ FTP Login gagal!</p>");
}

// Set passive mode
ftp_pasv($conn, true);

// Upload
echo "<p>⏳ Uploading...</p>";
if (ftp_put($conn, $remote_file, $local_file, FTP_BINARY)) {
    echo "<p style='color:green; font-size:20px;'><strong>✅ BERHASIL UPLOAD!</strong></p>";
    echo "<p>File <strong>developer/login.php</strong> sudah ter-update di hosting!</p>";
    echo "<p><a href='https://adfsystem.online/adf_system/developer/login.php' target='_blank'>Buka Login Page →</a></p>";
} else {
    echo "<p style='color:red;'>❌ Upload gagal!</p>";
    echo "<p>Error: " . error_get_last()['message'] . "</p>";
}

ftp_close($conn);
?>
