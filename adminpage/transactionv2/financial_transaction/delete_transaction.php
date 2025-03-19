<?php
include '../../../includes/db_connect.php'; // Database connection

header('Content-Type: application/json'); // Return JSON response

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure it's an integer to prevent SQL injection

    // Prepare the SQL DELETE query
    $query = "DELETE FROM financial_transaction WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id); // Bind parameter to prevent SQL injection

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Transaction has been deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete the transaction."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
