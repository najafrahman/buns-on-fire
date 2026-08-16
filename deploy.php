<?php
// Webhook secret — must match what you set in GitHub
define('SECRET', 'b65fe5699699491f002d4fadfd3c27647b924f64a2097fbb4837c16c3213572b');

// Verify GitHub signature
$payload  = file_get_contents('php://input');
$sig      = 'sha256=' . hash_hmac('sha256', $payload, SECRET);
$received = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!hash_equals($sig, $received)) {
    http_response_code(403);
    die('Forbidden');
}

// Pull latest from GitHub
$repo = '/home/tvwjlayt/buns-on-fire';
$pub  = '/home/tvwjlayt/public_html';

exec("cd {$repo} && git pull origin main 2>&1", $out);

// Copy files to public_html
exec("/bin/cp {$repo}/index.html {$pub}/");
exec("/bin/cp {$repo}/contact.html {$pub}/");
exec("/bin/cp -r {$repo}/assets {$pub}/");
exec("/bin/cp -r {$repo}/media_web {$pub}/");

http_response_code(200);
echo "Deployed: " . implode("\n", $out);
