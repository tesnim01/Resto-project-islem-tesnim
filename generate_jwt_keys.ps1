# Generate JWT Keys using OpenSSL (PowerShell Script)
# This script uses OpenSSL to generate JWT keys for Lexik JWT Bundle

$JWT_DIR = "config\jwt"
$PRIVATE_KEY = "$JWT_DIR\private.pem"
$PUBLIC_KEY = "$JWT_DIR\public.pem"
$PASSPHRASE = "d897c2d92169305ee287019beac25d0cb28dbd3bc72a95527c0c45d4fee2d0e4"

# Create directory if it doesn't exist
if (-not (Test-Path $JWT_DIR)) {
    New-Item -ItemType Directory -Path $JWT_DIR | Out-Null
    Write-Host "Created directory: $JWT_DIR" -ForegroundColor Green
}

# Find OpenSSL
$OPENSSL_PATH = $null

# Check if openssl is in PATH
$opensslInPath = Get-Command openssl -ErrorAction SilentlyContinue
if ($opensslInPath) {
    $OPENSSL_PATH = "openssl"
    Write-Host "Found OpenSSL in PATH" -ForegroundColor Green
}
# Check XAMPP OpenSSL
elseif (Test-Path "C:\xampp\apache\bin\openssl.exe") {
    $OPENSSL_PATH = "C:\xampp\apache\bin\openssl.exe"
    Write-Host "Found OpenSSL in XAMPP" -ForegroundColor Green
}
# Check Git Bash OpenSSL
elseif (Test-Path "C:\Program Files\Git\usr\bin\openssl.exe") {
    $OPENSSL_PATH = "C:\Program Files\Git\usr\bin\openssl.exe"
    Write-Host "Found OpenSSL in Git Bash" -ForegroundColor Green
}
else {
    Write-Host "ERROR: OpenSSL not found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install OpenSSL or ensure it's in your PATH." -ForegroundColor Yellow
    Write-Host "Options:" -ForegroundColor Yellow
    Write-Host "1. Download OpenSSL from: https://slproweb.com/products/Win32OpenSSL.html" -ForegroundColor Yellow
    Write-Host "2. Or use Git Bash which includes OpenSSL" -ForegroundColor Yellow
    Write-Host "3. Or use the online tool at: https://8gwifi.org/jwkconvertfunctions.jsp" -ForegroundColor Yellow
    exit 1
}

Write-Host "Using OpenSSL at: $OPENSSL_PATH" -ForegroundColor Cyan
Write-Host ""

# Generate private key
Write-Host "Generating private key..." -ForegroundColor Cyan
& $OPENSSL_PATH genrsa -out $PRIVATE_KEY -passout "pass:$PASSPHRASE" 2048
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to generate private key" -ForegroundColor Red
    exit 1
}

# Generate public key from private key
Write-Host "Generating public key..." -ForegroundColor Cyan
& $OPENSSL_PATH rsa -in $PRIVATE_KEY -pubout -out $PUBLIC_KEY -passin "pass:$PASSPHRASE"
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to generate public key" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "JWT Keys Generated Successfully!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Private Key: $PRIVATE_KEY" -ForegroundColor White
Write-Host "Public Key:  $PUBLIC_KEY" -ForegroundColor White
Write-Host ""
Write-Host "Passphrase: $PASSPHRASE" -ForegroundColor White
Write-Host ""
Write-Host "You can now use JWT authentication!" -ForegroundColor Green
Write-Host ""
