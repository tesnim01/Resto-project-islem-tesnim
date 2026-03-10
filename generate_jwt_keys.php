<?php
/**
 * Generate JWT Keys for Lexik JWT Authentication Bundle
 * 
 * Run this script to generate the private and public keys needed for JWT authentication.
 * Usage: php generate_jwt_keys.php
 */

$jwtDir = __DIR__ . '/config/jwt';
$privateKeyPath = $jwtDir . '/private.pem';
$publicKeyPath = $jwtDir . '/public.pem';
$passPhrase = 'd897c2d92169305ee287019beac25d0cb28dbd3bc72a95527c0c45d4fee2d0e4'; // From .env

// Create directory if it doesn't exist
if (!is_dir($jwtDir)) {
    mkdir($jwtDir, 0755, true);
    echo "Created directory: $jwtDir\n";
}

// Check if OpenSSL extension is available
if (!extension_loaded('openssl')) {
    die("ERROR: OpenSSL extension is not loaded. Please enable it in php.ini\n");
}

echo "Generating JWT keys...\n";

// Generate private key
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

// Generate key pair
$resource = openssl_pkey_new($config);
if ($resource === false) {
    die("ERROR: Failed to generate key pair. " . openssl_error_string() . "\n");
}

// Export private key
openssl_pkey_export($resource, $privateKey, $passPhrase);

// Export public key
$publicKeyDetails = openssl_pkey_get_details($resource);
if ($publicKeyDetails === false) {
    die("ERROR: Failed to get public key details.\n");
}
$publicKey = $publicKeyDetails["key"];

// Write private key to file
if (file_put_contents($privateKeyPath, $privateKey) === false) {
    die("ERROR: Failed to write private key to $privateKeyPath\n");
}
chmod($privateKeyPath, 0600); // Read/write for owner only
echo "✓ Private key generated: $privateKeyPath\n";

// Write public key to file
if (file_put_contents($publicKeyPath, $publicKey) === false) {
    die("ERROR: Failed to write public key to $publicKeyPath\n");
}
chmod($publicKeyPath, 0644); // Read for all, write for owner
echo "✓ Public key generated: $publicKeyPath\n";

echo "\n✅ JWT keys generated successfully!\n";
echo "Passphrase: $passPhrase\n";
echo "\nYou can now use JWT authentication in your application.\n";
