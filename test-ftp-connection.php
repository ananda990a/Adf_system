<?php
/**
 * Test FTP Connection
 */

$ftp_host = 'ftp.adfsystem.online';
$ftp_user = 'adfb2574';
$ftp_port = 21;

?>
<!DOCTYPE html>
<html>
<head>
    <title>FTP Connection Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .form-group { margin: 15px 0; }
        input { padding: 8px; width: 300px; }
        button { padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>🔧 FTP Connection Test</h2>
    <p>Host: <strong><?= $ftp_host ?></strong></p>
    <p>User: <strong><?= $ftp_user ?></strong></p>
    <p>Port: <strong><?= $ftp_port ?></strong></p>
    
    <form method="POST">
        <div class="form-group">
            <label>Password FTP:</label><br>
            <input type="password" name="ftp_pass" placeholder="Masukkan password FTP" required>
        </div>
        <button type="submit">Test Koneksi</button>
    </form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ftp_pass = $_POST['ftp_pass'] ?? '';
    
    if (empty($ftp_pass)) {
        echo '<p class="error"><strong>❌ Password tidak boleh kosong!</strong></p>';
        exit;
    }
    
    // Connect
    $conn = ftp_connect($ftp_host, $ftp_port, 10);
    if (!$conn) {
        echo "<p class='error'><strong>❌ Tidak bisa connect ke FTP server: $ftp_host</strong></p>";
        exit;
    }
    
    // Login
    if (!ftp_login($conn, $ftp_user, $ftp_pass)) {
        echo "<p class='error'><strong>❌ Username/Password FTP salah!</strong></p>";
        echo "<p>Cek kembali password di cPanel FTP Accounts</p>";
        ftp_close($conn);
        exit;
    }
    
    echo "<p class='success'><strong>✅ Koneksi FTP BERHASIL!</strong></p>";
    echo "<p>Password FTP sudah benar!</p>";
    
    // List files
    echo "<h3>Files di /home/adfb2574/public_html/adf_system/ (20 terbaru):</h3>";
    $files = ftp_nlist($conn, '/home/adfb2574/public_html/adf_system/');
    if ($files && count($files) > 0) {
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 4px;'>";
        foreach (array_slice($files, 0, 20) as $file) {
            echo htmlspecialchars($file) . "\n";
        }
        if (count($files) > 20) {
            echo "... dan " . (count($files) - 20) . " file lainnya";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Direktori kosong atau tidak bisa dibaca</p>";
    }
    
    ftp_close($conn);
}
?>
</body>
</html>
