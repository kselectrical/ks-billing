<?php
// db_connect.php - Database connection for KS Electrical and AC Services
$localhost = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "ks_billing";

$connect = @new mysqli($localhost, $username, $password, $dbname);
if ($connect->connect_error) {
    die("Connection Failed: " . $connect->connect_error);
}
?>