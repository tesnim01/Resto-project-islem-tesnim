@echo off
REM Generate JWT Keys using OpenSSL (Windows Batch Script)
REM This script uses OpenSSL to generate JWT keys for Lexik JWT Bundle

set JWT_DIR=config\jwt
set PRIVATE_KEY=%JWT_DIR%\private.pem
set PUBLIC_KEY=%JWT_DIR%\public.pem
set PASSPHRASE=d897c2d92169305ee287019beac25d0cb28dbd3bc72a95527c0c45d4fee2d0e4

REM Create directory if it doesn't exist
if not exist "%JWT_DIR%" mkdir "%JWT_DIR%"

echo Generating JWT keys using OpenSSL...
echo.

REM Try different OpenSSL locations
set OPENSSL_PATH=

REM Check if openssl is in PATH
where openssl >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    set OPENSSL_PATH=openssl
    goto :generate
)

REM Check XAMPP OpenSSL
if exist "C:\xampp\apache\bin\openssl.exe" (
    set OPENSSL_PATH=C:\xampp\apache\bin\openssl.exe
    goto :generate
)

REM Check if OpenSSL is in common locations
if exist "C:\OpenSSL-Win64\bin\openssl.exe" (
    set OPENSSL_PATH=C:\OpenSSL-Win64\bin\openssl.exe
    goto :generate
)

echo ERROR: OpenSSL not found!
echo.
echo Please install OpenSSL or ensure it's in your PATH.
echo Options:
echo 1. Download OpenSSL from: https://slproweb.com/products/Win32OpenSSL.html
echo 2. Or use Git Bash which includes OpenSSL
echo 3. Or use the online tool at: https://8gwifi.org/jwkconvertfunctions.jsp
echo.
pause
exit /b 1

:generate
echo Using OpenSSL at: %OPENSSL_PATH%
echo.

REM Generate private key
echo Generating private key...
%OPENSSL_PATH% genrsa -out "%PRIVATE_KEY%" -passout pass:%PASSPHRASE% 2048
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to generate private key
    pause
    exit /b 1
)

REM Generate public key from private key
echo Generating public key...
%OPENSSL_PATH% rsa -in "%PRIVATE_KEY%" -pubout -out "%PUBLIC_KEY%" -passin pass:%PASSPHRASE%
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to generate public key
    pause
    exit /b 1
)

echo.
echo ========================================
echo JWT Keys Generated Successfully!
echo ========================================
echo Private Key: %PRIVATE_KEY%
echo Public Key:  %PUBLIC_KEY%
echo.
echo Passphrase: %PASSPHRASE%
echo.
echo You can now use JWT authentication!
echo.
pause
