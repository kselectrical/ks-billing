<?php 	

$localhost = "localhost";
$username = "root";
$password = "root";
$dbname = "store";
$store_url = "http://localhost/php-inventory-management-system/";
// db connection
$connect = new mysqli($localhost, $username, $password, $dbname);
// check connection
if($connect->connect_error) {
  // If running in browser, offer to redirect to setup.php
  if (php_sapi_name() !== 'cli' && file_exists(dirname(__DIR__) . '/setup.php')) {
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $host = $_SERVER['HTTP_HOST'];
      $base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
      $base_dir = preg_replace('/(\/php_action|\/custom|\/includes)$/', '', rtrim($base_dir, '/'));
      $setup_url = $protocol . $host . ($base_dir === '' ? '/' : $base_dir . '/') . 'setup.php';
      
      echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #ffc107; border-radius: 5px; background-color: #fff3cd; color: #856404; text-align: center;'>";
      echo "<h2>System Database Setup Required</h2>";
      echo "<p>It looks like the database is not initialized yet, or your connection credentials need configuration.</p>";
      echo "<p>Connection error: <b>" . htmlspecialchars($connect->connect_error) . "</b></p>";
      echo "<br>";
      echo "<a href='" . htmlspecialchars($setup_url) . "' style='display: inline-block; padding: 12px 24px; background-color: #ffc107; color: #212529; text-decoration: none; border-radius: 5px; font-weight: bold;'>Run Automatic Database Installer</a>";
      echo "</div>";
      exit;
  }
  die("Connection Failed : " . $connect->connect_error);
} else {
  // echo "Successfully connected";
}

?>