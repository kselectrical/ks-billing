<?php
// index.php - Dashboard and Invoice List for KS Electrical and AC Services
require_once 'db_connect.php';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id > 0) {
        $sql_delete = "DELETE FROM `invoices` WHERE `id` = $delete_id";
        $connect->query($sql_delete);
    }
    header("Location: index.php");
    exit;
}

// Handle Search Query
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
    $where_clause = "WHERE `customer_name` LIKE '%$search%' OR `customer_phone` LIKE '%$search%'";
}

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

// Fetch invoices
$sql_invoices = "SELECT * FROM `invoices` $where_clause ORDER BY `id` DESC";
$res_invoices = $connect->query($sql_invoices);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | KS Electrical and AC Services</title>
    <!-- Appending time() forces browser to clear cached CSS instantly -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
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
            <a href="create_invoice.php">Create New Bill</a>
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

    <!-- Title and Action -->
    <div class="section-title-bar">
        <h2>Customer Invoices (ग्राहकों के बिल की सूची)</h2>
        <a href="create_invoice.php" class="btn btn-primary">+ Create New Bill (नया बिल बनाएं)</a>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" class="search-form">
            <input type="text" class="search-input" name="search" placeholder="Search by customer name or phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-secondary">Search</button>
            <?php if (!empty($search)) { ?>
                <a href="index.php" class="btn btn-secondary" style="padding: 10px;">Clear</a>
            <?php } ?>
        </form>
    </div>

    <!-- Invoices List Card -->
    <div class="card">
        <div class="card-header">All Bills</div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">Bill No.</th>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 25%;">Customer Name</th>
                            <th style="width: 15%;">Phone</th>
                            <th style="width: 15%; text-align: right;">Amount</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                            <th style="width: 10%; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($res_invoices->num_rows > 0) {
                            while($inv = $res_invoices->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td><strong>#KS-<?php echo str_pad($inv['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo date('d-m-Y', strtotime($inv['invoice_date'])); ?></td>
                            <td><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($inv['customer_phone']); ?></td>
                            <td style="text-align: right; font-weight: 600;">₹<?php echo number_format($inv['grand_total'], 2); ?></td>
                            <td style="text-align: center;">
                                <span class="badge <?php 
                                    if ($inv['payment_status'] == 'Paid') echo 'badge-success';
                                    elseif ($inv['payment_status'] == 'Unpaid') echo 'badge-danger';
                                    else echo 'badge-warning';
                                ?>">
                                    <?php echo $inv['payment_status']; ?>
                                </span>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="view_invoice.php?id=<?php echo $inv['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">View/Print</a>
                                <a href="index.php?delete=<?php echo $inv['id']; ?>" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;" onclick="return confirm('Are you sure you want to delete this bill?');">Delete</a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                No invoices found. Click "Create New Bill" to add one!
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
