<?php
include '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['export'])) {
    $id = $_POST['editId'];
    $date = $_POST['date'];
    $cheque_no = $_POST['cheque_no'];
    $dv_no = $_POST['dv_no'];
    $fund = $_POST['fund'];
    $payee = $_POST['payee'];
    $particulars = $_POST['particulars'];
    $gross_amount = $_POST['gross_amount'];
    $vat = isset($_POST['vat']) ? $_POST['vat'] : 0;
    $evat = isset($_POST['evat']) ? $_POST['evat'] : 0;
    $net_amount = $gross_amount - ($gross_amount * ($vat / 100)) - ($gross_amount * ($evat / 100));

    if ($id) {
        // Update existing record
        $sql = "UPDATE financial_transaction SET date=?, cheque_no=?, dv_no=?, fund=?, payee=?, particulars=?, gross_amount=?, vat=?, evat=?, net_amount=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssddddi", $date, $cheque_no, $dv_no, $fund, $payee, $particulars, $gross_amount, $vat, $evat, $net_amount, $id);
    } else {
        // Insert new record
        $sql = "INSERT INTO financial_transaction (date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vat, evat, net_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssdddd", $date, $cheque_no, $dv_no, $fund, $payee, $particulars, $gross_amount, $vat, $evat, $net_amount);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Record saved successfully');</script>";
    } else {
        echo "<script>alert('Error saving record');</script>";
    }
    $stmt->close();
}

$sql = "SELECT id, date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vat, evat, net_amount FROM financial_transaction";
$result = $conn->query($sql);

// Fetch export history for financial transactions
$exportHistorySql = "SELECT filename, export_type, export_date FROM export_history WHERE transaction_type = 'Financial' ORDER BY export_date DESC";
$exportHistoryResult = $conn->query($exportHistorySql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Transaction Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="financial_form.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>Financial Transactions</h2>
                <button class="btn btn-secondary mb-3" onclick="location.href='../transactionv3.php'">Back to Transactions</button>
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
                        <th onclick="sortTable(1)">Cheque No.</th>
                        <th onclick="sortTable(2)">DV No.</th>
                        <th onclick="sortTable(3)">Fund</th>
                        <th onclick="sortTable(4)">Payee</th>
                        <th onclick="sortTable(5)">Particulars</th>
                        <th onclick="sortTable(6)">Gross Amount</th>
                        <th onclick="sortTable(7)">VAT</th>
                        <th onclick="sortTable(8)">EVAT</th>
                        <th onclick="sortTable(9)">Net Amount</th>
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
                            echo "<td>" . $row["vat"] . "</td>";
                            echo "<td>" . $row["evat"] . "</td>";
                            echo "<td>" . $row["net_amount"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <button class="btn btn-warning" onclick="editSelectedRow()">Edit</button>
            <button class="btn btn-danger" onclick="deleteSelectedRow()">Delete</button>
            <button class="btn btn-info" onclick="selectAllRows()">Select All</button>
            <button class="btn btn-secondary" onclick="clearSelection()">Clear Selection</button>
        </div>

        <button class="btn btn-primary mt-3" onclick="showForm()">ADD TRANSACTION</button>
        <button class="btn btn-secondary mt-3" onclick="showExportForm('soic_export.php')">EXPORT SOIC</button>
        <button class="btn btn-secondary mt-3" onclick="showExportForm('other_export.php')">EXPORT OTHER</button>

        <form method="POST" action="" id="financialForm" class="hidden mt-5">
            <input type="hidden" id="editId" name="editId">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="date">Date:</label>
                    <input type="date" class="form-control" id="date" name="date">
                </div>
                <div class="form-group col-md-6">
                    <label for="cheque_no">Cheque No.:</label>
                    <input type="text" class="form-control" id="cheque_no" name="cheque_no">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="dv_no">DV No.:</label>
                    <input type="text" class="form-control" id="dv_no" name="dv_no">
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
                <div class="form-group col-md-2">
                    <label for="vatable">Vatable:</label>
                    <input type="checkbox" class="form-control" id="vatable" name="vatable">
                </div>
                <div class="form-group col-md-4">
                    <label for="vat">VAT:</label>
                    <select class="form-control" id="vat" name="vat" disabled>
                        <option value="0">0%</option>
                        <option value="1">1%</option>
                        <option value="5">5%</option>
                        <option value="12">12%</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="evat">EVAT:</label>
                    <select class="form-control" id="evat" name="evat" disabled>
                        <option value="0">0%</option>
                        <option value="1">1%</option>
                        <option value="5">5%</option>
                        <option value="12">12%</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="net_amount">Net Amount:</label>
                    <input type="number" step="0.01" class="form-control" id="net_amount" name="net_amount" readonly>
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
                        <form method="POST" action="" id="exportForm">
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
        <script src="financial_form.js"></script>
</body>

</html>