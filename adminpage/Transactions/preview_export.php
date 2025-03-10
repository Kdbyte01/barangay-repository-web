<?php
include '../../includes/db_connect.php';

$export_type = $_POST['export_type'];
$file_name = $_POST['file_name'];

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

if (!$result) {
    die("Query failed: " . $conn->error);
}

$preview_data = [];
while ($row = $result->fetch_assoc()) {
    $preview_data[] = $row;
}

echo json_encode($preview_data);
