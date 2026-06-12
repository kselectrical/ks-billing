<?php
// setup.php - Interactive Database Installer for KS Electrical and AC Services

$db_connect_path = __DIR__ . '/db_connect.php';
$message = "";
$show_form = false;

// Default localhost values
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "ks_billing";

// If form is submitted, use the submitted values
if ($_POST) {
    $host = trim($_POST['db_host']);
    $user = trim($_POST['db_user']);
    $pass = trim($_POST['db_pass']);
    $dbname = trim($_POST['db_name']);
    
    // Try connecting with new details
    $conn = @new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        $message = "<div style='color: red; padding: 15px; margin-bottom: 20px; border: 1px solid red; background: #fee2e2; border-radius: 6px;'>
            <strong>Connection Failed:</strong> " . $conn->connect_error . "<br>Please check your database credentials and try again.
        </div>";
        $show_form = true;
    } else {
        // Create database if not exists (only on localhost, online servers usually require creating database first in cPanel)
        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        if (!$conn->select_db($dbname)) {
            $message = "<div style='color: red; padding: 15px; margin-bottom: 20px; border: 1px solid red; background: #fee2e2; border-radius: 6px;'>
                <strong>Selected Database Not Found:</strong> " . $conn->error . "<br>Please ensure you have created the database inside your hosting control panel (MySQL Databases).
            </div>";
            $show_form = true;
        } else {
            // Re-create tables
            $conn->query("DROP TABLE IF EXISTS `invoice_items` ");
            $conn->query("DROP TABLE IF EXISTS `invoices` ");

            // Create invoices table
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
            $conn->query($create_invoices);

            // Create invoice_items table
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
            $conn->query($create_items);

            // Insert sample invoice data
            $insert_invoice = "INSERT INTO `invoices` 
                (`customer_name`, `customer_phone`, `customer_address`, `invoice_date`, `sub_total`, `gst_rate`, `gst_amount`, `discount`, `grand_total`, `received`, `balance`, `payment_status`, `payment_method`) 
                VALUES 
                ('Customer Name', '918860989289', 'c-1419 6th Avenue', '2026-06-12', 2450.00, 0.00, 0.00, 0.00, 2450.00, 2400.00, 50.00, 'Partial', 'Cash');";

            if ($conn->query($insert_invoice)) {
                $invoice_id = $conn->insert_id;
                $conn->query("INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Cooler motor (1 year warranty)', '', 1, 1800.00, 1800.00);");
                $conn->query("INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Labour charge', '', 1, 350.00, 350.00);");
                $conn->query("INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Cooler Fan Blade', '', 1, 300.00, 300.00);");
            }

            // Write connection file
            $connect_code = "<?php
// db_connect.php - Database connection for KS Electrical and AC Services
\$localhost = \"$host\";
\$username = \"$user\";
\$password = \"$pass\";
\$dbname = \"$dbname\";

\$connect = @new mysqli(\$localhost, \$username, \$password, \$dbname);
if (\$connect->connect_error) {
    die(\"Connection Failed: \" . \$connect->connect_error);
}
?>";
            file_put_contents($db_connect_path, $connect_code);

            // Success Screen
            echo "<div style='font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 80px auto; padding: 40px; border-radius: 12px; background-color: #d4edda; color: #155724; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
            echo "<div style='font-size: 50px; margin-bottom: 20px;'>✅</div>";
            echo "<h1 style='margin-top: 0; font-weight: 600;'>डेटाबेस सेटअप सफल! / Setup Successful!</h1>";
            echo "<p style='font-size: 16px; line-height: 1.6;'>बिलिंग डेटाबेस सफलतापूर्वक कॉन्फ़िगर हो गया है।</p>";
            echo "<p style='font-size: 14px; color: #155724;'>Database configuration written and tables created.</p>";
            echo "<br><br>";
            echo "<a href='index.php' style='display: inline-block; padding: 14px 28px; background-color: #28a745; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>डैशबोर्ड पर जाएँ / Go to Dashboard</a>";
            echo "</div>";
            exit;
        }
    }
} else {
    // Try connecting to default localhost
    $conn = @new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        $show_form = true;
    } else {
        // Localhost connects fine, run silent setup
        $conn->query("DROP TABLE IF EXISTS `invoice_items` ");
        $conn->query("DROP TABLE IF EXISTS `invoices` ");
        
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
        $conn->query($create_invoices);

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
        $conn->query($create_items);

        $insert_invoice = "INSERT INTO `invoices` 
            (`customer_name`, `customer_phone`, `customer_address`, `invoice_date`, `sub_total`, `gst_rate`, `gst_amount`, `discount`, `grand_total`, `received`, `balance`, `payment_status`, `payment_method`) 
            VALUES 
            ('Customer Name', '918860989289', 'c-1419 6th Avenue', '2026-06-12', 2450.00, 0.00, 0.00, 0.00, 2450.00, 2400.00, 50.00, 'Partial', 'Cash');";

        if ($conn->query($insert_invoice)) {
            $invoice_id = $conn->insert_id;
            $conn->query("INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Cooler motor (1 year warranty)', '', 1, 1800.00, 1800.00);");
            $conn->query("INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Labour charge', '', 1, 350.00, 350.00);");
            $conn->query("INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) VALUES ($invoice_id, 'Cooler Fan Blade', '', 1, 300.00, 300.00);");
        }

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup | KS Electrical & AC Services</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #1e293b; padding: 20px; }
        .setup-box { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        h2 { margin-top: 0; color: #0284c7; font-size: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 15px; }
        label { font-size: 13px; font-weight: 600; color: #475569; }
        .form-control { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; }
        .form-control:focus { border-color: #0284c7; }
        .btn { padding: 10px 20px; background: #0284c7; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 14px; }
        .btn:hover { background: #0369a1; }
        .tip-box { background: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; padding: 10px 15px; border-radius: 6px; font-size: 12px; margin-bottom: 20px; line-height: 1.5; }
    </style>
</head>
<body>

<div class="setup-box">
    <h2>MySQL Database Setup</h2>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Please enter your database server connection details below to initialize the system.</p>
    
    <?php echo $message; ?>
    
    <div class="tip-box">
        💡 <strong>cPanel Info:</strong> Log into InfinityFree, go to <strong>MySQL Databases</strong>, create a database, and enter the Hostname, Username, and Password details shown there.
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="db_host">MySQL Hostname (e.g. sql300.infinityfree.com)</label>
            <input type="text" class="form-control" id="db_host" name="db_host" required placeholder="sqlXXX.infinityfree.com" value="<?php echo htmlspecialchars($host); ?>">
        </div>
        <div class="form-group">
            <label for="db_user">MySQL Username (e.g. epiz_xxxxxxxx)</label>
            <input type="text" class="form-control" id="db_user" name="db_user" required placeholder="epiz_xxxxxxxx" value="<?php echo htmlspecialchars($user); ?>">
        </div>
        <div class="form-group">
            <label for="db_pass">MySQL Password</label>
            <input type="password" class="form-control" id="db_pass" name="db_pass" placeholder="Enter DB Password" value="<?php echo htmlspecialchars($pass); ?>">
        </div>
        <div class="form-group">
            <label for="db_name">MySQL Database Name</label>
            <input type="text" class="form-control" id="db_name" name="db_name" required placeholder="epiz_xxxxxxxx_ks_billing" value="<?php echo htmlspecialchars($dbname); ?>">
        </div>
        <button type="submit" class="btn">Connect & Setup Tables</button>
    </form>
</div>

</body>
</html>
