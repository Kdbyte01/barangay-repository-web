<?php
include '../includes/db_connect.php';

$sql = "SELECT filename, export_type, export_date, transaction_type FROM export_history ORDER BY export_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="view_transaction.css">
    <link href="admin_dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Transaction Types (View Only)</h2>
        <div class="row justify-content-center mb-4">
            <div class="col-md-4">
                <button class="btn btn-primary btn-transaction btn-disabled"><i class="fas fa-file-invoice-dollar"></i> Financial Transaction</button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-secondary btn-transaction btn-disabled"><i class="fas fa-file-alt"></i> Other Transaction 1</button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-secondary btn-transaction btn-disabled"><i class="fas fa-file-alt"></i> Other Transaction 2</button>
            </div>
        </div>

        <h2 class="text-center mt-5">Export History</h2>
        <div class="scrollable-table">
            <table class="table table-bordered table-hover table-striped" id="exportHistoryTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Filename</th>
                        <th>Export Type</th>
                        <th>Export Date</th>
                        <th>Transaction Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["filename"] . "</td>";
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

        <div class="d-flex justify-content-center mt-4">
            <button class="btn btn-primary" id="proceedToEditTransactions"><i class="fas fa-edit"></i> Proceed to Edit</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEditTransactions').click(function() {
                window.location.href = 'transactionv2/transactionv2.php';
            });
        });
    </script>
</body>

</html>