<?php

declare(strict_types=1);

/**
 * Entry point for /database-adminer (Adminer, all drivers, vendored in tools/adminer).
 *
 * This runs outside the Symfony kernel, so it carries its own gate: HTTP basic auth checked against
 * ADMINER_BASIC_AUTH_USER / ADMINER_BASIC_AUTH_HASH (a password_hash() value; single-quote it in
 * .env.local because it contains "$"). With either unset the page stays locked. Adminer's own
 * database login is a second layer. Use it over HTTPS outside of local development, basic auth
 * credentials are otherwise sent in clear text.
 *
 * Generate the hash with: php -r 'echo password_hash("your-password", PASSWORD_DEFAULT), "\n";'
 */

$user = (string) getenv('ADMINER_BASIC_AUTH_USER');
$hash = (string) getenv('ADMINER_BASIC_AUTH_HASH');

if ('' === $user || '' === $hash) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Adminer is disabled: set ADMINER_BASIC_AUTH_USER and ADMINER_BASIC_AUTH_HASH.');
}

$givenUser = (string) ($_SERVER['PHP_AUTH_USER'] ?? '');
$givenPassword = (string) ($_SERVER['PHP_AUTH_PW'] ?? '');

// Not every SAPI fills PHP_AUTH_*; fall back to parsing the Authorization header.
if ('' === $givenUser) {
    $authorization = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (1 === preg_match('~^Basic\s+(\S+)$~i', $authorization, $matches)) {
        $decoded = base64_decode($matches[1], true);
        if (false !== $decoded && str_contains($decoded, ':')) {
            [$givenUser, $givenPassword] = explode(':', $decoded, 2);
        }
    }
}

// Always run both checks so a wrong username costs the same as a wrong password.
$userOk = hash_equals($user, $givenUser);
$passwordOk = password_verify($givenPassword, $hash);

if (!$userOk || !$passwordOk) {
    http_response_code(401);
    header('WWW-Authenticate: Basic realm="Adminer", charset="UTF-8"');
    header('Content-Type: text/plain; charset=utf-8');
    exit('Authentication required.');
}

require dirname(__DIR__, 2) . '/tools/adminer/adminer-6.2.0.php';
