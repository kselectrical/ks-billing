<?php
// index.php - Dashboard and Invoice List for KS Electrical and AC Services
require_once 'auth_check.php';
require_once 'db_connect.php';

// Fetch metrics
$query_total = "SELECT SUM(`grand_total`) AS total_billed, COUNT(`id`) AS total_bills FROM `invoices`";
$res_total = $connect->query($query_total)->fetch_assoc();
$total_billed = floatval($res_total['total_billed']);
$total_bills = intval($res_total['total_bills']);

$query_paid = "SELECT SUM(`grand_total`) AS total_paid FROM `invoices` WHERE `payment_status` = 'Paid'";
$res_paid = $connect->query($query_paid)->fetch_assoc();
$total_paid = floatval($res_paid['total_paid']);

$query_pending = "SELECT SUM(`grand_total`) AS total_pending FROM `invoices` WHERE `payment_status` = 'Unpaid'";
$res_pending = $connect->query($query_pending)->fetch_assoc();
$total_pending = floatval($res_pending['total_pending']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | KS Electrical and AC Services</title>
    <!-- Appending time() forces browser to clear cached CSS instantly -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .dashboard-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        .action-link {
            text-decoration: none;
            color: inherit;
        }
        .action-card {
            background-color: var(--card-bg);
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 35px 25px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        .card-primary-hover:hover {
            border-color: var(--primary) !important;
            background-color: rgba(2, 132, 199, 0.02);
        }
        .card-secondary-hover:hover {
            border-color: var(--secondary) !important;
            background-color: rgba(13, 148, 136, 0.02);
        }
        .action-icon {
            font-size: 40px;
            display: block;
            margin-bottom: 15px;
        }
        .action-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }
        .action-card p {
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<header>
    <div class="container header-container">
        <div class="logo-section">
            <h1>KS Electrical and AC Services</h1>
            <span>Kaushindra Singh • Billing System</span>
        </div>
        <nav>
            <a href="index.php" class="active">Dashboard</a>
            <a href="history.php">Bill History</a>
            <a href="create_invoice.php">Create New Bill</a>
            <a href="logout.php" style="color: var(--danger); margin-left: 10px;">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <!-- Metrics Cards Row -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-info">
                <h3>Total Billed (कुल बिल राशि)</h3>
                <p>₹<?php echo number_format($total_billed, 2); ?></p>
            </div>
            <div class="metric-icon">📊</div>
        </div>
        <div class="metric-card">
            <div class="metric-info">
                <h3>Total Paid (प्राप्त राशि)</h3>
                <p style="color: var(--success);">₹<?php echo number_format($total_paid, 2); ?></p>
            </div>
            <div class="metric-icon">💰</div>
        </div>
        <div class="metric-card">
            <div class="metric-info">
                <h3>Pending / Unpaid (बकाया राशि)</h3>
                <p style="color: var(--danger);">₹<?php echo number_format($total_pending, 2); ?></p>
            </div>
            <div class="metric-icon">⏳</div>
        </div>
        <div class="metric-card">
            <div class="metric-info">
                <h3>Total Invoices (कुल बिल संख्या)</h3>
                <p><?php echo $total_bills; ?></p>
            </div>
            <div class="metric-icon">📄</div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="section-title-bar">
        <h2>Quick Actions (त्वरित विकल्प)</h2>
    </div>

    <div class="dashboard-actions-grid">
        <a href="create_invoice.php" class="action-link">
            <div class="action-card card-primary-hover">
                <span class="action-icon">➕</span>
                <h3>Create New Bill</h3>
                <p>कस्टमर के लिए नया बिल बनाएं और सहेजें</p>
            </div>
        </a>
        <a href="history.php" class="action-link">
            <div class="action-card card-secondary-hover">
                <span class="action-icon">📜</span>
                <h3>Bill History (बिल इतिहास)</h3>
                <p>तारीख, महीने और साल के अनुसार सभी पुराने बिल खोजें और प्रबंधित करें</p>
            </div>
        </a>
    </div>
</div>

</body>
</html>
