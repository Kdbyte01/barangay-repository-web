<?php
include '../../includes/db_connect.php';

$sql = "SELECT filename, export_type, export_date, transaction_type FROM export_history ORDER BY export_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Types</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="transactionv2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <button class="btn btn-secondary mb-3" onclick="location.href='/adminpage/admin_dashboard.php'"><i class="fas fa-arrow-left"></i> Back</button>
        <div class="text-center">
            <h3>Select Transaction Type</h3>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <button class="btn btn-primary btn-transaction" onclick="location.href='financial_transaction/financial_transactionv2.php'"><i class="fas fa-file-invoice-dollar"></i> Financial Transaction</button>
                </div>
                <!-- Add more buttons for other transaction types as needed -->
                <div class="col-md-4">
                    <button class="btn btn-secondary btn-transaction" onclick="location.href='#'"><i class="fas fa-file-alt"></i> Other Transaction 1</button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-secondary btn-transaction" onclick="location.href='#'"><i class="fas fa-file-alt"></i> Other Transaction 2</button>
                </div>
            </div>

            <h3 class="mt-5">Export History</h3>
            <div class="form-row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" onkeyup="filterTable()" placeholder="Search for entries..">
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-danger" onclick="clearExportHistory()"><i class="fas fa-trash-alt"></i> Clear History</button>
                </div>
            </div>
            <div class="scrollable-table">
                <table class="table table-bordered table-hover table-striped" id="exportHistoryTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Filename</th>
                            <th onclick="sortTable(1)">Export Type</th>
                            <th onclick="sortTable(2)">Export Date</th>
                            <th onclick="sortTable(3)">Transaction Type</th>
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("exportHistoryTable");
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < rows.length - 1; i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }

        function filterTable() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toLowerCase();
            table = document.getElementById("exportHistoryTable");
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }

        function clearExportHistory() {
            if (confirm("Are you sure you want to clear the export history?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "clear_export_history.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        location.reload();
                    }
                };
                xhr.send();
            }
        }
    </script>
</body>

</html>