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

// ── Respond to GitHub immediately so it doesn't time out ──
http_response_code(200);
echo "Deploy queued.";

// Flush response to GitHub before doing any heavy work
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    header('Content-Length: ' . ob_get_length());
    ob_end_flush();
    flush();
}

// ── Run deployment in background (GitHub no longer waiting) ──
$repo = '/home/tvwjlayt/buns-on-fire';
$pub  = '/home/tvwjlayt/public_html';
$log  = '/home/tvwjlayt/deploy.log';

file_put_contents($log, date('Y-m-d H:i:s') . " — deploy started\n", FILE_APPEND);

exec("cd {$repo} && git pull origin main >> {$log} 2>&1");

exec("/bin/cp {$repo}/index.html {$pub}/");
exec("/bin/cp {$repo}/contact.html {$pub}/");
exec("/bin/cp -r {$repo}/assets {$pub}/");
exec("/bin/cp -r {$repo}/media_web {$pub}/");

file_put_contents($log, date('Y-m-d H:i:s') . " — deploy done\n", FILE_APPEND);
