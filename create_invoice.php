<?php
// create_invoice.php - Invoice Creation for KS Electrical and AC Services
require_once 'db_connect.php';

$message = "";

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
    $payment_status = mysqli_real_escape_string($connect, $_POST['payment_status']);
    $payment_method = mysqli_real_escape_string($connect, $_POST['payment_method']);

    // Insert into invoices
    $sql_invoice = "INSERT INTO `invoices` 
        (`customer_name`, `customer_phone`, `customer_address`, `invoice_date`, `sub_total`, `gst_rate`, `gst_amount`, `discount`, `grand_total`, `payment_status`, `payment_method`) 
        VALUES 
        ('$customer_name', '$customer_phone', '$customer_address', '$invoice_date', $sub_total, $gst_rate, $gst_amount, $discount, $grand_total, '$payment_status', '$payment_method')";

    if ($connect->query($sql_invoice)) {
        $invoice_id = $connect->insert_id;

        // Insert items
        $descriptions = $_POST['description'];
        $quantities = $_POST['quantity'];
        $rates = $_POST['rate'];
        $totals = $_POST['total_val'];

        for ($i = 0; $i < count($descriptions); $i++) {
            $desc = mysqli_real_escape_string($connect, $descriptions[$i]);
            $qty = intval($quantities[$i]);
            $rate = floatval($rates[$i]);
            $tot = floatval($totals[$i]);

            if (!empty($desc)) {
                $sql_item = "INSERT INTO `invoice_items` (`invoice_id`, `description`, `quantity`, `rate`, `total`) 
                             VALUES ($invoice_id, '$desc', $qty, $rate, $tot)";
                $connect->query($sql_item);
            }
        }

        header("Location: view_invoice.php?id=" . $invoice_id);
        exit;
    } else {
        $message = "<div style='color: red; padding: 10px; margin-bottom: 20px; border: 1px solid red; background: #fee2e2; border-radius: 6px;'>Error creating invoice: " . $connect->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Bill | KS Electrical and AC Services</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container header-container">
        <div class="logo-section">
            <h1>KS Electrical and AC Services</h1>
            <span>Kaushindra Singh • Billing System</span>
        </div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="create_invoice.php" class="active">Create New Bill</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="section-title-bar">
        <h2>Create Customer Bill (नया बिल बनाएं)</h2>
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
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required placeholder="e.g. Ramesh Kumar">
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="text" class="form-control" id="customer_phone" name="customer_phone" required placeholder="e.g. 9876543210" pattern="[0-9]{10}" title="Please enter a 10 digit phone number">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Date *</label>
                        <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customer_address">Address / Location</label>
                    <textarea class="form-control" id="customer_address" name="customer_address" rows="2" placeholder="e.g. Flat 302, Sector 4, Vaishali, Ghaziabad"></textarea>
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
                                <th style="width: 50%;">Service/Part Description (कार्य का विवरण)</th>
                                <th style="width: 12%; text-align: center;">Qty (मात्रा)</th>
                                <th style="width: 18%; text-align: right;">Rate (₹) (दर)</th>
                                <th style="width: 15%; text-align: right;">Total (₹) (कुल)</th>
                                <th style="width: 5%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="description[]" required placeholder="e.g. Split AC Jet Servicing">
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
                                    <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="removeRow(this)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Invoice Summary & Save -->
        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); align-items: start;">
            <!-- Payment Info -->
            <div class="card">
                <div class="card-header">Payment details</div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="payment_status">Payment Status</label>
                        <select class="form-control" id="payment_status" name="payment_status">
                            <option value="Paid">Paid (पूरा भुगतान)</option>
                            <option value="Unpaid">Unpaid (उधार/बकाया)</option>
                            <option value="Partial">Partial (आंशिक भुगतान)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="Cash">Cash (नकद)</option>
                            <option value="UPI">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="Card">Card</option>
                            <option value="Net Banking">Net Banking</option>
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
                            <td>Subtotal (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="sub_total" name="sub_total" value="0.00" readonly style="text-align: right; font-weight: 500; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td>GST Rate (%)</td>
                            <td>
                                <input type="number" class="form-control" id="gst_rate" name="gst_rate" value="18" min="0" style="text-align: right;" oninput="calculateInvoiceTotal()">
                            </td>
                        </tr>
                        <tr>
                            <td>GST Amount (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="gst_amount" name="gst_amount" value="0.00" readonly style="text-align: right; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td>Discount (₹)</td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="0" min="0" style="text-align: right;" oninput="calculateInvoiceTotal()">
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Grand Total (₹)</strong></td>
                            <td>
                                <input type="number" step="0.01" class="form-control" id="grand_total" name="grand_total" value="0.00" readonly style="text-align: right; font-weight: 700; font-size: 18px; border: none; background: transparent; color: var(--primary);">
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 12px 20px;">Save & View Invoice (बिल सहेजें)</button>
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
            <input type="text" class="form-control" name="description[]" required placeholder="e.g. Split AC Installation">
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
            <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="removeRow(this)">Delete</button>
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
}
</script>

</body>
</html>
