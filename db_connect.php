<?php
// db_connect.php - Smart & Robust Database Connection for KS Electrical and AC Services

// Disable mysqli throwing exceptions (essential for PHP 8.1+ compatibility)
@mysqli_report(MYSQLI_REPORT_OFF);

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

try {
    // Attempt connection
    $connect = @new mysqli($localhost, $username, $password, $dbname);
} catch (Exception $e) {
    $connect = null;
}

if (!$connect || $connect->connect_error) {
    // Fallback for local MAMP/WAMP with 'root' password
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        try {
            $password = "root";
            $connect = @new mysqli($localhost, $username, $password, $dbname);
        } catch (Exception $e) {
            $connect = null;
        }
    }
    
    if (!$connect || $connect->connect_error) {
        $error_msg = $connect ? $connect->connect_error : "MySQL connection failed (DNS cache propagating or database server offline)";
        
        die("<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24; text-align: center;'>" .
            "<h2>Database Connection Failed (डेटाबेस कनेक्शन विफल)</h2>" .
            "<p>Could not connect to database server. Please ensure MySQL is running or your database credentials are active.</p>" .
            "<p>Error details: <b>" . htmlspecialchars($error_msg) . "</b></p>" .
            "<p style='font-size: 12px; color: #721c24; margin-top: 15px;'>Note: If you just created the account, it might take a few minutes for the database server to activate.</p>" .
            "</div>");
    }
}
?>