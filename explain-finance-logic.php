<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjelasan Logika Sistem Keuangan Hotel</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e293b;
            margin-top: 0;
        }
        h2 {
            color: #334155;
            border-bottom: 3px solid #6366f1;
            padding-bottom: 10px;
        }
        .flow-diagram {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            margin: 30px 0;
        }
        .account-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .account-box.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .account-box.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .account-box h3 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
        }
        .account-box .balance {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
        .account-box .desc {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .arrow {
            font-size: 3em;
            color: #6366f1;
            text-align: center;
        }
        .example {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .example.red {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
        .example.green {
            background: #f0fdf4;
            border-left-color: #22c55e;
        }
        .table-compare {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table-compare th {
            background: #6366f1;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .table-compare td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-compare tr:hover {
            background: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .badge.income {
            background: #dcfce7;
            color: #166534;
        }
        .badge.capital {
            background: #fef3c7;
            color: #92400e;
        }
        .badge.expense {
            background: #fee2e2;
            color: #991b1b;
        }
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .step {
            background: #fefce8;
            border-left: 4px solid #eab308;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>📘 Penjelasan Logika Sistem Keuangan Hotel</h1>
        <p style="font-size: 1.1em; color: #64748b;">Mari kita pahami cara sistem memisahkan Pendapatan Hotel dan Modal Owner</p>
    </div>

    <div class="card">
        <h2>🏦 2 Akun Kas Terpisah</h2>
        
        <div class="flow-diagram">
            <div class="account-box green">
                <h3>💵 PETTY CASH</h3>
                <div class="balance">Rp 4.200.000</div>
                <div class="desc">Kas Besar - Operasional</div>
                <hr style="border-color: rgba(255,255,255,0.3); margin: 15px 0;">
                <strong>UNTUK:</strong><br>
                Terima Pendapatan Hotel
            </div>
            
            <div class="arrow">⚡</div>
            
            <div class="account-box orange">
                <h3>🔥 MODAL OWNER</h3>
                <div class="balance">Rp 4.000.000</div>
                <div class="desc">Kas Modal Owner</div>
                <hr style="border-color: rgba(255,255,255,0.3); margin: 15px 0;">
                <strong>UNTUK:</strong><br>
                Bayar Expense Operasional
            </div>
        </div>

        <div class="example green">
            <h4>✅ BENAR: Transaksi Terpisah</h4>
            <p><strong>Scenario 1:</strong> Tamu hotel bayar kamar Rp 500.000</p>
            <ul>
                <li>✅ Input ke Cash Book → Type: <strong>INCOME</strong></li>
                <li>✅ Pilih Cash Account: <strong>Petty Cash (Kas Besar)</strong></li>
                <li>✅ Hasil: Masuk ke <strong>Total Pemasukan</strong> (revenue hotel)</li>
            </ul>

            <p><strong>Scenario 2:</strong> Owner kasih modal Rp 5.000.000</p>
            <ul>
                <li>✅ Input ke Cash Book → Type: <strong>INCOME</strong></li>
                <li>✅ Pilih Cash Account: <strong>Kas Modal Owner</strong></li>
                <li>✅ Hasil: <strong>TIDAK</strong> masuk Total Pemasukan (ini modal, bukan revenue)</li>
            </ul>

            <p><strong>Scenario 3:</strong> Beli sabun Rp 100.000 dari modal owner</p>
            <ul>
                <li>✅ Input ke Cash Book → Type: <strong>EXPENSE</strong></li>
                <li>✅ Pilih Cash Account: <strong>Kas Modal Owner</strong></li>
                <li>✅ Hasil: Keluar dari Modal Owner, <strong>BUKAN</strong> dari Petty Cash</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <h2>📊 Perbedaan Pendapatan vs Modal</h2>
        
        <table class="table-compare">
            <tr>
                <th>Kriteria</th>
                <th>Pendapatan Hotel (Revenue)</th>
                <th>Modal Owner (Capital)</th>
            </tr>
            <tr>
                <td><strong>Sumber</strong></td>
                <td>Tamu hotel, penjualan, service</td>
                <td>Owner kasih modal untuk operasional</td>
            </tr>
            <tr>
                <td><strong>Akun Kas</strong></td>
                <td><span class="badge income">Petty Cash (Kas Besar)</span></td>
                <td><span class="badge capital">Kas Modal Owner</span></td>
            </tr>
            <tr>
                <td><strong>Transaction Type</strong></td>
                <td><span class="badge income">INCOME</span></td>
                <td><span class="badge capital">INCOME</span> (tapi beda akun!)</td>
            </tr>
            <tr>
                <td><strong>Tampil di Laporan</strong></td>
                <td>✅ TOTAL PEMASUKAN</td>
                <td>❌ TIDAK tampil di Total Pemasukan</td>
            </tr>
            <tr>
                <td><strong>Untuk Apa</strong></td>
                <td>Revenue murni dari bisnis hotel</td>
                <td>Dana dari owner untuk bayar expense</td>
            </tr>
            <tr>
                <td><strong>Contoh</strong></td>
                <td>Rp 500.000 (bayar kamar)</td>
                <td>Rp 5.000.000 (modal owner)</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>🔧 Cara Sistem Memisahkan</h2>
        
        <div class="step">
            <h4>Step 1: Input Transaksi di Cash Book</h4>
            <p>Saat input transaksi, ada dropdown <strong>"Cash Account"</strong></p>
            <ul>
                <li>Pilih <strong>Kas Besar (Petty Cash)</strong> → Untuk pendapatan hotel</li>
                <li>Pilih <strong>Kas Modal Owner</strong> → Untuk modal owner atau expense dari modal</li>
            </ul>
        </div>

        <div class="step">
            <h4>Step 2: Sistem Cek cash_account_id</h4>
            <p>Setiap transaksi punya field <code>cash_account_id</code> yang link ke cash_accounts table</p>
            <div class="code-block">
                <pre>
cash_accounts:
ID | business_id | account_name      | account_type  | current_balance
1  | 1           | Kas Besar         | cash          | 4,200,000
2  | 1           | Kas Modal Owner   | owner_capital | 4,000,000

cash_book:
ID | description  | amount    | transaction_type | cash_account_id
1  | Bayar kamar  | 500,000   | income          | 1 (Petty Cash) ✅ MASUK TOTAL PEMASUKAN
2  | Modal owner  | 5,000,000 | income          | 2 (Modal Owner) ❌ TIDAK MASUK TOTAL PEMASUKAN
                </pre>
            </div>
        </div>

        <div class="step">
            <h4>Step 3: Query dengan Exclusion Filter</h4>
            <p>Saat hitung Total Pemasukan, sistem exclude cash_account_id = 2 (Modal Owner)</p>
            <div class="code-block">
                <pre>
SELECT SUM(amount) as total_income
FROM cash_book
WHERE transaction_type = 'income'
  AND (cash_account_id IS NULL OR cash_account_id NOT IN (2))
                </pre>
            </div>
            <p><strong>Hasil:</strong> Hanya Rp 500.000 (pendapatan hotel), TIDAK termasuk Rp 5.000.000 (modal owner)</p>
        </div>
    </div>

    <div class="card">
        <h2>🐛 Troubleshooting: Kenapa Saldo Rp 0?</h2>
        
        <div class="example red">
            <h4>❌ Masalah yang Sering Terjadi:</h4>
            <ol>
                <li><strong>Cash accounts belum dibuat</strong> untuk business ini</li>
                <li><strong>Business ID tidak match</strong> antara config dan database</li>
                <li><strong>Balance belum di-update</strong> (masih 0 di current_balance)</li>
            </ol>
        </div>

        <div class="example">
            <h4>🔍 Cara Debug:</h4>
            <ol>
                <li>Buka: <a href="debug-cash-balance.php" target="_blank"><strong>debug-cash-balance.php</strong></a></li>
                <li>Cek apakah cash_accounts ada untuk business_id Anda</li>
                <li>Cek apakah current_balance punya nilai atau masih 0</li>
                <li>Kalau belum ada, harus create cash accounts dulu</li>
            </ol>
        </div>

        <div class="example green">
            <h4>✅ Solusi:</h4>
            <p>Jika cash_accounts belum ada atau balance-nya 0, kita perlu:</p>
            <ol>
                <li>Create 2 cash accounts (Petty Cash & Modal Owner)</li>
                <li>Set initial balance dari transaksi yang sudah ada</li>
                <li>Link semua transaksi lama ke cash_account_id yang sesuai</li>
            </ol>
            <p><a href="fix-cash-accounts-setup.php" target="_blank" style="background: #6366f1; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 10px;">🔧 Auto-Fix Cash Accounts</a></p>
        </div>
    </div>

    <div class="card">
        <h2>📚 Kesimpulan</h2>
        <div class="example green">
            <h4>✅ Yang Harus Diingat:</h4>
            <ul style="font-size: 1.1em; line-height: 1.8;">
                <li><strong>Pendapatan Hotel</strong> = Uang dari tamu/customer → Masuk <strong>Petty Cash</strong></li>
                <li><strong>Modal Owner</strong> = Uang dari owner untuk operasional → Masuk <strong>Modal Owner Account</strong></li>
                <li><strong>Expense Operasional</strong> = Bayar dari <strong>Modal Owner</strong> (beli sabun, gaji, dll)</li>
                <li><strong>Total Pemasukan</strong> di laporan = Hanya pendapatan hotel (exclude modal owner)</li>
                <li><strong>Sistem pisahkan otomatis</strong> berdasarkan dropdown "Cash Account" saat input</li>
            </ul>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #64748b;">
        <p>Mari kita cek hasil debug di <a href="debug-cash-balance.php" target="_blank">debug-cash-balance.php</a></p>
        <p><a href="modules/reports/daily.php">← Back to Laporan Harian</a> | <a href="index.php">Dashboard</a></p>
    </div>
</body>
</html>
