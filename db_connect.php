<?php
// db_connect.php - Database connection for KS Electrical and AC Services

$localhost = "127.0.0.1";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "ks_billing"; // Clean, dedicated database

// Try connecting to the server
$connect = @new mysqli($localhost, $username, $password);

if ($connect->connect_error) {
    // Try with password 'root' in case they are on MAMP or custom setup
    $password = "root";
    $connect = @new mysqli($localhost, $username, $password);
    
    if ($connect->connect_error) {
        die("<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24; text-align: center;'>" .
            "<h2>Database Server Connection Failed</h2>" .
            "<p>Could not connect to MySQL server on 127.0.0.1. Please make sure XAMPP (MySQL) is running.</p>" .
            "</div>");
    }
}

// Attempt to select the database
$db_selected = $connect->select_db($dbname);

// If the database doesn't exist and we're not on setup.php, redirect to it
if (!$db_selected && php_sapi_name() !== 'cli' && basename($_SERVER['PHP_SELF']) !== 'setup.php') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_dir = rtrim($base_dir, '/');
    $setup_url = $protocol . $host . $base_dir . '/setup.php';
    
    header("Location: $setup_url");
    exit;
}
?>
