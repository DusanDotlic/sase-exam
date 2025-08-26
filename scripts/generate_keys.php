<?php
$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'secrets';
if (!is_dir($dir)) {
    mkdir($dir, 0700, true);
}

$config = [
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

// Create RSA key pair
$res = openssl_pkey_new($config);
if (!$res) {
    die("OpenSSL failed to create key. Is the openssl extension enabled in php.ini?\n");
}

$privKey = '';
openssl_pkey_export($res, $privKey);           // PEM private key
$pubKeyData = openssl_pkey_get_details($res);
$pubKey = $pubKeyData['key'];                   // PEM public key

file_put_contents($dir . DIRECTORY_SEPARATOR . 'jwtRS256.key', $privKey);
file_put_contents($dir . DIRECTORY_SEPARATOR . 'jwtRS256.key.pub', $pubKey);

file_put_contents(
    $dir . DIRECTORY_SEPARATOR . '.htaccess',
    "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n"
);

echo "Keys written to /secrets. Done.\n";
