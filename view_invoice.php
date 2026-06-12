<?php
// view_invoice.php - Smart Vyapar-Style Tax Invoice for KS Electrical and AC Services
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

// Helper function to convert number to Indian currency words
function getIndianCurrencyInWords($number) {
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    );
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter].$plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? "and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' only';
}

$amount_in_words = getIndianCurrencyInWords($invoice['grand_total']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice #<?php echo $invoice['id']; ?> | KS Electrical and AC Services</title>
    <link rel="stylesheet" href="style.css">
    <!-- html2pdf.js for Client-Side PDF Generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

<header>
    <div class="container header-container">
        <div class="logo-section">
            <h1 style="display: flex; align-items: center; gap: 10px;">
                <img src="logo.png" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
                KS Electrical and AC Services
            </h1>
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

    <!-- Action Buttons -->
    <div class="invoice-actions-bar">
        <button class="btn btn-primary" onclick="downloadInvoicePDF()">📥 Download PDF (पीडीएफ डाउनलोड करें)</button>
        <button class="btn btn-success" onclick="window.print()">🖨️ Print Invoice (प्रिंट निकालें)</button>
        <a href="create_invoice.php" class="btn btn-secondary">+ Create New Bill</a>
    </div>

    <!-- Vyapar-style Invoice Structure -->
    <div class="vyapar-container-wrapper">
        <div class="vyapar-invoice" id="invoice-print-area">
            
            <!-- Title Header -->
            <div class="vyapar-invoice-title">Tax Invoice</div>

            <!-- Company details with logo.png -->
            <div class="vyapar-header">
                <div class="vyapar-logo-container">
                    <?php if (file_exists('logo.png')) { ?>
                        <img class="vyapar-logo" src="logo.png" alt="Logo">
                    <?php } else { ?>
                        <div style="font-weight: 700; color: var(--primary);">KS</div>
                    <?php } ?>
                </div>
                <div class="vyapar-company-details">
                    <div class="vyapar-company-name">KS Electrical And AC Services</div>
                    <div class="vyapar-company-info">
                        Gaur City 1 sector 4 greater Noida Uttar Pradesh India<br>
                        <strong>Phone:</strong> 7895321472 &nbsp;|&nbsp; <strong>Email:</strong> kaushindrasingh04@gmail.com
                    </div>
                </div>
            </div>

            <!-- Metadata details -->
            <div class="vyapar-meta-grid">
                <div class="vyapar-meta-box vyapar-meta-box-left">
                    <div class="vyapar-meta-label">Bill To:</div>
                    <strong>Address:</strong><br>
                    <?php echo nl2br(htmlspecialchars($invoice['customer_address'])); ?><br><br>
                    <strong>Contact No:</strong> <?php echo htmlspecialchars($invoice['customer_phone']); ?>
                </div>
                <div class="vyapar-meta-box">
                    <div class="vyapar-meta-label">Invoice Details:</div>
                    <strong>No:</strong> <?php echo $invoice['id']; ?><br>
                    <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($invoice['invoice_date'])); ?>
                </div>
            </div>

            <!-- Items list table -->
            <div class="vyapar-table-container">
                <table class="vyapar-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">#</th>
                            <th style="width: 50%;">Item Name</th>
                            <th style="width: 15%; text-align: center;">HSN/ SAC</th>
                            <th style="width: 10%; text-align: center;">Quantity</th>
                            <th style="width: 10%; text-align: right;">Price/ Unit (₹)</th>
                            <th style="width: 10%; text-align: right;">Amount(₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        $total_qty = 0;
                        while($item = $res_items->fetch_assoc()) { 
                            $total_qty += $item['quantity'];
                        ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($item['hsn_sac']); ?></td>
                            <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right;">₹ <?php echo number_format($item['rate'], 2); ?></td>
                            <td style="text-align: right;">₹ <?php echo number_format($item['total'], 2); ?></td>
                        </tr>
                        <?php } ?>
                        <!-- Total Row -->
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right; padding-right: 15px;">Total</td>
                            <td style="text-align: center;"><?php echo $total_qty; ?></td>
                            <td></td>
                            <td style="text-align: right;">₹ <?php echo number_format($invoice['sub_total'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bottom summary blocks -->
            <div class="vyapar-bottom-section">
                <div class="vyapar-terms-box">
                    <div class="vyapar-terms-title">Terms And Conditions:</div>
                    <p>Thank you for doing business with us.</p>
                </div>
                <div class="vyapar-calculations-box">
                    <div class="vyapar-calc-row">
                        <span>Sub Total</span>
                        <span>: &nbsp;&nbsp;&nbsp; ₹ <?php echo number_format($invoice['sub_total'], 2); ?></span>
                    </div>
                    <?php if ($invoice['gst_amount'] > 0) { ?>
                    <div class="vyapar-calc-row">
                        <span>GST (<?php echo floatval($invoice['gst_rate']); ?>%)</span>
                        <span>: &nbsp;&nbsp;&nbsp; ₹ <?php echo number_format($invoice['gst_amount'], 2); ?></span>
                    </div>
                    <?php } ?>
                    <?php if ($invoice['discount'] > 0) { ?>
                    <div class="vyapar-calc-row">
                        <span>Discount</span>
                        <span>: &nbsp;&nbsp;&nbsp; -₹ <?php echo number_format($invoice['discount'], 2); ?></span>
                    </div>
                    <?php } ?>
                    <div class="vyapar-calc-row highlight">
                        <span>Total</span>
                        <span>: &nbsp;&nbsp;&nbsp; ₹ <?php echo number_format($invoice['grand_total'], 2); ?></span>
                    </div>
                    <div class="vyapar-words-row">
                        <strong>Invoice Amount in Words :</strong><br>
                        <span style="font-size: 11px; color: #475569;"><?php echo $amount_in_words; ?></span>
                    </div>
                    <div class="vyapar-calc-row">
                        <span>Received</span>
                        <span>: &nbsp;&nbsp;&nbsp; ₹ <?php echo number_format($invoice['received'], 2); ?></span>
                    </div>
                    <div class="vyapar-calc-row" style="color: var(--danger); font-weight: 600;">
                        <span>Balance</span>
                        <span>: &nbsp;&nbsp;&nbsp; ₹ <?php echo number_format($invoice['balance'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="vyapar-signature-section">
                <div class="vyapar-signature-box">
                    <div class="vyapar-signature-title">For KS Electrical And AC Services:</div>
                    <div class="vyapar-signature-graphic">Kaushindra</div>
                    <div class="vyapar-signature-footer">Authorized Signatory</div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function downloadInvoicePDF() {
    var element = document.getElementById('invoice-print-area');
    var opt = {
        margin:       [0.2, 0.2, 0.2, 0.2],
        filename:     'Invoice_KS_<?php echo $invoice['id']; ?>_' + '<?php echo str_replace(' ', '_', $invoice['customer_name']); ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2.5, useCORS: true, logging: false },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    
    // Convert to PDF and download
    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
