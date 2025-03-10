<?php
include '../../../includes/db_connect.php';
require('soic_pdf.php'); // Updated path

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export'])) {
    $filename = $_POST['filename'];
    $exportType = $_POST['exportType'];
    $data = json_decode($_POST['data'], true);
    $transactionType = 'SOIC'; // Set the transaction type

    if ($exportType == 'PDF') {
        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 5); // Reduced font size
        $header = array('Date', 'Check Number', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT', 'NET Amount');
        $widths = array(20, 20, 20, 20, 30, 30, 20, 10, 20); // Adjusted widths
        foreach ($header as $key => $col) {
            $pdf->Cell($widths[$key], 7, $col, 1);
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 4); // Reduced font size
        foreach ($data as $row) {
            foreach ($row as $key => $col) {
                $pdf->Cell($widths[$key], 6, $col, 1);
            }
            $pdf->Ln();
        }
        $pdf->Output('D', $filename . '.pdf');
    } elseif ($exportType == 'CSV') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Date', 'Check Number', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT', 'NET Amount'));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    } elseif ($exportType == 'EXCEL') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $filename . '.xls');
        echo '<table border="1">';
        echo '<tr><th>Date</th><th>Check Number</th><th>Voucher No.</th><th>Fund</th><th>Payee</th><th>Particulars</th><th>Gross Amount</th><th>VAT</th><th>NET Amount</th></tr>';
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . $cell . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    // Log the export in the export_history table
    $stmt = $conn->prepare("INSERT INTO export_history (filename, export_type, transaction_type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $filename, $exportType, $transactionType);
    $stmt->execute();
    $stmt->close();

    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['export'])) {
    $editId = $_POST['editId'] ?? null;
    $date = $_POST['date'];
    $check_no = $_POST['checkNumber'];
    $dv_no = $_POST['voucherNo'];
    $fund = $_POST['fund'];
    $payee = $_POST['payee'];
    $particulars = $_POST['particulars'];
    $gross_amount = $_POST['grossAmount'];
    $vat = rtrim($_POST['vat'], '%'); // Remove the '%' character
    $vat = floatval($vat); // Convert to float

    // Ensure gross_amount is a float
    $gross_amount = floatval($gross_amount);

    // Calculate net amount
    if ($vat == 0) {
        $net_amount = $gross_amount;
    } else {
        $net_amount = $gross_amount - ($gross_amount * ($vat / 100));
    }
    if ($editId) {
        // Update existing record
        $sql = "UPDATE transaction_soic SET date='$date', check_no='$check_no', dv_no='$dv_no', fund='$fund', payee='$payee', particulars='$particulars', gross_amount='$gross_amount', vat='$vat', net_amount='$net_amount' WHERE id='$editId'";
    } else {
        // Insert new record
        $sql = "INSERT INTO transaction_soic (date, check_no, dv_no, fund, payee, particulars, gross_amount, vat, net_amount) 
                VALUES ('$date', '$check_no', '$dv_no', '$fund', '$payee', '$particulars', '$gross_amount', '$vat', '$net_amount')";
    }

    if ($conn->query($sql) === TRUE) {
        $message = "Record saved successfully";
        $messageType = "success";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
        $messageType = "danger";
    }
}

$sql = "SELECT id, date, check_no, dv_no, fund, payee, particulars, gross_amount, vat, net_amount FROM transaction_soic";
$result = $conn->query($sql);

