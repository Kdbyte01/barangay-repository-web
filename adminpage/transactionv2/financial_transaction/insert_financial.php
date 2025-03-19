<?php
include '../../../includes/db_connect.php'; // Adjust path as needed
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $chequeNumber = $_POST['chequeNumber'];
    $voucherNo = $_POST['voucherNo'];
    $fund = $_POST['fund'];
    $payee = $_POST['payee'];
    $particulars = $_POST['particulars'];
    $grossAmount = floatval($_POST['grossAmount']); // Ensure it's a float
    $vatable = $_POST['vatable']; // 1 if checked, 0 otherwise
    $vatvalue = $_POST['vat']; // 1 if checked, 0 otherwise
    $evatvalue = $_POST['evat']; // 1 if checked, 0 otherwise

    // Set default VAT and EVAT values if not provided
    $vatPercent = isset($_POST['vat']) ? floatval(str_replace('%', '', $_POST['vat'])) / 100 : 0;
    $evatPercent = isset($_POST['evat']) ? floatval(str_replace('%', '', $_POST['evat'])) / 100 : 0;

    // Compute VAT & EVAT
    $vat = $grossAmount * $vatPercent;
    $evat = $grossAmount * $evatPercent;

    // Compute Total Amount
    $totalAmount = $grossAmount - $vat - $evat;

    $sql = "INSERT INTO financial_transaction (date, cheque_no, dv_no, fund, payee, particulars, gross_amount, vatable, vat, evat, vat_amount, evat_amount, net_amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Error preparing statement: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssssssssssss", $date, $chequeNumber, $voucherNo, $fund, $payee, $particulars, $grossAmount, $vatable, $vatvalue, $evatvalue, $vat, $evat, $totalAmount);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Transaction successfully added!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
