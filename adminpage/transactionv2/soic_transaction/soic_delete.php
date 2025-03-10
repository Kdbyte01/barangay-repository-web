<?php
include '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ids = json_decode($_POST['ids'], true);
    if (!empty($ids)) {
        $ids = implode(',', array_map('intval', $ids));
        $sql = "DELETE FROM transaction_soic WHERE id IN ($ids)";
        if ($conn->query($sql) === TRUE) {
            echo "Records deleted successfully";
        } else {
            echo "Error deleting records: " . $conn->error;
        }
    } else {
        echo "No records selected for deletion";
    }
}
