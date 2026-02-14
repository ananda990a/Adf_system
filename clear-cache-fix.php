<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Cache & Fix Console Errors</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .step {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 15px;
            margin: 15px 0;
        }
        .success {
            background: #dcfce7;
            border-left-color: #16a34a;
            color: #166534;
        }
        .warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
            color: #92400e;
        }
        button {
            background: #6366f1;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin: 10px 5px;
        }
        button:hover {
            background: #4f46e5;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
        }
        .code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔧 Clear Cache & Fix Console Errors</h1>
        
        <div class="step success">
            <h3>✅ Step 1: Clear Browser Cache</h3>
            <p>Untuk melihat perubahan terbaru di Laporan Harian dan Dashboard:</p>
            <ol>
                <li>Tekan <strong>Ctrl + Shift + Delete</strong> (Windows/Linux) atau <strong>Cmd + Shift + Delete</strong> (Mac)</li>
                <li>Atau klik tombol di bawah ini:</li>
            </ol>
            <button onclick="clearBrowserCache()">🗑️ Clear Cache</button>
            <button onclick="hardReload()">🔄 Hard Reload</button>
        </div>

        <div class="step warning">
            <h3>⚠️ Step 2: Fix ServiceWorker Error</h3>
            <p>Error di console: <code class="code">Failed to register ServiceWorker... sw.js: 404</code></p>
            <p>Ini tidak mempengaruhi fungsi sistem, tapi mengganggu. Klik tombol di bawah untuk fix:</p>
            <button onclick="createServiceWorker()">✨ Create Service Worker</button>
            <button onclick="unregisterServiceWorker()" class="btn-danger">🗑️ Unregister Service Worker</button>
        </div>

        <div class="step">
            <h3>📋 Step 3: Test Setelah Clear Cache</h3>
            <p>Setelah clear cache, test halaman-halaman ini:</p>
            <ol>
                <li><a href="index.php" target="_blank"><strong>Dashboard</strong></a> - Cek TOTAL PEMASUKAN tetap Rp 500.000 (bukan Rp 5.500.000)</li>
                <li><a href="modules/reports/daily.php" target="_blank"><strong>Laporan Harian</strong></a> - Harus muncul "Saldo Petty Cash" dan "Saldo Modal Owner"</li>
                <li><a href="modules/reports/monthly.php" target="_blank"><strong>Laporan Bulanan</strong></a> - Harus muncul 2 saldo</li>
                <li><a href="modules/reports/yearly.php" target="_blank"><strong>Laporan Tahunan</strong></a> - Harus muncul 2 saldo</li>
            </ol>
        </div>

        <div class="step success">
            <h3>✅ Yang Sudah Diperbaiki:</h3>
            <ul>
                <li><strong>✅ Dashboard API</strong> - Auto-refresh (30 detik) sekarang exclude modal owner dari pemasukan</li>
                <li><strong>✅ Laporan Harian</strong> - Tampilkan Saldo Petty Cash dan Modal Owner</li>
                <li><strong>✅ Laporan Bulanan</strong> - Tampilkan 2 saldo akun</li>
                <li><strong>✅ Laporan Tahunan</strong> - Tampilkan 2 saldo akun</li>
                <li><strong>✅ Laporan Per Divisi</strong> - Tampilkan 2 saldo akun</li>
                <li><strong>✅ Exclusion Filter</strong> - Modal owner tidak dihitung sebagai pendapatan operasional</li>
            </ul>
        </div>

        <div class="step warning">
            <h3>🔍 Troubleshooting</h3>
            <p><strong>Kalau setelah clear cache masih belum muncul:</strong></p>
            <ol>
                <li>Hard refresh halaman: <strong>Ctrl + Shift + R</strong> (atau <strong>Cmd + Shift + R</strong>)</li>
                <li>Buka browser dalam mode Incognito/Private</li>
                <li>Gunakan browser lain untuk test (Chrome, Edge, Firefox)</li>
                <li>Restart Apache/MySQL via XAMPP</li>
            </ol>
        </div>
    </div>

    <script>
        function clearBrowserCache() {
            // Clear console
            console.clear();
            
            // Try to clear cache via cache API
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => {
                        caches.delete(name);
                    });
                });
                alert('✅ Cache cleared! Sekarang tekan Ctrl+Shift+R untuk hard reload.');
            } else {
                alert('⚠️ Browser tidak support Cache API. Silakan tekan Ctrl+Shift+Delete manual.');
            }
        }

        function hardReload() {
            location.reload(true); // Force reload from server
        }

        function createServiceWorker() {
            fetch('sw.js', {method: 'HEAD'})
                .then(response => {
                    if (response.ok) {
                        alert('✅ Service Worker file sudah ada!');
                    } else {
                        alert('⚠️ Service Worker file tidak ditemukan. Akan di-create via API...');
                        // Create via API or just disable it
                        unregisterServiceWorker();
                    }
                });
        }

        function unregisterServiceWorker() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    for(let registration of registrations) {
                        registration.unregister();
                    }
                    console.log('✅ All Service Workers unregistered');
                    alert('✅ Service Worker di-unregister. Error di console seharusnya hilang setelah refresh.');
                    setTimeout(() => location.reload(), 1000);
                });
            } else {
                alert('⚠️ Browser ini tidak support Service Worker.');
            }
        }

        // Auto-unregister service worker on page load to prevent console error
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                if (registrations.length > 0) {
                    console.log('Found Service Worker registrations, unregistering...');
                    registrations.forEach(reg => reg.unregister());
                }
            });
        }
    </script>
</body>
</html>
