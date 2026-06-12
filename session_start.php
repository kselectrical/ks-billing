<?php
// session_start.php - Secure and long-lasting session initialization

$session_dir = __DIR__ . '/sessions';
if (!file_exists($session_dir)) {
    @mkdir($session_dir, 0755, true);
    // Create .htaccess to prevent public access to session files
    @file_put_contents($session_dir . '/.htaccess', "Deny from all\n");
}

if (is_dir($session_dir) && is_writable($session_dir)) {
    ini_set('session.save_path', $session_dir);
}

// Set session lifetime to 30 days (2592000 seconds)
ini_set('session.gc_maxlifetime', 2592000);

if (session_status() === PHP_SESSION_NONE) {
    // Send session cookie that lasts for 30 days
    session_set_cookie_params([
        'lifetime' => 2592000,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
?>