// Fetch export history for SOIC transactions
$exportHistorySql = "SELECT filename, export_type, export_date FROM export_history WHERE transaction_type = 'SOIC' ORDER BY export_date DESC";
$exportHistoryResult = $conn->query($exportHistorySql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOIC Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="soic_formv2.css">
    <style>
        .export-history {
            max-height: 150px;
            overflow-y: auto;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>Summary of Check Issued</h2>
                <button class="btn btn-secondary mb-3" onclick="location.href='../transactionv2.php'">Back to Transactions</button>

            </div>
            <div class="col-md-4">
                <h4>Exported File History</h4>
                <div class="export-history">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Export Type</th>
                                <th>Export Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($exportHistoryResult->num_rows > 0) {
                                while ($row = $exportHistoryResult->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["filename"] . "</td>";
                                    echo "<td>" . $row["export_type"] . "</td>";
                                    echo "<td>" . $row["export_date"] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No export history found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="startDate">Start Date:</label>
                        <input type="date" class="form-control" id="startDate" name="startDate">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="endDate">End Date:</label>
                        <input type="date" class="form-control" id="endDate" name="endDate">
                    </div>
                    <div class="form-group col-md-6">
                        <button class="btn btn-primary" onclick="filterByDate()">Search</button>
                    </div>
                </div>
                <div class="form-row">

                    <div class="form-group col-md-6">
                        <input type="text" class="form-control" id="searchInput" onkeyup="filterTable()" placeholder="Search for entries..">
                    </div>
                </div>
            </div>
        </div>
        <div class="scrollable-table">
            <table class="table table-bordered table-hover table-striped" id="dataTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Date</th>
                        <th onclick="sortTable(1)">Check Number</th>
                        <th onclick="sortTable(2)">Voucher No.</th>
                        <th onclick="sortTable(3)">Fund</th>
                        <th onclick="sortTable(4)">Payee</th>
                        <th onclick="sortTable(5)">Particulars</th>
                        <th onclick="sortTable(6)">Gross Amount</th>
                        <th onclick="sortTable(7)">VAT</th>
                        <th onclick="sortTable(8)">NET Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $net_amount = $row["gross_amount"] - ($row["gross_amount"] * ($row["vat"] / 100));
                            echo "<tr data-id='" . $row["id"] . "' onclick='rowClicked(this)'>";
                            echo "<td>" . $row["date"] . "</td>";
                            echo "<td>" . $row["check_no"] . "</td>";
                            echo "<td>" . $row["dv_no"] . "</td>";
                            echo "<td>" . $row["fund"] . "</td>";
                            echo "<td>" . $row["payee"] . "</td>";
                            echo "<td>" . $row["particulars"] . "</td>";
                            echo "<td>" . $row["gross_amount"] . "</td>";
                            echo "<td>" . $row["vat"] . "</td>";
                            echo "<td>" . $net_amount . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <button class="btn btn-warning" onclick="editSelectedRow()">Edit</button>
            <button class="btn btn-danger" onclick="deleteSelectedRow()">Delete</button>
        </div>

        <button class="btn btn-primary mt-3" onclick="showForm()">ADD TRANSACTION</button>
        <button class="btn btn-secondary mt-3" onclick="showExportForm()">EXPORT</button>

        <form method="POST" action="" id="soicForm" class="hidden mt-5">
            <input type="hidden" id="editId" name="editId">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="date">Date:</label>
                    <input type="date" class="form-control" id="date" name="date">
                </div>
                <div class="form-group col-md-6">
                    <label for="checkNumber">Check Number:</label>
                    <input type="text" class="form-control" id="checkNumber" name="checkNumber">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="voucherNo">Disbursement Voucher No.:</label>
                    <input type="text" class="form-control" id="voucherNo" name="voucherNo">
                </div>
                <div class="form-group col-md-6">
                    <label for="fund">Fund:</label>
                    <input type="text" class="form-control" id="fund" name="fund">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="payee">Payee:</label>
                    <input type="text" class="form-control" id="payee" name="payee">
                </div>
                <div class="form-group col-md-6">
                    <label for="particulars">Particulars:</label>
                    <input type="text" class="form-control" id="particulars" name="particulars">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="grossAmount">Gross Amount:</label>
                    <input type="number" class="form-control" id="grossAmount" name="grossAmount">
                </div>
                <div class="form-group col-md-6">
                    <label for="vat">VAT:</label>
                    <select class="form-control" id="vat" name="vat">
                        <option value="0%">0%</option>
                        <option value="5%">5%</option>
                        <option value="12%">12%</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <!-- Export Modal -->
        <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <input type="hidden" name="export" value="1">
                            <input type="hidden" name="data" id="exportData">
                            <div class="form-group">
                                <label for="filename">Filename:</label>
                                <input type="text" class="form-control" id="filename" name="filename" required>
                            </div>
                            <div class="form-group">
                                <label for="exportType">Export Type:</label>
                                <select class="form-control" id="exportType" name="exportType" required>
                                    <option value="CSV">CSV</option>
                                    <option value="EXCEL">Excel</option>
                                    <option value="PDF">PDF</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Preview:</label>
                                <div id="preview" class="border p-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Check Number</th>
                                                    <th>Voucher No.</th>
                                                    <th>Fund</th>
                                                    <th>Payee</th>
                                                    <th>Particulars</th>
                                                    <th>Gross Amount</th>
                                                    <th>VAT</th>
                                                    <th>NET Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="previewBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Export</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="soic_formv2.js"></script>
</body>

</html>