<?php
include '../../includes/db_connect.php';

// Fetch transactions from the database
$search_query = "SELECT * FROM financial_transactions";
$result = $conn->query($search_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="transaction.css">
    <link href="admin_dashboard.css" rel="stylesheet">
    <style>
        .btn-disabled {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-primary" onclick="window.location.href='../admin_dashboard.php'">Back to Dashboard</button>
        </div>

        <h2 class="text-center">Transaction Types (View Only)</h2>
        <div class="row justify-content-center mb-4">
            <div class="col-md-4">
                <button class="btn btn-primary btn-transaction btn-disabled">SOIC Transaction</button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-secondary btn-transaction btn-disabled">Other Transaction 1</button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-secondary btn-transaction btn-disabled">Other Transaction 2</button>
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
                            echo "<td>" . htmlspecialchars($row["filename"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["export_type"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["export_date"]) . "</td>";
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

        <div class="d-flex justify-content-center mt-4">
            <button class="btn btn-primary" id="proceedToEditTransactions">Proceed to Edit</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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