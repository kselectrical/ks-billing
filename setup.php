<?php
// setup.php - Database initialization for KS Electrical and AC Services

$localhost = "127.0.0.1";
$username = "root";
$password = ""; // Try empty first
$dbname = "ks_billing";

// Try connecting
$conn = @new mysqli($localhost, $username, $password);
if ($conn->connect_error) {
    // Try with 'root' password
    $password = "root";
    $conn = @new mysqli($localhost, $username, $password);
    if ($conn->connect_error) {
        die("<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24; text-align: center;'>" .
            "<h2>Database Connection Failed</h2>" .
            "<p>Could not connect to MySQL server. Please make sure XAMPP (MySQL) is running.</p>" .
            "</div>");
    }
}

// Create database if not exists
$create_db = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_general_ci";
if (!$conn->query($create_db)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Drop tables if they exist to apply new schema
$conn->query("DROP TABLE IF EXISTS `invoice_items` ");
$conn->query("DROP TABLE IF EXISTS `invoices` ");

// 2. Create invoices table
$create_invoices = "CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_address` TEXT,
    `invoice_date` DATE NOT NULL,
    `sub_total` DECIMAL(10,2) NOT NULL,
    `gst_rate` DECIMAL(5,2) DEFAULT 18.00,
    `gst_amount` DECIMAL(10,2) NOT NULL,
    `discount` DECIMAL(10,2) DEFAULT 0.00,
    `grand_total` DECIMAL(10,2) NOT NULL,
    `received` DECIMAL(10,2) DEFAULT 0.00,
    `balance` DECIMAL(10,2) DEFAULT 0.00,
    `payment_status` VARCHAR(20) DEFAULT 'Paid',
    `payment_method` VARCHAR(50) DEFAULT 'Cash',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if (!$conn->query($create_invoices)) {
    die("Error creating invoices table: " . $conn->error);
}

// 3. Create invoice_items table
$create_items = "CREATE TABLE IF NOT EXISTS `invoice_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `hsn_sac` VARCHAR(50) DEFAULT '',
    `quantity` INT NOT NULL DEFAULT 1,
    `rate` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if (!$conn->query($create_items)) {
    die("Error creating invoice_items table: " . $conn->error);
}

// Insert dummy invoice data matching the user's template
$insert_invoice = "INSERT INTO `invoices` 
    (`customer_name`, `customer_phone`, `customer_address`, `invoice_date`, `sub_total`, `gst_rate`, `gst_amount`, `discount`, `grand_total`, `received`, `balance`, `payment_status`, `payment_method`) 
    VALUES 
    ('Customer Name', '918860989289', 'c-1419 6th Avenue', '2026-06-12', 2450.00, 0.00, 0.00, 0.00, 2450.00, 2400.00, 50.00, 'Partial', 'Cash');";

if ($conn->query($insert_invoice)) {
    $invoice_id = $conn->insert_id;
    
    $insert_item1 = "INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Cooler motor (1 year warranty)', '', 1, 1800.00, 1800.00);";
    $insert_item2 = "INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Labour charge', '', 1, 350.00, 350.00);";
    $insert_item3 = "INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Cooler Fan Blade', '', 1, 300.00, 300.00);";
    
    $conn->query($insert_item1);
    $conn->query($insert_item2);
    $conn->query($insert_item3);
}

// Copy db_connect config
$db_connect_path = __DIR__ . '/db_connect.php';
$connect_code = "<?php
// db_connect.php - Database connection for KS Electrical and AC Services
\$localhost = \"$localhost\";
\$username = \"$username\";
\$password = \"$password\";
\$dbname = \"$dbname\";

\$connect = @new mysqli(\$localhost, \$username, \$password, \$dbname);
if (\$connect->connect_error) {
    die(\"Connection Failed: \" . \$connect->connect_error);
}
?>";
file_put_contents($db_connect_path, $connect_code);

echo "<div style='font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 80px auto; padding: 40px; border: none; border-radius: 12px; background-color: #d4edda; color: #155724; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
echo "<div style='font-size: 50px; margin-bottom: 20px;'>✅</div>";
echo "<h1 style='margin-top: 0; font-weight: 600;'>डेटाबेस सेटअप सफल! / Setup Successful!</h1>";
echo "<p style='font-size: 16px; line-height: 1.6;'>KS Electrical & AC Services का नया बिलिंग डेटाबेस सेटअप हो गया है।</p>";
echo "<br><br>";
echo "<a href='index.php' style='display: inline-block; padding: 14px 28px; background-color: #28a745; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>डैशबोर्ड पर जाएँ / Go to Dashboard</a>";
echo "</div>";
?>
