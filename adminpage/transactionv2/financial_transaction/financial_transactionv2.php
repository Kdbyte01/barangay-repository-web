<?php
include '../../../includes/db_connect.php';
require('financial_pdf.php'); // Updated path

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export'])) {
    $filename = $_POST['filename'];
    $exportType = $_POST['exportType'];
    $data = json_decode($_POST['data'], true);
    $transactionType = $_POST['transactionType']; // Get the transaction type from the form

    // Calculate totals
    $totalGrossAmount = 0;
    $totalVAT = 0;
    $totalEVAT = 0;
    $totalNetAmount = 0;

    foreach ($data as $row) {
        $grossAmount = floatval($row[6]);
        $vat = floatval($row[7]);
        $evat = floatval($row[8]);
        $netAmount = floatval($row[9]);

        $totalGrossAmount += $grossAmount;
        $totalVAT += $grossAmount * ($vat / 100);
        $totalEVAT += $grossAmount * ($evat / 100);
        $totalNetAmount += $netAmount;
    }

    $dateRange = getDateRange($data);

    if ($transactionType == 'SOIC') {
        if ($exportType == 'PDF') {
            $pdf = new PDF('L'); // Set orientation to landscape
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 8); // Reduced font size
            $header = array('Date', 'Cheque No.', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT 3%', 'VAT 5%', 'VAT 12%', 'EVAT 1%', 'EVAT 2%', 'Net Amount');
            $widths = array(25, 25, 25, 25, 33, 33, 27, 13, 13, 13, 13, 13, 27); // Adjusted widths

            foreach ($header as $key => $col) {
                $pdf->Cell($widths[$key], 7, $col, 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8); // Reduced font size

            foreach ($data as $row) {
                foreach ($row as $key => $col) {
                    $pdf->Cell($widths[$key], 6, $col, 1, 0, 'C');
                }
                $pdf->Ln();
            }

            // Add a clear row above the totals row
            $pdf->Cell(array_sum($widths), 6, '', 0, 1);

            // Add totals
            $pdf->Cell($widths[0], 6, '', 1);
            $pdf->Cell($widths[1], 6, '', 1);
            $pdf->Cell($widths[2], 6, '', 1);
            $pdf->Cell($widths[3], 6, '', 1);
            $pdf->Cell($widths[4], 6, '', 1);
            $pdf->Cell($widths[5], 6, 'Totals:', 1, 0, 'C');
            $pdf->Cell($widths[6], 6, '₱' . number_format($totalGrossAmount, 2), 1, 0, 'C');
            $pdf->Cell($widths[7], 6, '₱' . number_format($totalVAT, 2), 1, 0, 'C');
            $pdf->Cell($widths[8], 6, '₱' . number_format($totalEVAT, 2), 1, 0, 'C');
            $pdf->Cell($widths[9], 6, '₱' . number_format($totalNetAmount, 2), 1, 0, 'C');

            $pdf->Output('D', $filename . '.pdf');
        } elseif ($exportType == 'CSV') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=' . $filename . '.csv');
            $output = fopen('php://output', 'w');

            $payee = $_POST['payee'];
            $particulars = $_POST['particulars'];
            $gross_amount = $_POST['grossAmount'];
            $vatable = isset($_POST['vatable']) ? true : false;
            $evatable = isset($_POST['evatable']) ? true : false;
            $vat = $vatable ? rtrim($_POST['vat'], '%') : 0; // Remove the '%' character
            $evat = $evatable ? rtrim($_POST['evat'], '%') : 0; // Remove the '%' character
            $vat = floatval($vat); // Convert to float
            $evat = floatval($evat); // Convert to float

            // Ensure gross_amount is a float
            $gross_amount = floatval($gross_amount);

            // Calculate VAT and EVAT amounts
            $vat3 = $vat == 3 ? $gross_amount * 0.03 : 0;
            $vat5 = $vat == 5 ? $gross_amount * 0.05 : 0;
            $vat12 = $vat == 12 ? $gross_amount * 0.12 : 0;
            $evat1 = $evat == 1 ? $gross_amount * 0.01 : 0;
            $evat2 = $evat == 2 ? $gross_amount * 0.02 : 0;

            // Calculate net amount
            $net_amount = $gross_amount - $vat3 - $vat5 - $vat12 - $evat1 - $evat2;

            if ($editId) {
                // Update existing record
                $sql = "UPDATE financial_transaction SET date='$date', cheque_no='$cheque_no', dv_no='$dv_no', fund='$fund', payee='$payee', particulars='$particulars', gross_amount='$gross_amount', vat_3='$vat3', vat_5='$vat5', vat_12='$vat12', evat_1='$evat1', evat_2='$evat2', net_amount='$net_amount' WHERE id='$editId'";
            } else {
                // Insert new record
                $sql = "INSERT INTO financial_transaction (date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vat_3, vat_5, vat_12, evat_1, evat_2, net_amount) 
                        VALUES ('$date', '$cheque_no', '$dv_no', '$fund', '$payee', '$particulars', '$gross_amount', '$vat3', '$vat5', '$vat12', '$evat1', '$evat2', '$net_amount')";
            }

            if ($conn->query($sql) === TRUE) {
                $message = "Record saved successfully";
                $messageType = "success";
            } else {
                $message = "Error: " . $sql . "<br>" . $conn->error;
                $messageType = "danger";
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $editId = $_POST['editId'];
    $date = $_POST['date'];
    $cheque_no = $_POST['chequeNumber'];
    $dv_no = $_POST['voucherNo'];
    $fund = $_POST['fund'];
    $payee = $_POST['payee'];
    $particulars = $_POST['particulars'];
    $gross_amount = $_POST['grossAmount'];
    $vatable = isset($_POST['vatable']) ? true : false;
    $evatable = isset($_POST['evatable']) ? true : false;
    $vat = $vatable ? rtrim($_POST['vat'], '%') : 0; // Remove the '%' character
    $evat = $evatable ? rtrim($_POST['evat'], '%') : 0; // Remove the '%' character
    $vat = floatval($vat); // Convert to float
    $evat = floatval($evat); // Convert to float

    // Ensure gross_amount is a float
    $gross_amount = floatval($gross_amount);

    // Calculate VAT and EVAT amounts
    $vat3 = $vat == 3 ? $gross_amount * 0.03 : 0;
    $vat5 = $vat == 5 ? $gross_amount * 0.05 : 0;
    $vat12 = $vat == 12 ? $gross_amount * 0.12 : 0;
    $evat1 = $evat == 1 ? $gross_amount * 0.01 : 0;
    $evat2 = $evat == 2 ? $gross_amount * 0.02 : 0;

    // Calculate net amount
    $net_amount = $gross_amount - $vat3 - $vat5 - $vat12 - $evat1 - $evat2;

    if ($editId) {
        // Update existing record
        $sql = "UPDATE financial_transaction SET date='$date', cheque_no='$cheque_no', dv_no='$dv_no', fund='$fund', payee='$payee', particulars='$particulars', gross_amount='$gross_amount', vat_3='$vat3', vat_5='$vat5', vat_12='$vat12', evat_1='$evat1', evat_2='$evat2', net_amount='$net_amount' WHERE id='$editId'";
    } else {
        // Insert new record
        $sql = "INSERT INTO financial_transaction (date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vat_3, vat_5, vat_12, evat_1, evat_2, net_amount) 
                VALUES ('$date', '$cheque_no', '$dv_no', '$fund', '$payee', '$particulars', '$gross_amount', '$vat3', '$vat5', '$vat12', '$evat1', '$evat2', '$net_amount')";
    }

    if ($conn->query($sql) === TRUE) {
        $message = "Record saved successfully";
        $messageType = "success";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
        $messageType = "danger";
    }
}

$sql = "SELECT id, date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vat_3, vat_5, vat_12, evat_1, evat_2, net_amount FROM financial_transaction";
$result = $conn->query($sql);

// Fetch export history for financial transactions
$exportHistorySql = "SELECT filename, export_type, export_date, transaction_type, file_path FROM export_history WHERE transaction_type IN ('FINANCIAL', 'SOIC', 'TRANSACTION1') ORDER BY export_date DESC";
$exportHistoryResult = $conn->query($exportHistorySql);
if ($exportHistoryResult === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

function getDateRange($data)
{
    $dates = array_column($data, 0); // Assuming the date is in the first column
    $dates = array_map('strtotime', $dates);
    sort($dates);
    $startDate = date('F', reset($dates));
    $endDate = date('F', end($dates));
    $startYear = date('Y', reset($dates));
    $endYear = date('Y', end($dates));

    if ($startDate === $endDate && $startYear === $endYear) {
        return "For the Month of $startDate $startYear";
    } else {
        return "For the Months of $startDate - $endDate $startYear";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Financial Transactions</title>
    <link rel="stylesheet" href="financial_formv2.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 10px;
        }

        .selected {
            background-color: #d1ecf1 !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>Summary of Financial Transactions</h2>
                <button class="btn btn-secondary mb-3" onclick="location.href='../transactionv2.php'">
                    <i class="fas fa-arrow-left"></i> Back to Transactions
                </button>
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
                                <th>Transaction Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($exportHistoryResult->num_rows > 0) {
                                while ($row = $exportHistoryResult->fetch_assoc()) {
                                    $fileUrl = "download_export.php?file_path=" . urlencode($row["file_path"]);
                                    echo "<tr>";
                                    echo "<td><a href='$fileUrl'>" . $row["filename"] . "</a></td>";
                                    echo "<td>" . $row["export_type"] . "</td>";
                                    echo "<td>" . $row["export_date"] . "</td>";
                                    echo "<td>" . $row["transaction_type"] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No export history found</td></tr>";
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
                        <button class="btn btn-primary" onclick="filterByDate()">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <input type="text" class="form-control" id="searchInput" onkeyup="filterTable()" placeholder="Search for entries..">
                    </div>
                </div>
            </div>
        </div>

        <!-- Add these elements above the table -->
        <div class="row">
            <div class="col-md-6">
                <p>Total Rows: <span id="totalRows">0</span></p>
            </div>
            <div class="col-md-6">
                <p>Selected Rows: <span id="selectedRows">0</span></p>
            </div>
        </div>

        <div class="scrollable-table">
            <table class="table table-bordered table-hover table-striped" id="dataTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Date</th>
                        <th onclick="sortTable(1)">Cheque No.</th>
                        <th onclick="sortTable(2)">Voucher No.</th>
                        <th onclick="sortTable(3)">Fund</th>
                        <th onclick="sortTable(4)">Payee</th>
                        <th onclick="sortTable(5)">Particulars</th>
                        <th onclick="sortTable(6)">Gross Amount</th>
                        <th onclick="sortTable(7)">VAT 3%</th>
                        <th onclick="sortTable(8)">VAT 5%</th>
                        <th onclick="sortTable(9)">VAT 12%</th>
                        <th onclick="sortTable(10)">EVAT 1%</th>
                        <th onclick="sortTable(11)">EVAT 2%</th>
                        <th onclick="sortTable(12)">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr data-id='" . $row["id"] . "' onclick='rowClicked(this)'>";
                            echo "<td>" . $row["date"] . "</td>";
                            echo "<td>" . $row["cheque_no"] . "</td>";
                            echo "<td>" . $row["dv_no"] . "</td>";
                            echo "<td>" . $row["fund"] . "</td>";
                            echo "<td>" . $row["payee"] . "</td>";
                            echo "<td>" . $row["particulars"] . "</td>";
                            echo "<td>" . $row["gross_amount"] . "</td>";
                            echo "<td>" . number_format($row["vat_3"], 2) . "</td>";
                            echo "<td>" . number_format($row["vat_5"], 2) . "</td>";
                            echo "<td>" . number_format($row["vat_12"], 2) . "</td>";
                            echo "<td>" . number_format($row["evat_1"], 2) . "</td>";
                            echo "<td>" . number_format($row["evat_2"], 2) . "</td>";
                            echo "<td>" . number_format($row["net_amount"], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='13'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <button class="btn btn-warning" onclick="editSelectedRow()">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-danger" onclick="deleteSelectedRow()">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
            <button class="btn btn-info" onclick="selectAllRows()">
                <i class="fas fa-check-square"></i> Select All
            </button>
            <button class="btn btn-info" onclick="clearAllRows()">
                <i class="fas fa-square"></i> Clear All
            </button>
        </div>

        <div class="totals mt-3">
            <h4>Totals</h4>
            <p>Gross Amount: <span id="totalGrossAmount">₱0.00</span></p>
            <p>VAT: <span id="totalVAT">₱0.00</span></p>
            <p>EVAT: <span id="totalEVAT">₱0.00</span></p>
            <p>Net Amount: <span id="totalNetAmount">₱0.00</span></p>
        </div>

        <button class="btn btn-primary mt-3" onclick="showForm()">
            <i class="fas fa-plus"></i> ADD TRANSACTION
        </button>
        <div class="btn-group mt-3">
            <button class="btn btn-secondary" onclick="showExportForm('SOIC')">
                <i class="fas fa-file-export"></i> EXPORT SOIC
            </button>
            <button class="btn btn-secondary" onclick="showExportForm('TRANSACTION1')">
                <i class="fas fa-file-export"></i> EXPORT TRANSACTION1
            </button>
        </div>

        <form method="POST" action="" id="financialForm" class="mt-5 hidden">
            <input type="hidden" id="editId" name="editId">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="date">Date:</label>
                    <input type="date" class="form-control" id="date" name="date">
                </div>
                <div class="form-group col-md-6">
                    <label for="chequeNumber">Cheque No.:</label>
                    <input type="text" class="form-control" id="chequeNumber" name="chequeNumber">
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
                <div class="form-group col-md-3">
                    <label for="vatable">Vatable:</label>
                    <input type="checkbox" id="vatable" name="vatable" onclick="toggleVatEvatFields()">
                </div>
                <div class="form-group col-md-3">
                    <label for="vat">VAT:</label>
                    <select class="form-control" id="vat" name="vat" disabled>
                        <option value="3%">3%</option>
                        <option value="5%">5%</option>
                        <option value="12%">12%</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="evatable">EVATable:</label>
                    <input type="checkbox" id="evatable" name="evatable" onclick="toggleVatEvatFields()">
                </div>
                <div class="form-group col-md-3">
                    <label for="evat">EVAT:</label>
                    <select class="form-control" id="evat" name="evat" disabled>
                        <option value="1%">1%</option>
                        <option value="2%">2%</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Submit
            </button>
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
                        <form method="POST" action="" id="exportForm">
                            <input type="hidden" name="export" value="1">
                            <input type="hidden" name="data" id="exportData">
                            <input type="hidden" name="transactionType" id="transactionType">
                            <input type="hidden" name="exportDateTime" id="exportDateTime">
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
                                                    <th>Cheque No.</th>
                                                    <th>Voucher No.</th>
                                                    <th>Fund</th>
                                                    <th>Payee</th>
                                                    <th>Particulars</th>
                                                    <th>Gross Amount</th>
                                                    <th>VAT 3%</th>
                                                    <th>VAT 5%</th>
                                                    <th>VAT 12%</th>
                                                    <th>EVAT 1%</th>
                                                    <th>EVAT 2%</th>
                                                    <th>Net Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="previewBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-export"></i> Export
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript includes -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="financial_formv2.js"></script>
    <script>
        function calculateTotals() {
            var selectedRows = document.querySelectorAll('#dataTable tbody tr.selected');
            var totalGrossAmount = 0;
            var totalVAT = 0;
            var totalEVAT = 0;
            var totalNetAmount = 0;

            selectedRows.forEach(function(row) {
                var grossAmount = parseFloat(row.cells[6].innerText) || 0;
                var vat = parseFloat(row.cells[7].innerText) || 0;
                var evat = parseFloat(row.cells[8].innerText) || 0;
                var netAmount = parseFloat(row.cells[9].innerText) || 0;

                totalGrossAmount += grossAmount;
                totalVAT += grossAmount * (vat / 100);
                totalEVAT += grossAmount * (evat / 100);
                totalNetAmount += netAmount;
            });

            document.getElementById('totalGrossAmount').innerText = '₱' + totalGrossAmount.toFixed(2);
            document.getElementById('totalVAT').innerText = '₱' + totalVAT.toFixed(2);
            document.getElementById('totalEVAT').innerText = '₱' + totalEVAT.toFixed(2);
            document.getElementById('totalNetAmount').innerText = '₱' + totalNetAmount.toFixed(2);

            // Update selected rows count
            document.getElementById('selectedRows').innerText = selectedRows.length;
        }

        function rowClicked(row) {
            row.classList.toggle('selected');
            calculateTotals();
        }

        function selectAllRows() {
            var rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function(row) {
                if (row.style.display !== 'none') {
                    row.classList.add('selected');
                }
            });
            calculateTotals();
        }

        function clearAllRows() {
            var rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function(row) {
                row.classList.remove('selected');
            });
            calculateTotals();
        }

        function showExportForm(transactionType) {
            var selectedRows = document.querySelectorAll("tr.selected");
            if (selectedRows.length === 0) {
                alert("Please select at least one row to export.");
                return;
            }

            var data = [];
            var totalGrossAmount = 0;
            var totalVAT = 0;
            var totalEVAT = 0;
            var totalNetAmount = 0;

            selectedRows.forEach(function(row) {
                var rowData = [];
                for (var i = 0; i < row.cells.length; i++) {
                    rowData.push(row.cells[i].innerText);
                }
                data.push(rowData);

                var grossAmount = parseFloat(row.cells[6].innerText) || 0;
                var vat = parseFloat(row.cells[7].innerText) || 0;
                var evat = parseFloat(row.cells[8].innerText) || 0;
                var netAmount = parseFloat(row.cells[9].innerText) || 0;

                totalGrossAmount += grossAmount;
                totalVAT += grossAmount * (vat / 100);
                totalEVAT += grossAmount * (evat / 100);
                totalNetAmount += netAmount;
            });

            document.getElementById("exportData").value = JSON.stringify(data);
            document.getElementById("transactionType").value = transactionType;

            var currentDateTime = new Date();
            var formattedDate = (currentDateTime.getMonth() + 1) + '/' + currentDateTime.getDate() + '/' + currentDateTime.getFullYear();
            document.getElementById("exportDateTime").value = formattedDate;

            var previewBody = document.getElementById("previewBody");
            previewBody.innerHTML = "";

            data.forEach(function(row) {
                var tr = document.createElement("tr");
                row.forEach(function(cell) {
                    var td = document.createElement("td");
                    td.innerText = cell;
                    tr.appendChild(td);
                });
                previewBody.appendChild(tr);
            });

            var totalsRow = document.createElement("tr");
            totalsRow.innerHTML = `
                <td colspan="6">Totals</td>
                <td>₱${totalGrossAmount.toFixed(2)}</td>
                <td>₱${totalVAT.toFixed(2)}</td>
                <td>₱${totalEVAT.toFixed(2)}</td>
                <td>₱${totalNetAmount.toFixed(2)}</td>
            `;
            previewBody.appendChild(totalsRow);

            if (transactionType === 'SOIC') {
                document.getElementById("exportForm").action = "financial_export_soic.php";
            } else {
                document.getElementById("exportForm").action = "";
            }

            $("#exportModal").modal("show");
        }

        function updateTotalRowsCount() {
            var rows = document.querySelectorAll('#dataTable tbody tr');
            document.getElementById('totalRows').innerText = rows.length;
        }

        // Call this function after the table is populated
        updateTotalRowsCount();
    </script>
</body>

</html>