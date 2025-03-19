<?php
include '../../../includes/db_connect.php'; // Ensure correct path

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST['id']; // Ensure ID is an integer
    $date = $_POST['date'];
    $cheque_no = $_POST['cheque_no'];
    $dv_no = $_POST['voucher_no'];
    $fund = $_POST['fund'];
    $payee = $_POST['payee'];
    $particulars = $_POST['particulars'];
    $gross_amount = (float) $_POST['gross_amount']; // Ensure Gross Amount is a float

    // Remove % sign and convert VAT/eVAT to float
    $vat_percent = str_replace('%', '', $_POST['vat']); // Keep as string
    $evat_percent = str_replace('%', '', $_POST['evat']); // Keep as string

    // Calculate VAT Amount and eVAT Amount
    $vat_amount = ((float) $vat_percent / 100) * $gross_amount;
    $evat_amount = ((float) $evat_percent / 100) * $gross_amount;

    // Calculate Net Amount
    $net_amount = $gross_amount - ($vat_amount + $evat_amount);

    // Prepare SQL Update Statement
    $sql = "UPDATE financial_transaction SET
            date = ?,
            cheque_no = ?,
            dv_no = ?,
            fund = ?,
            payee = ?,
            particulars = ?,
            gross_amount = ?,
            vat = ?,
            evat = ?,
            vat_amount = ?,
            evat_amount = ?,
            net_amount = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters correctly
        $stmt->bind_param("sssssssdddddi",
            $date,
            $cheque_no,
            $dv_no,
            $fund,
            $payee,
            $particulars,
            $gross_amount,
            $vat_percent,
            $evat_percent,
            $vat_amount,
            $evat_amount,
            $net_amount,
            $id
        );

        // Execute and redirect based on success/failure
        if ($stmt->execute()) {
            header("Location: financial_transactionv2.php?success=1");
            exit();
        } else {
            header("Location: financial_transactionv2.php?error=" . urlencode("Error Updating Transaction: " . $stmt->error));
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        header("Location: financial_transactionv2.php?error=" . urlencode("Error Preparing Statement: " . $conn->error));
        exit();
    }

    // Close connection
    $conn->close();
} else {
    header("Location: financial_transactionv2.php?error=" . urlencode("Invalid Request Method!"));
    exit();
}
?>
