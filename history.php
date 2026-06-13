<?php
// history.php - Bill History with Date, Month, and Year Filters for KS Electrical and AC Services
require_once 'auth_check.php';
require_once 'db_connect.php';

// Handle Delete Action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id > 0) {
        $sql_delete = "DELETE FROM `invoices` WHERE `id` = $delete_id";
        $connect->query($sql_delete);
    }
    header("Location: history.php");
    exit;
}

// Get filter inputs
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$filter_date = isset($_GET['filter_date']) ? mysqli_real_escape_string($connect, $_GET['filter_date']) : '';
$filter_month = isset($_GET['filter_month']) ? mysqli_real_escape_string($connect, $_GET['filter_month']) : '';
$filter_year = isset($_GET['filter_year']) ? mysqli_real_escape_string($connect, $_GET['filter_year']) : '';

// Build SQL where clause dynamically
$where_clauses = [];

if (!empty($search)) {
    $where_clauses[] = "(`customer_name` LIKE '%$search%' OR `customer_phone` LIKE '%$search%')";
}
if (!empty($filter_date)) {
    $where_clauses[] = "DATE(`invoice_date`) = '$filter_date'";
}
if (!empty($filter_month)) {
    $where_clauses[] = "MONTH(`invoice_date`) = '" . intval($filter_month) . "'";
}
if (!empty($filter_year)) {
    $where_clauses[] = "YEAR(`invoice_date`) = '" . intval($filter_year) . "'";
}

$where_clause = "";
if (count($where_clauses) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where_clauses);
}

// Fetch filtered invoices
$sql_invoices = "SELECT * FROM `invoices` $where_clause ORDER BY `id` DESC";
$res_invoices = $connect->query($sql_invoices);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill History | KS Electrical and AC Services</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
    <div class="container header-container">
        <div class="logo-section">
            <h1 style="display: flex; align-items: center; gap: 8px; font-size: 18px;">
                <?php if (file_exists('logo.png')) { ?>
                    <img src="logo.png" alt="Logo" style="height: 28px; width: auto; object-fit: contain;">
                <?php } ?>
                KS Electrical and AC Services
            </h1>
            <span>Kaushindra Singh • Billing System</span>
        </div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="history.php" class="active">Bill History</a>
            <a href="create_invoice.php">Create New Bill</a>
            <a href="logout.php" style="color: var(--danger); margin-left: 10px;">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <!-- Title and Action -->
    <div class="section-title-bar">
        <h2>Bill History & Filters (बिलों का इतिहास)</h2>
        <a href="create_invoice.php" class="btn btn-primary">+ Create New Bill</a>
    </div>

    <!-- Filters Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">Filter & Search Bills (बिल फ़िल्टर करें)</div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: end;">
                    <div class="form-group">
                        <label>Search Customer Name/Phone</label>
                        <input type="text" class="form-control" name="search" placeholder="Search customer..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <label>Specific Date (तारीख)</label>
                        <input type="date" class="form-control" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    <div class="form-group">
                        <label>Month (महीना)</label>
                        <select class="form-control" name="filter_month">
                            <option value="">-- All Months --</option>
                            <?php
                            $months = [
                                1 => "January (जनवरी)", 2 => "February (फ़रवरी)", 3 => "March (मार्च)",
                                4 => "April (अप्रैल)", 5 => "May (मई)", 6 => "June (जून)",
                                7 => "July (जुलाई)", 8 => "August (अगस्त)", 9 => "September (सितंबर)",
                                10 => "October (अक्टूबर)", 11 => "November (नवंबर)", 12 => "December (दिसंबर)"
                            ];
                            foreach ($months as $num => $name) {
                                $selected = ($filter_month !== '' && intval($filter_month) === $num) ? 'selected' : '';
                                echo "<option value='$num' $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year (साल)</label>
                        <select class="form-control" name="filter_year">
                            <option value="">-- All Years --</option>
                            <?php
                            $current_year = intval(date('Y'));
                            for ($y = $current_year - 2; $y <= $current_year + 5; $y++) {
                                $selected = ($filter_year !== '' && intval($filter_year) === $y) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 10px 12px; height: 38px;">Apply Filters</button>
                        <a href="history.php" class="btn btn-secondary" style="padding: 10px 12px; height: 38px; text-decoration: none; text-align: center; line-height: 18px;">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bills List Card -->
    <div class="card">
        <div class="card-header">
            <span>Invoices Found (<?php echo $res_invoices->num_rows; ?>)</span>
            <a href="index.php" style="font-size: 12px; color: var(--primary); text-decoration: none;">← Back to Dashboard</a>
        </div>
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
                                <a href="view_invoice.php?id=<?php echo $inv['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 11px;">View/Print</a>
                                <a href="edit_invoice.php?id=<?php echo $inv['id']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 11px; background-color: var(--secondary);">Edit</a>
                                <a href="history.php?delete=<?php echo $inv['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 11px;" onclick="return confirm('Are you sure you want to delete this bill?');">Delete</a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                No invoices found matching the selected filters.
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
