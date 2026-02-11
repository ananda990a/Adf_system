# PowerShell FTP Upload Script
$ftpHost = "ftp://ftp.adfsystem.online"
$ftpUser = "adfb2574"
$ftpPass = "mDQmDQtfK3P5xXe74"  # Password dari screenshot
$localFile = "C:\xampp\htdocs\adf_system\developer\login.php"
$remoteFile = "/home/adfb2574/public_html/adf_system/developer/login.php"

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  FTP Upload via PowerShell" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Local File : $localFile" -ForegroundColor Yellow
Write-Host "Remote File: $remoteFile" -ForegroundColor Yellow
Write-Host ""

# Check if local file exists
if (-not (Test-Path $localFile)) {
    Write-Host "ERROR: File lokal tidak ditemukan!" -ForegroundColor Red
    exit 1
}

Write-Host "Uploading..." -ForegroundColor Green

try {
    # Create FTP request
    $ftpUri = "$ftpHost$remoteFile"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $ftpRequest.UseBinary = $true
    $ftpRequest.KeepAlive = $false
    
    # Read file content
    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
    $ftpRequest.ContentLength = $fileContent.Length
    
    # Upload
    $requestStream = $ftpRequest.GetRequestStream()
    $requestStream.Write($fileContent, 0, $fileContent.Length)
    $requestStream.Close()
    
    # Get response
    $response = $ftpRequest.GetResponse()
    
    Write-Host ""
    Write-Host "SUCCESS! File berhasil di-upload!" -ForegroundColor Green
    Write-Host "Status: $($response.StatusDescription)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Sekarang buka: https://adfsystem.online/adf_system/developer/login.php" -ForegroundColor Cyan
    Write-Host "Hard refresh (Ctrl+F5) dan eye icon seharusnya MUNCUL!" -ForegroundColor Cyan
    
    $response.Close()
} catch {
    Write-Host ""
    Write-Host "ERROR: Upload gagal!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}
