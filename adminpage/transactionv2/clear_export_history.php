<?php
include '../../includes/db_connect.php';

$sql = "DELETE FROM export_history";
if ($conn->query($sql) === TRUE) {
    echo "Export history cleared successfully.";
} else {
    echo "Error clearing export history: " . $conn->error;
}

$conn->close();
