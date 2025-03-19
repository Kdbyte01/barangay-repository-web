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

            fputcsv($output, array('Date', 'Cheque No.', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT 3%', 'VAT 5%', 'VAT 12%', 'EVAT 1%', 'EVAT 2%', 'Net Amount'));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }
    }
}

$sql = "SELECT * FROM financial_transaction";
$result = $conn->query($sql);

// Fetch export history for financial transactions
$exportHistorySql = "SELECT * FROM export_history WHERE transaction_type IN ('FINANCIAL', 'SOIC', 'TRANSACTION1') ORDER BY export_date DESC";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            margin-top: 2rem;
        }

        .selected {
            background-color: #d1ecf1 !important;
        }

        .totals h4 {
            margin-bottom: 0.5rem;
        }

        .export-history a {
            text-decoration: none;
        }

        .scrollable-table {
            overflow-y: auto;
            max-height: 400px; /* Adjust based on your layout */
        }

        #exportModalLabel {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .modal.modal-fullscreen .modal-dialog {
            padding: 20px;
            max-width: 100%;
            margin: 0;
            height: 100%;
        }

        .modal.modal-fullscreen .modal-content {
            height: 100%;
            border-radius: 0;
        }

    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row m-4 d-flex align-items-center justify-content-between">
            <div>
                <button class="btn btn-secondary mr-4" onclick="location.href='../transactionv2.php'">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <h2 class="text-primary d-inline">Summary of Financial Transactions</h2>
            </div>
            <button class="btn btn-primary" onclick="location.href='../../manage_settings.php'">
                <i class="fas fa-cog"></i> Configuration
            </button>
        </div>
        <div class="card m-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaction History</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="startDate">Start Date:</label>
                            <input type="date" class="form-control" id="startDate" name="startDate">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="endDate">End Date:</label>
                            <input type="date" class="form-control" id="endDate" name="endDate">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <!-- Right-aligned Buttons -->
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success mr-2" onclick="showForm()">
                            <i class="fas fa-plus"></i> Add Transaction
                        </button>
                        <button class="btn btn-info mr-2" onclick="selectAllRows()">
                            <i class="fas fa-check-square"></i> Select All
                        </button>
                        <button class="btn btn-info mr-2" onclick="clearAllRows()">
                            <i class="fas fa-square"></i> Clear All
                        </button>
                        <button class="btn btn-secondary mr-2" onclick="showExportForm('SOIC')">
                            <i class="fas fa-file-export"></i> Export SOIC
                        </button>
                        <button class="btn btn-secondary" onclick="showExportForm('TRANSACTION1')">
                            <i class="fas fa-file-export"></i> Export Transaction1
                        </button>
                    </div>

                    <!-- Left-aligned Search Input -->
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchInput" onkeyup="filterTable()" placeholder="Search for entries..">
                    </div>
                </div>

                <table class="table table-bordered table-hover table-striped mt-3" id="dataTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Date &#9650;&#9660;</th>
                        <th onclick="sortTable(1)">Cheque No. &#9650;&#9660;</th>
                        <th onclick="sortTable(2)">Voucher No. &#9650;&#9660;</th>
                        <th onclick="sortTable(3)">Fund &#9650;&#9660;</th>
                        <th onclick="sortTable(4)">Payee &#9650;&#9660;</th>
                        <th onclick="sortTable(5)">Particulars &#9650;&#9660;</th>
                        <th onclick="sortTable(6)">Gross Amount &#9650;&#9660;</th>
                        <th onclick="sortTable(7)">VAT &#9650;&#9660;</th>
                        <th onclick="sortTable(8)">eVAT &#9650;&#9660;</th>
                        <th onclick="sortTable(9)">VAT Amount &#9650;&#9660;</th>
                        <th onclick="sortTable(10)">eVAT Amount &#9650;&#9660;</th>
                        <th onclick="sortTable(11)">Net Amount &#9650;&#9660;</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    if ($result->num_rows > 0) {
                        function formatPercentage($value) {
                            $floatValue = floatval($value);
                            return ($floatValue == floor($floatValue)) ? number_format($floatValue, 0) . "%" : number_format($floatValue, 2) . "%";
                        }
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr data-id='" . htmlspecialchars($row["id"]) . "' onclick='rowClicked(this)'>";
                            echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["cheque_no"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["dv_no"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["fund"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["payee"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["particulars"]) . "</td>";
                            echo "<td>" . number_format($row["gross_amount"], 2) . "</td>";
                            echo "<td>" . formatPercentage($row["vat"]) . "</td>";
                            echo "<td>" . formatPercentage($row["evat"]) . "</td>";
                            echo "<td>" . number_format(floatval($row["vat_amount"]), 2) . "</td>";
                            echo "<td>" . number_format(floatval($row["evat_amount"]), 2) . "</td>";
                            echo "<td>" . number_format(floatval($row["net_amount"]), 2) . "</td>";
                            echo "<td>
                                    <a href='#' class='text-primary edit-btn'
                                       data-id='" . $row['id'] . "'
                                       data-date='" . $row['date'] . "'
                                       data-cheque='" . $row['cheque_no'] . "'
                                       data-voucher='" . $row['dv_no'] . "'
                                       data-fund='" . $row['fund'] . "'
                                       data-payee='" . $row['payee'] . "'
                                       data-particulars='" . $row['particulars'] . "'
                                       data-gross='" . $row['gross_amount'] . "'
                                       data-vat='" . $row['vat'] . "'
                                       data-evat='" . $row['evat'] . "'
                                       data-toggle='modal' data-target='#editTransactionModal'>
                                        <button class='btn btn-success btn-sm'>Edit</button>
                                    </a>
                                    &nbsp;
                                    <a href='javascript:void(0);' class='text-danger' onclick='confirmDelete(" . $row['id'] . ");'>
                                        <button class='btn btn-danger btn-sm'>Delete</button>
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='13'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div id="pdfPreviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="pdfPreviewModalLabel">PDF Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <iframe id="pdfFrame" width="100%" height="500px"></iframe>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button id="downloadPdf" type="button" class="btn btn-primary">Download PDF</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Export Modal -->
        <div class="modal fade modal-fullscreen" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen" role="document">
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
                            <div class="form-row"> <!-- Use form-row for alignment -->
                                <div class="form-group col-md-6"> <!-- Adjust column width as needed -->
                                    <label for="filename">Filename:</label>
                                    <input type="text" class="form-control" id="filename" name="filename" required placeholder="enter filename here">
                                </div>
                                <div class="form-group col-md-6"> <!-- Same width as filename -->
                                    <label for="exportType">Export Type:</label>
                                    <select class="form-control" id="exportType" name="exportType" required>
                                        <option value="PDF">PDF</option>
                                        <option value="CSV">CSV</option>
                                        <option value="EXCEL">Excel</option>
                                    </select>
                                </div>
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
                                                    <th>VAT</th>
                                                    <th>eVAT</th>
                                                    <th>VAT Amount</th>
                                                    <th>EVAT Amount</th>
                                                    <th>Net Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="previewBody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary float-right">
                                <i class="fas fa-file-export"></i> Export
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card m-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Exported File History</h5>
            </div>
            <div class="card-body">
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
                                $fileUrl = "../../../exports/pdf/" . $row["filename"] . ".pdf"; // Use $row["filename"]

                                echo "<tr>";
                                echo "<td><a href='$fileUrl' target='_blank'>" . htmlspecialchars($row["filename"]) . "</a></td>";
                                echo "<td>" . htmlspecialchars($row["export_type"]) . "</td>";
                                echo "<td>" . date("F j, Y h:i A", strtotime($row["export_date"])) . "</td>";
                                echo "<td>" . htmlspecialchars($row["transaction_type"]) . "</td>";
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

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Entry</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <div class="form-group">
                                <label for="editDate">Date</label>
                                <input type="text" class="form-control" id="editDate" name="editDate">
                            </div>
                            <div class="form-group">
                                <label for="editChequeNo">Cheque No.</label>
                                <input type="text" class="form-control" id="editChequeNo" name="editChequeNo">
                            </div>
                            <div class="form-group">
                                <label for="editVoucherNo">Voucher No.</label>
                                <input type="text" class="form-control" id="editVoucherNo" name="editVoucherNo">
                            </div>
                            <div class="form-group">
                                <label for="editFund">Fund</label>
                                <input type="text" class="form-control" id="editFund" name="editFund">
                            </div>
                            <div class="form-group">
                                <label for="editPayee">Payee</label>
                                <input type="text" class="form-control" id="editPayee" name="editPayee">
                            </div>
                            <!-- Add additional fields as needed based on your data structure -->
                            <input type="hidden" id="editRowIndex" name="editRowIndex">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Form Modal -->
        <div class="modal fade" id="financialModal" tabindex="-1" role="dialog" aria-labelledby="financialModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="financialModalLabel">Add Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="" id="financialForm">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="date">Date:</label>
                                    <input type="date" class="form-control" id="date" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="chequeNumber">Cheque No.:</label>
                                    <input type="text" class="form-control" id="chequeNumber" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="voucherNo">Disbursement Voucher No.:</label>
                                    <input type="text" class="form-control" id="voucherNo" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="fund">Fund:</label>
                                    <input type="text" class="form-control" id="fund" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="payee">Payee:</label>
                                    <input type="text" class="form-control" id="payee" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="particulars">Particulars:</label>
                                    <input type="text" class="form-control" id="particulars" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="grossAmount">Gross Amount:</label>
                                    <input type="text" class="form-control" id="grossAmount" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="vatable">Vatable:</label>
                                    <div class="form-check">
                                        <input type="checkbox" id="vatable" class="form-check-input" onclick="toggleVatEvatFields()">
                                        <label class="form-check-label" for="vatable">Yes</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="vat">VAT:</label>
                                    <select class="form-control" id="vat" disabled>
                                        <option value="3%">3%</option>
                                        <option value="5%">5%</option>
                                        <option value="12%">12%</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="evat">EVAT:</label>
                                    <select class="form-control" id="evat" disabled>
                                        <option value="1%">1%</option>
                                        <option value="2%">2%</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success float-right">
                                <i class="fas fa-save"></i> Submit Transaction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Transaction Modal -->
        <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="update_transaction.php" id="editTransactionForm">
                            <input type="hidden" id="edit_id" name="id">

                            <div class="form-group">
                                <label for="edit_date">Date:</label>
                                <input type="date" class="form-control" id="edit_date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_cheque">Cheque No.:</label>
                                <input type="text" class="form-control" id="edit_cheque" name="cheque_no" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_voucher">Disbursement Voucher No.:</label>
                                <input type="text" class="form-control" id="edit_voucher" name="voucher_no" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_fund">Fund:</label>
                                <input type="text" class="form-control" id="edit_fund" name="fund" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_payee">Payee:</label>
                                <input type="text" class="form-control" id="edit_payee" name="payee" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_particulars">Particulars:</label>
                                <input type="text" class="form-control" id="edit_particulars" name="particulars" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_gross">Gross Amount:</label>
                                <input type="text" class="form-control" id="edit_gross" name="gross_amount" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_vat">VAT:</label>
                                <select class="form-control" id="edit_vat" name="vat">
                                    <option value="3%">3%</option>
                                    <option value="5%">5%</option>
                                    <option value="12%">12%</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_evat">EVAT:</label>
                                <select class="form-control" id="edit_evat" name="evat">
                                    <option value="1%">1%</option>
                                    <option value="2%">2%</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Transaction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Add this script to handle modal display -->
        <script>
        function showForm() {
            $('#financialModal').modal('show');
        }
        </script>
    </div>

    <!-- JavaScript includes -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- or latest version -->

    <!-- Bootstrap JS (make sure this is included after jQuery) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> <!-- or latest version -->
    <!-- SweetAlert2 CDN -->
    <script src="swal.js"></script>
    <!-- <script src="financial_formv2.js"></script> -->
    <script>
        $(document).ready(function() {
            $('.edit-btn').click(function() {
                $('#edit_id').val($(this).data('id'));
                $('#edit_date').val($(this).data('date'));
                $('#edit_cheque').val($(this).data('cheque'));
                $('#edit_voucher').val($(this).data('voucher'));
                $('#edit_fund').val($(this).data('fund'));
                $('#edit_payee').val($(this).data('payee'));
                $('#edit_particulars').val($(this).data('particulars'));
                $('#edit_gross').val($(this).data('gross'));
                $('#edit_vat').val($(this).data('vat'));
                $('#edit_evat').val($(this).data('evat'));
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $("#financialForm").on("submit", function(event) {
                event.preventDefault(); // Prevent default form submission

                let formData = {};
                let elements = this.elements;

                for (let element of elements) {
                    if (element.id && element.type !== "submit") {
                        if (element.type === "checkbox") {
                            formData[element.id] = element.checked ? 1 : 0; // Convert boolean to 1 or 0
                        } else {
                            formData[element.id] = element.value;
                        }
                    }
                }

                console.log("Form Data:", formData);

                $.ajax({
                    url: "insert_financial.php",
                    type: "POST",
                    data: formData,
                    dataType: "json", // Expect JSON response
                    success: function(response) {
                        if (response.status === "success") {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                $("#financialForm")[0].reset(); // Reset form fields
                                toggleVatEvatFields(); // Reset disabled VAT/EVAT fields
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message,
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        Swal.fire({
                            title: "Error!",
                            text: "An error occurred while processing the request.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            });

            function toggleVatEvatFields() {
                let vatSelect = document.getElementById("vat");
                let evatSelect = document.getElementById("evat");
                let vatableCheckbox = document.getElementById("vatable");

                vatSelect.disabled = !vatableCheckbox.checked;
                evatSelect.disabled = !vatableCheckbox.checked;
            }
        });
    </script>
    <script>
        document.getElementById("financialForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent actual form submission

            let formData = {};
            let elements = this.elements;

            for (let element of elements) {
                if (element.id && element.type !== "submit") {
                    if (element.type === "checkbox") {
                        formData[element.id] = element.checked;
                    } else {
                        formData[element.id] = element.value;
                    }
                }
            }

            console.log("Form Data:", formData);
        });

        function toggleVatEvatFields() {
            let vatSelect = document.getElementById("vat");
            let evatSelect = document.getElementById("evat");
            let vatableCheckbox = document.getElementById("vatable");

            vatSelect.disabled = !vatableCheckbox.checked;
            evatSelect.disabled = !vatableCheckbox.checked;
        }
    </script>
    <script>
        function toggleVatEvatFields() {
            // Get checkboxes
            var vatable = document.getElementById('vatable').checked;

            // Enable or disable VAT and EVAT dropdowns based on checkbox states
            document.getElementById('vat').disabled = !vatable;
            document.getElementById('evat').disabled = !vatable;
        }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        function allowOnlyNumbers(input) {
            input.addEventListener("input", function () {
                this.value = this.value.replace(/[^0-9]/g, ""); // Remove any non-numeric characters
            });
        }

        allowOnlyNumbers(document.getElementById("grossAmount"));
    });
    </script>
    <script>
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
                Swal.fire({
                    title: "Warning!",
                    text: "Please select at least one row to export.",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
                return;
            }

            var data = [];
            var totalGrossAmount = 0;
            var totalVATAmount = 0;
            var totalEVATAmount = 0;
            var totalNetAmount = 0;

            selectedRows.forEach(function(row) {
                var rowData = [];
                for (var i = 0; i < row.cells.length - 1; i++) { // Excluding last column (Edit/Delete)
                    rowData.push(row.cells[i].innerText);
                }
                data.push(rowData);

                // Get values and ensure they are parsed as numbers
                var grossAmount = parseFloat(row.cells[6].innerText.replace(/[^0-9.-]+/g, "")) || 0;
                var vatAmount = parseFloat(row.cells[9].innerText.replace(/[^0-9.-]+/g, "")) || 0;
                var evatAmount = parseFloat(row.cells[10].innerText.replace(/[^0-9.-]+/g, "")) || 0;
                var netAmount = parseFloat(row.cells[11].innerText.replace(/[^0-9.-]+/g, "")) || 0;

                // Add to totals
                totalGrossAmount += grossAmount;
                totalVATAmount += vatAmount;
                totalEVATAmount += evatAmount;
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

            // Append totals row
            var totalsRow = document.createElement("tr");
            totalsRow.innerHTML = `
                <td colspan="6"><strong>Totals</strong></td>
                <td><strong>₱${totalGrossAmount.toFixed(2)}</strong></td>
                <td><strong></strong></td> <!-- VAT Total -->
                <td><strong></strong></td> <!-- eVAT Total -->
                <td><strong>₱${totalVATAmount.toFixed(2)}</strong></td> <!-- VAT Amount -->
                <td><strong>₱${totalEVATAmount.toFixed(2)}</strong></td> <!-- eVAT Amount -->
                <td><strong>₱${totalNetAmount.toFixed(2)}</strong></td>
            `;
            previewBody.appendChild(totalsRow);

            if (transactionType === 'SOIC') {
                document.getElementById("exportForm").action = "financial_export_soic.php";
            } else {
                document.getElementById("exportForm").action = "financial_export_transaction1.php";
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
    <script>
        function filterTable() {
            let input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) { // Start from 1 to skip header row
                tr[i].style.display = "none"; // Hide all rows initially
                td = tr[i].getElementsByTagName("td");

                for (j = 0; j < td.length; j++) { // Loop through all columns
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = ""; // Show the row if match found
                            break; // No need to check other columns
                        }
                    }
                }
            }
        }
    </script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX request to delete_transaction.php
                    fetch("delete_transaction.php?id=" + id)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success") {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: data.message,
                                    icon: "success"
                                }).then(() => {
                                    location.reload(); // Refresh page after deletion
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: data.message,
                                    icon: "error"
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: "Error!",
                                text: "An unexpected error occurred.",
                                icon: "error"
                            });
                        });
                }
            });
        }
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("startDate").addEventListener("change", filterByDate);
            document.getElementById("endDate").addEventListener("change", filterByDate);
        });

        function filterByDate() {
            let startDate = document.getElementById("startDate").value;
            let endDate = document.getElementById("endDate").value;
            let table = document.getElementById("dataTable");
            let tbody = table.getElementsByTagName("tbody")[0];
            let rows = tbody.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                let dateCell = rows[i].getElementsByTagName("td")[0]; // Date column
                let rowDate = new Date(dateCell.textContent || dateCell.innerText);

                if (
                    (!startDate || new Date(startDate) <= rowDate) &&
                    (!endDate || new Date(endDate) >= rowDate)
                ) {
                    rows[i].style.display = ""; // Show row
                } else {
                    rows[i].style.display = "none"; // Hide row
                }
            }
        }

        function sortTable(columnIndex) {
            let table = document.getElementById("dataTable");
            let tbody = table.getElementsByTagName("tbody")[0];
            let rows = Array.from(tbody.getElementsByTagName("tr"));
            let isAscending = table.getAttribute("data-sort-order") === "asc";

            rows.sort((a, b) => {
                let aText = a.getElementsByTagName("td")[columnIndex].textContent.trim();
                let bText = b.getElementsByTagName("td")[columnIndex].textContent.trim();

                if (!isNaN(aText) && !isNaN(bText)) {
                    return isAscending ? aText - bText : bText - aText;
                } else {
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
            });

            rows.forEach(row => tbody.appendChild(row));

            table.setAttribute("data-sort-order", isAscending ? "desc" : "asc");
        }
    </script>
</body>
</html>