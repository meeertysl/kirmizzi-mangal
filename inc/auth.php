<?php
require_once __DIR__ . '/db.php';

const KM_DEFAULT_PASSWORD = 'admin123';

function km_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 7,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_name('km_session');
    session_start();
}

function km_is_admin()
{
    km_session_start();
    return !empty($_SESSION['admin']);
}

function km_check_password($password)
{
    $db = km_read_db();
    $stored = $db['settings']['adminPassword'] ?? null;
    if (!$stored) {
        return hash_equals(KM_DEFAULT_PASSWORD, (string) $password);
    }
    return password_verify((string) $password, $stored);
}

function km_login($password)
{
    km_session_start();
    if (!km_check_password($password)) {
        usleep(500000); // kaba deneme yavaşlatma
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    return true;
}

function km_logout()
{
    km_session_start();
    $_SESSION = [];
    session_destroy();
}

function km_csrf_token()
{
    km_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function km_csrf_check()
{
    km_session_start();
    $token = $_POST['csrf'] ?? '';
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function km_csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(km_csrf_token()) . '">';
}
