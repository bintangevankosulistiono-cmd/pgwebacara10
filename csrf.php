<?php
declare(strict_types=1);

// Memulai sesi supaya token keamanan bisa disimpan dan dibaca antarhalaman.
if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
    http_response_code(500);
    exit('Sesi tidak dapat dimulai.');
}

// Membuat token acak untuk formulir, atau menggunakan token sesi yang sudah ada.
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Membandingkan token dari formulir dengan token sesi untuk mencegah CSRF.
function csrf_token_is_valid(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
