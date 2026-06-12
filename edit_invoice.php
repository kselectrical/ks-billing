<?php
// edit_invoice.php - Edit Existing Invoices for KS Electrical and AC Services
require_once 'auth_check.php';
require_once 'db_connect.php';

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($invoice_id === 0) {
    header("Location: index.php");
    exit;
}

$message = "";

// Fetch Invoice details
$sql_invoice = "SELECT * FROM `invoices` WHERE `id` = $invoice_id LIMIT 1";
$res_invoice = $connect->query($sql_invoice);

if ($res_invoice->num_rows == 0) {
    die("Invoice not found.");
}

$invoice = $res_invoice->fetch_assoc();

// Fetch Invoice Items
$sql_items = "SELECT * FROM `invoice_items` WHERE `invoice_id` = $invoice_id";
$res_items = $connect->query($sql_items);
$items_list = [];
while ($row = $res_items->fetch_assoc()) {
    $items_list[] = $row;
}

// Handle Form Submission (Save updates)
if ($_POST) {
    $customer_name = mysqli_real_escape_string($connect, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($connect, $_POST['customer_phone']);
    $customer_address = mysqli_real_escape_string($connect, $_POST['customer_address']);
    $invoice_date = mysqli_real_escape_string($connect, $_POST['invoice_date']);
    $sub_total = floatval($_POST['sub_total']);
    $gst_rate = floatval($_POST['gst_rate']);
    $gst_amount = floatval($_POST['gst_amount']);
    $discount = floatval($_POST['discount']);
    $grand_total = floatval($_POST['grand_total']);
    $received = floatval($_POST['received']);
    $balance = floatval($_POST['balance']);
    $payment_method = mysqli_real_escape_string($connect, $_POST['payment_method']);

    // Set payment status based on received amount
    $payment_status = "Paid";
    if ($received == 0) {
        $payment_status = "Unpaid";
    } elseif ($received < $grand_total) {
        $payment_status = "Partial";
    }

    // Update invoices table
    $sql_update = "UPDATE `invoices` SET 
        `customer_name` = '$customer_name', 
        `customer_phone` = '$customer_phone', 
        `customer_address` = '$customer_address', 
        `invoice_date` = '$invoice_date', 
        `sub_total` = $sub_total, 
        `gst_rate` = $gst_rate, 
        `gst_amount` = $gst_amount, 
        `discount` = $discount, 
        `grand_total` = $grand_total, 
        `received` = $received, 
        `balance` = $balance, 
        `payment_status` = '$payment_status', 
        `payment_method` = '$payment_method' 
        WHERE `id` = $invoice_id";

    if ($connect->query($sql_update)) {
        // Delete old items for this invoice
        $connect->query("DELETE FROM `invoice_items` WHERE `invoice_id` = $invoice_id");

        // Insert new/updated items
        $descriptions = $_POST['description'];
        $hsn_sacs = $_POST['hsn_sac'];
        $quantities = $_POST['quantity'];
        $rates = $_POST['rate'];
        $totals = $_POST['total_val'];

        for ($i = 0; $i < count($descriptions); $i++) {
            $desc = mysqli_real_escape_string($connect, $descriptions[$i]);
            $hsn = mysqli_real_escape_string($connect, $hsn_sacs[$i]);
            $qty = intval($quantities[$i]);
            $rate = floatval($rates[$i]);
            $tot = floatval($totals[$i]);

            if (!empty($desc)) {
                $sql_item = "INSERT INTO `invoice_items` (`invoice_id`, `description`, `hsn_sac`, `quantity`, `rate`, `total`) 
                             VALUES ($invoice_id, '$desc', '$hsn', $qty, $rate, $tot)";
                $connect->query($sql_item);
            }
        }

        header("Location: view_invoice.php?id=" . $invoice_id);
        exit;
    } else {
        $message = "<div style='color: red; padding: 10px; margin-bottom: 20px; border: 1px solid red; background: #fee2e2; border-radius: 6px;'>Error updating invoice: " . $connect->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bill #<?php echo $invoice['id']; ?> | KS Electrical and AC Services</title>
    <!-- Appending time() forces browser to clear cached CSS instantly -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
    <div class="container header-container">
        <div class="logo-section">
            <h1 style="display: flex; align-items: center; gap: 8px;">
                <?php if (file_exists('logo.png')) { ?>
                    <img src="logo.png" alt="Logo" style="height: 28px; width: auto; object-fit: contain;">
                <?php } ?>
                KS Electrical and AC Services
            </h1>
            <span>Kaushindra Singh • Billing System</span>
        </div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="create_invoice.php">Create New Bill</a>
            <a href="logout.php" style="color: var(--danger); margin-left: 10px;">Logout</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="section-title-bar">
        <h2>Edit Bill #KS-<?php echo str_pad($invoice['id'], 4, '0', STR_PAD_LEFT); ?> (बिल संपादित करें)</h2>
        <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <?php echo $message; ?>

    <form method="POST" id="invoiceForm">
        <!-- Customer Info Card -->
        <div class="card">
            <div class="card-header">Customer Details (ग्राहक का विवरण)</div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_name">Customer Name *</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required value="<?php echo htmlspecialchars($invoice['customer_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="text" class="form-control" id="customer_phone" name="customer_phone" required value="<?php echo htmlspecialchars($invoice['customer_phone']); ?>" pattern="[0-9]{10,12}" title="Please enter a valid phone number">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Date *</label>
                        <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?php echo $invoice['invoice_date']; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customer_address">Address / Location</label>
                    <textarea class="form-control" id="customer_address" name="customer_address" rows="2"><?php echo htmlspecialchars($invoice['customer_address']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Services / Items Card -->
        <div class="card">
            <div class="card-header">
                <span>Services & Spare Parts List</span>
                <button type="button" class="btn btn-secondary" onclick="addRow()">+ Add Service Row</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Item Name / Service Description *</th>
                                <th style="width: 15%;">HSN / SAC</th>
                                <th style="width: 10%; text-align: center;">Quantity</th>
                                <th style="width: 15%; text-align: right;">Price / Unit (₹)</th>
                                <th style="width: 12%; text-align: right;">Amount (₹)</th>
                                <th style="width: 3%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_list as $item) { ?>
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="description[]" required value="<?php echo htmlspecialchars($item['description']); ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="hsn_sac[]" value="<?php echo htmlspecialchars($item['hsn_sac']); ?>">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="quantity[]" value="<?php echo $item['quantity']; ?>" min="1" style="text-align: center;" oninput="calculateRowTotal(this)">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control" name="rate[]" required style="text-align: right;" value="<?php echo $item['rate']; ?>" oninput="calculateRowTotal(this)">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control row-total-input" name="total_val[]" value="<?php echo $item['total']; ?>" readonly style="text-align: right; font-weight: 600; border: none; background: transparent;">
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="removeRow(this)">X</button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Invoice Summary & Save -->
        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); align-items: start;">
            <!-- Payment Info -->
            <div class="card">
                <div class="card-header">Payment Info</div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="Cash" <?php if ($invoice['payment_method'] == 'Cash') echo 'selected'; ?>>Cash (नकद)</option>
                            <option value="UPI" <?php if ($invoice['payment_method'] == 'UPI') echo 'selected'; ?>>UPI (GPay / PhonePe / Paytm)</option>
                            <option value="Card" <?php if ($invoice['payment_method'] == 'Card') echo 'selected'; ?>>Card</option>
                            <option value="Net Banking" <?php if ($invoice['payment_method'] == 'Net Banking') echo 'selected'; ?>>Net Banking</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Calculations Box -->
            <div class="card">
                <div class="card-header">Total Calculation</div>
                <div class="card-body">
                    <table class="summary-table" style="max-width: 100%;">
                        <tr>
                            <td>Sub Total (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="sub_total" name="sub_total" value="<?php echo $invoice['sub_total']; ?>" readonly style="text-align: right; font-weight: 500; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td>GST Rate (%)</td>
                            <td>
                                <input type="number" class="form-control" id="gst_rate" name="gst_rate" value="<?php echo floatval($invoice['gst_rate']); ?>" min="0" style="text-align: right;" oninput="calculateInvoiceTotal()">
                            </td>
                        </tr>
                        <tr>
                            <td>GST Amount (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="gst_amount" name="gst_amount" value="<?php echo $invoice['gst_amount']; ?>" readonly style="text-align: right; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="<?php echo $invoice['discount']; ?>" min="0" style="text-align: right;" oninput="calculateInvoiceTotal()">
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total (₹)</strong></td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="grand_total" name="grand_total" value="<?php echo $invoice['grand_total']; ?>" readonly style="text-align: right; font-weight: 700; font-size: 16px; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr style="background-color: #f8fafc;">
                            <td>Received Amount (₹) *</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="received" name="received" value="<?php echo $invoice['received']; ?>" min="0" style="text-align: right; font-weight: 600;" oninput="calculateInvoiceTotal()">
                            </td>
                        </tr>
                        <tr>
                            <td>Balance Due (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="<?php echo $invoice['balance']; ?>" readonly style="text-align: right; font-weight: 600; border: none; background: transparent; color: var(--danger);">
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 12px 20px;">Save & View Invoice (बदलाव सहेजें)</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function addRow() {
    var table = document.getElementById("itemsTable").getElementsByTagName('tbody')[0];
    var newRow = table.insertRow();
    
    newRow.innerHTML = `
        <td>
            <input type="text" class="form-control" name="description[]" required placeholder="e.g. Labor Charge / Part Name">
        </td>
        <td>
            <input type="text" class="form-control" name="hsn_sac[]" placeholder="e.g. 9987">
        </td>
        <td>
            <input type="number" class="form-control" name="quantity[]" value="1" min="1" style="text-align: center;" oninput="calculateRowTotal(this)">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="rate[]" required style="text-align: right;" placeholder="0.00" oninput="calculateRowTotal(this)">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control row-total-input" name="total_val[]" value="0.00" readonly style="text-align: right; font-weight: 600; border: none; background: transparent;">
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="removeRow(this)">X</button>
        </td>
    `;
}

function removeRow(btn) {
    var table = document.getElementById("itemsTable").getElementsByTagName('tbody')[0];
    if (table.rows.length > 1) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
        calculateInvoiceTotal();
    } else {
        alert("At least one service row must be kept.");
    }
}

function calculateRowTotal(input) {
    var row = input.parentNode.parentNode;
    var qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
    var rate = parseFloat(row.querySelector('input[name="rate[]"]').value) || 0;
    var total = qty * rate;
    
    row.querySelector('.row-total-input').value = total.toFixed(2);
    calculateInvoiceTotal();
}

function calculateInvoiceTotal() {
    var rows = document.querySelectorAll('.items-table tbody tr');
    var subtotal = 0;
    
    rows.forEach(function(row) {
        var rowTotal = parseFloat(row.querySelector('.row-total-input').value) || 0;
        subtotal += rowTotal;
    });
    
    document.getElementById('sub_total').value = subtotal.toFixed(2);
    
    var gstRate = parseFloat(document.getElementById('gst_rate').value) || 0;
    var gstAmount = (subtotal * gstRate) / 100;
    document.getElementById('gst_amount').value = gstAmount.toFixed(2);
    
    var discount = parseFloat(document.getElementById('discount').value) || 0;
    var grandTotal = (subtotal + gstAmount) - discount;
    
    document.getElementById('grand_total').value = grandTotal.toFixed(2);
    
    var received = parseFloat(document.getElementById('received').value) || 0;
    var balance = grandTotal - received;
    if (balance < 0) balance = 0; // Prevent negative balance
    
    document.getElementById('balance').value = balance.toFixed(2);
}
</script>

</body>
</html>
