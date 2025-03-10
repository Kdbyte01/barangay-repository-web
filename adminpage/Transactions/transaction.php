<?php
include '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_transaction'])) {
    $pbc_no = $_POST['pbc_no'];
    $dv_no = $_POST['dv_no'];
    $cheque_no = $_POST['cheque_no'];
    $account_no = $_POST['account_no'];
    $payee = $_POST['payee'];
    $date = $_POST['date'];
    $particulars = $_POST['particulars'];
    $fund = $_POST['fund'];
    $gross_amount = $_POST['gross_amount'];
    $vat_percentage = $_POST['vat'];

    // Calculate VAT, EVAT, and Net Amount
    $vat = ($gross_amount * $vat_percentage) / 100;
    $evat = $vat * 0.12; // Example calculation for EVAT
    $net_amount = $gross_amount - $vat - $evat;

    $sql = "INSERT INTO financial_transactions (date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vat, evat, net_amount)
            VALUES ('$date', '$cheque_no', '$dv_no', '$fund', '$payee', '$particulars', '$gross_amount', '$vat', '$evat', '$net_amount')";

    if ($conn->query($sql) === TRUE) {
        $message = "New transaction added successfully";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch transactions from the database
$search_query = "SELECT * FROM financial_transactions WHERE 1=1";

if (isset($_GET['search'])) {
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        $search_query .= " AND date BETWEEN '$start_date' AND '$end_date'";
    }
    if (!empty($_GET['search_payee'])) {
        $search_payee = $_GET['search_payee'];
        $search_query .= " AND payee LIKE '%$search_payee%'";
    }
}

$result = $conn->query($search_query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="transaction.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-primary" onclick="window.location.href='../admin_dashboard.php'">Back to Dashboard</button>
            <div class="btn-group">
                <button class="btn btn-success" data-toggle="modal" data-target="#exportExcelModal">Export to Excel</button>
                <button class="btn btn-info" data-toggle="modal" data-target="#exportCsvModal">Export to CSV</button>
                <button class="btn btn-danger" data-toggle="modal" data-target="#exportPdfModal">Export to PDF</button>
                <button class="btn btn-secondary" data-toggle="modal" data-target="#exportedFilesModal">View Exported Files</button>
            </div>
            <div>
                <form action="transaction.php" method="get" class="form-inline">
                    <input type="date" name="start_date" class="form-control mr-2" placeholder="Start Date">
                    <input type="date" name="end_date" class="form-control mr-2" placeholder="End Date">
                    <input type="text" name="search_payee" class="form-control mr-2" placeholder="Payee">
                    <button type="submit" name="search" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link active" href="#">SOIC</a></li>
            <li class="nav-item"><a class="nav-link" href="#">RAO</a></li>
            <li class="nav-item"><a class="nav-link" href="#">CASHBOOK</a></li>
            <li class="nav-item"><a class="nav-link" href="#">IMCD</a></li>
            <li class="nav-item"><a class="nav-link" href="#">REAI</a></li>
            <li class="nav-item"><a class="nav-link" href="#">TRANSMITTAL</a></li>
            <li class="nav-item"><a class="nav-link" href="#">PBC</a></li>
            <li class="nav-item"><a class="nav-link" href="#">DV</a></li>
        </ul>
    </div>

    <!-- Export to Excel Modal -->
    <div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportExcelModalLabel">Export to Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportExcelForm" action="export.php" method="post">
                        <div class="form-group">
                            <label for="excelFileName">File Name</label>
                            <input type="text" class="form-control" id="excelFileName" name="file_name" required>
                        </div>
                        <input type="hidden" name="export_type" value="excel">
                        <button type="button" class="btn btn-secondary" onclick="previewExport('excel')">Preview</button>
                        <button type="submit" class="btn btn-success">Export</button>
                    </form>
                    <div id="excelPreview" class="mt-3" style="display: none;">
                        <h5>Preview</h5>
                        <div id="excelPreviewContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export to CSV Modal -->
    <div class="modal fade" id="exportCsvModal" tabindex="-1" role="dialog" aria-labelledby="exportCsvModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportCsvModalLabel">Export to CSV</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportCsvForm" action="export.php" method="post">
                        <div class="form-group">
                            <label for="csvFileName">File Name</label>
                            <input type="text" class="form-control" id="csvFileName" name="file_name" required>
                        </div>
                        <input type="hidden" name="export_type" value="csv">
                        <button type="button" class="btn btn-secondary" onclick="previewExport('csv')">Preview</button>
                        <button type="submit" class="btn btn-info">Export</button>
                    </form>
                    <div id="csvPreview" class="mt-3" style="display: none;">
                        <h5>Preview</h5>
                        <div id="csvPreviewContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export to PDF Modal -->
    <div class="modal fade" id="exportPdfModal" tabindex="-1" role="dialog" aria-labelledby="exportPdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportPdfModalLabel">Export to PDF</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportPdfForm" action="export.php" method="post">
                        <div class="form-group">
                            <label for="pdfFileName">File Name</label>
                            <input type="text" class="form-control" id="pdfFileName" name="file_name" required>
                        </div>
                        <input type="hidden" name="export_type" value="pdf">
                        <button type="button" class="btn btn-secondary" onclick="previewExport('pdf')">Preview</button>
                        <button type="submit" class="btn btn-danger">Export</button>
                    </form>
                    <div id="pdfPreview" class="mt-3" style="display: none;">
                        <h5>Preview</h5>
                        <div id="pdfPreviewContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Exported Files -->
    <div class="modal fade" id="exportedFilesModal" tabindex="-1" role="dialog" aria-labelledby="exportedFilesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportedFilesModalLabel">Exported Files</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="text" id="searchExportedFiles" class="form-control mb-3" placeholder="Search by file name or date">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>File Name</th>
                                <th>Export Type</th>
                                <th>Export Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="exportedFilesTable">
                            <!-- Exported files will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="clearExportedFiles">Clear All</button>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Date</th>
                <th>Cheque No.</th>
                <th>DV No.</th>
                <th>Fund</th>
                <th>Payee</th>
                <th>Particulars</th>
                <th>Gross Amount</th>
                <th>VAT</th>
                <th>EVAT</th>
                <th>Net Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['cheque_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['dv_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['fund']); ?></td>
                    <td><?php echo htmlspecialchars($row['payee']); ?></td>
                    <td><?php echo htmlspecialchars($row['particulars']); ?></td>
                    <td><?php echo htmlspecialchars($row['gross_amount']); ?></td>
                    <td><?php echo htmlspecialchars($row['vat']); ?></td>
                    <td><?php echo htmlspecialchars($row['evat']); ?></td>
                    <td><?php echo htmlspecialchars($row['net_amount']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <form action="transaction.php" method="post" class="mt-4">
        <input type="hidden" name="add_transaction" value="1">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="pbc_no">PBC No.</label>
                <input type="text" class="form-control" id="pbc_no" name="pbc_no" required>
            </div>
            <div class="form-group col-md-3">
                <label for="dv_no">Disbursement Voucher No.</label>
                <input type="text" class="form-control" id="dv_no" name="dv_no" required>
            </div>
            <div class="form-group col-md-3">
                <label for="cheque_no">Cheque No.</label>
                <input type="text" class="form-control" id="cheque_no" name="cheque_no" required>
            </div>
            <div class="form-group col-md-3">
                <label for="account_no">Account No.</label>
                <input type="text" class="form-control" id="account_no" name="account_no" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="payee">Payee</label>
                <input type="text" class="form-control" id="payee" name="payee" required>
            </div>
            <div class="form-group col-md-3">
                <label for="date">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="form-group col-md-3">
                <label for="particulars">Particulars</label>
                <input type="text" class="form-control" id="particulars" name="particulars" required>
            </div>
            <div class="form-group col-md-3">
                <label for="fund">Fund</label>
                <input type="text" class="form-control" id="fund" name="fund" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="gross_amount">Gross Amount</label>
                <input type="number" step="0.01" class="form-control" id="gross_amount" name="gross_amount" required>
            </div>
            <div class="form-group col-md-3">
                <label for="vat">VAT (%)</label>
                <select class="form-control" id="vat" name="vat">
                    <option value="5">5%</option>
                    <option value="3">3%</option>
                    <option value="1">1%</option>
                    <option value="2">2%</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">ADD TRANSACTION</button>
    </form>
    </div>

    <script>
        $(document).ready(function() {
            // Load exported files when the modal is opened
            $('#exportedFilesModal').on('show.bs.modal', function() {
                loadExportedFiles();
            });

            // Search exported files
            $('#searchExportedFiles').on('keyup', function() {
                var query = $(this).val().toLowerCase();
                $('#exportedFilesTable tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
                });
            });

            // Clear all exported files
            $('#clearExportedFiles').on('click', function() {
                if (confirm('Are you sure you want to clear all exported files?')) {
                    $.ajax({
                        url: 'manage_exported_files.php',
                        type: 'POST',
                        data: {
                            action: 'clear'
                        },
                        success: function(response) {
                            loadExportedFiles();
                        }
                    });
                }
            });
        });

        function loadExportedFiles() {
            $.ajax({
                url: 'manage_exported_files.php',
                type: 'POST',
                data: {
                    action: 'fetch'
                },
                success: function(response) {
                    $('#exportedFilesTable').html(response);
                }
            });
        }

        function deleteExportedFile(id) {
            if (confirm('Are you sure you want to delete this file?')) {
                $.ajax({
                    url: 'manage_exported_files.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    success: function(response) {
                        loadExportedFiles();
                    }
                });
            }
        }

        function previewExport(exportType) {
            var formId = '';
            var previewId = '';
            var previewContentId = '';

            if (exportType === 'excel') {
                formId = '#exportExcelForm';
                previewId = '#excelPreview';
                previewContentId = '#excelPreviewContent';
            } else if (exportType === 'csv') {
                formId = '#exportCsvForm';
                previewId = '#csvPreview';
                previewContentId = '#csvPreviewContent';
            } else if (exportType === 'pdf') {
                formId = '#exportPdfForm';
                previewId = '#pdfPreview';
                previewContentId = '#pdfPreviewContent';
            }

            var formData = $(formId).serialize();

            $.ajax({
                url: 'preview_export.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    var previewData = JSON.parse(response);
                    var previewHtml = '<table class="table table-bordered"><thead><tr><th>Date</th><th>Cheque No.</th><th>DV No.</th><th>Fund</th><th>Payee</th><th>Particulars</th><th>Gross Amount</th><th>VAT</th><th>EVAT</th><th>Net Amount</th></tr></thead><tbody>';

                    previewData.forEach(function(row) {
                        previewHtml += '<tr>';
                        previewHtml += '<td>' + row.date + '</td>';
                        previewHtml += '<td>' + row.cheque_no + '</td>';
                        previewHtml += '<td>' + row.dv_no + '</td>';
                        previewHtml += '<td>' + row.fund + '</td>';
                        previewHtml += '<td>' + row.payee + '</td>';
                        previewHtml += '<td>' + row.particulars + '</td>';
                        previewHtml += '<td>' + row.gross_amount + '</td>';
                        previewHtml += '<td>' + row.vat + '</td>';
                        previewHtml += '<td>' + row.evat + '</td>';
                        previewHtml += '<td>' + row.net_amount + '</td>';
                        previewHtml += '</tr>';
                    });

                    previewHtml += '</tbody></table>';

                    $(previewContentId).html(previewHtml);
                    $(previewId).show();
                }
            });
        }
    </script>

</body>

</html>