<?php
// auth_check.php - Restrict access to logged-in users
require_once 'session_start.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
