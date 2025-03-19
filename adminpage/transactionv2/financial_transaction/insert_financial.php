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
    $vatable = intval($_POST['vatable']); // Convert to integer (0 or 1)

    // Default values for VAT & EVAT
    $vat = 0;
    $evat = 0;
    $vatvalue = 0;
    $evatvalue = 0;
    $totalAmount = $grossAmount;

    // If vatable, compute VAT & EVAT
    if ($vatable === 1) {
        $vatvalue = isset($_POST['vat']) ? floatval(str_replace('%', '', $_POST['vat'])) / 100 : 0;
        $evatvalue = isset($_POST['evat']) ? floatval(str_replace('%', '', $_POST['evat'])) / 100 : 0;

        $vat = $grossAmount * $vatvalue;
        $evat = $grossAmount * $evatvalue;

        $totalAmount = $grossAmount - $vat - $evat;
    }

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