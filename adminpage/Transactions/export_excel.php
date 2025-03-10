<?php
// filepath: /C:/xampp/htdocs/BarangayWeb/adminpage/Transactions/export_excel.php
include '../../includes/db_connect.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=financial_transactions.xls");
header("Pragma: no-cache");
header("Expires: 0");

$search_query = "SELECT * FROM financial_transactions WHERE 1=1";

if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $search_query .= " AND date BETWEEN '$start_date' AND '$end_date'";
}
if (!empty($_POST['search_payee'])) {
    $search_payee = $_POST['search_payee'];
    $search_query .= " AND payee LIKE '%$search_payee%'";
}

$result = $conn->query($search_query);

echo "Date\tCheque No.\tDV No.\tFund\tPayee\tParticulars\tGross Amount\tVAT\tEVAT\tNet Amount\n";

while ($row = $result->fetch_assoc()) {
    echo $row['date'] . "\t" . $row['cheque_no'] . "\t" . $row['dv_no'] . "\t" . $row['fund'] . "\t" . $row['payee'] . "\t" . $row['particulars'] . "\t" . $row['gross_amount'] . "\t" . $row['vat'] . "\t" . $row['evat'] . "\t" . $row['net_amount'] . "\n";
}

$conn->close();
