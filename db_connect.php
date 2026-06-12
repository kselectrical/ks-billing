<?php
// db_connect.php - Smart Database Connection for KS Electrical and AC Services

$localhost = "127.0.0.1";
$username = "root";
$password = ""; // Local XAMPP default
$dbname = "ks_billing";

// Automatically use online InfinityFree credentials if running on the live server
if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    $localhost = "sql301.infinityfree.com";
    $username = "if0_42168126";
    $password = "FpQLnmHHGj";
    $dbname = "if0_42168126_ks_billing";
}

// Attempt connection
$connect = @new mysqli($localhost, $username, $password, $dbname);

if ($connect->connect_error) {
    // Fallback for local MAMP/WAMP with 'root' password
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        $password = "root";
        $connect = @new mysqli($localhost, $username, $password, $dbname);
    }
    
    if ($connect->connect_error) {
        die("<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24; text-align: center;'>" .
            "<h2>Database Connection Failed</h2>" .
            "<p>Could not connect to database: " . $connect->connect_error . "</p>" .
            "</div>");
    }
}
?>