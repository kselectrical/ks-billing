<?php
// view_invoice.php - Printable Invoice View for KS Electrical and AC Services
require_once 'db_connect.php';

$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($invoice_id === 0) {
    header("Location: index.php");
    exit;
}

// Fetch invoice details
$sql_invoice = "SELECT * FROM `invoices` WHERE `id` = $invoice_id LIMIT 1";
$res_invoice = $connect->query($sql_invoice);

if ($res_invoice->num_rows == 0) {
    die("Invoice not found.");
}

$invoice = $res_invoice->fetch_assoc();

// Fetch invoice items
$sql_items = "SELECT * FROM `invoice_items` WHERE `invoice_id` = $invoice_id";
$res_items = $connect->query($sql_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #<?php echo $invoice['id']; ?> | KS Electrical and AC Services</title>
    <link rel="stylesheet" href="style.css">
    <!-- html2pdf.js Library for frontend PDF download on phone & PC -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            <a href="create_invoice.php">Create New Bill</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="section-title-bar">
        <h2>Invoice Details (बिल का विवरण)</h2>
        <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Action buttons (Hidden during printing) -->
    <div class="invoice-actions-bar">
        <button class="btn btn-primary" onclick="downloadInvoicePDF()">📥 Download PDF (पीडीएफ डाउनलोड करें)</button>
        <button class="btn btn-success" onclick="window.print()">🖨️ Print Invoice (प्रिंट निकालें)</button>
        <a href="create_invoice.php" class="btn btn-secondary">+ Create New Bill</a>
    </div>

    <!-- Printable Invoice A4 Container -->
    <div class="invoice-container" id="invoice-print-area">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-header-left">
                <h2>KS Electrical & AC Services</h2>
                <p><strong>Proprietor:</strong> Kaushindra Singh</p>
                <p>Specialist in: All types of AC Repair, Gas Charging, Installation & Electrical Services</p>
            </div>
            <div class="invoice-header-right">
                <h3>INVOICE (बिल)</h3>
                <p style="margin-top: 5px;"><strong>Invoice No:</strong> #KS-<?php echo str_pad($invoice['id'], 4, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($invoice['invoice_date'])); ?></p>
            </div>
        </div>

        <!-- Details Grid (Customer vs Service Provider) -->
        <div class="invoice-details-grid">
            <div class="invoice-details-box">
                <h4>Billed To (ग्राहक का नाम):</h4>
                <p style="font-weight: 600; font-size: 16px; color: var(--text-main);"><?php echo htmlspecialchars($invoice['customer_name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($invoice['customer_phone']); ?></p>
                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($invoice['customer_address'])); ?></p>
            </div>
            <div class="invoice-details-box" style="text-align: right;">
                <h4>Service Provider (सेवा प्रदाता):</h4>
                <p style="font-weight: 600;">KS Electrical & AC Services</p>
                <p><strong>Contact:</strong> +91 98765 43210</p>
                <p><strong>Email:</strong> kselectricals@gmail.com</p>
                <p>Ghaziabad, Uttar Pradesh, India</p>
            </div>
        </div>

        <!-- Services Table -->
        <table style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 55%;">Service / Item Description (कार्य का नाम)</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 15%; text-align: right;">Rate (₹)</th>
                    <th style="width: 15%; text-align: right;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                while($item = $res_items->fetch_assoc()) { 
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['rate'], 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Summary & Totals -->
        <div class="invoice-summary-box">
            <table class="summary-table">
                <tr>
                    <td>Subtotal (₹)</td>
                    <td style="text-align: right; font-weight: 500;"><?php echo number_format($invoice['sub_total'], 2); ?></td>
                </tr>
                <tr>
                    <td>GST (<?php echo floatval($invoice['gst_rate']); ?>%) (₹)</td>
                    <td style="text-align: right;"><?php echo number_format($invoice['gst_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td>Discount (₹)</td>
                    <td style="text-align: right; color: var(--danger);">-<?php echo number_format($invoice['discount'], 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Grand Total (₹)</strong></td>
                    <td style="text-align: right; font-weight: 700; color: var(--primary);"><?php echo number_format($invoice['grand_total'], 2); ?></td>
                </tr>
                <tr>
                    <td style="border-bottom: none; font-size: 13px;">Payment Status</td>
                    <td style="text-align: right; border-bottom: none;">
                        <span class="badge <?php 
                            if ($invoice['payment_status'] == 'Paid') echo 'badge-success';
                            elseif ($invoice['payment_status'] == 'Unpaid') echo 'badge-danger';
                            else echo 'badge-warning';
                        ?>">
                            <?php echo $invoice['payment_status']; ?> (<?php echo $invoice['payment_method']; ?>)
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer terms -->
        <div style="margin-top: 60px; border-top: 1px solid var(--border); padding-top: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
            <p>Thank you for choosing KS Electrical & AC Services! We appreciate your business.</p>
            <p style="margin-top: 5px; font-weight: 500;">This is a computer-generated invoice and requires no signature.</p>
        </div>
    </div>
</div>

<script>
function downloadInvoicePDF() {
    var element = document.getElementById('invoice-print-area');
    var opt = {
        margin:       0.3,
        filename:     'Invoice_KS_<?php echo $invoice['id']; ?>_' + '<?php echo str_replace(' ', '_', $invoice['customer_name']); ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    
    // Run html2pdf download
    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
