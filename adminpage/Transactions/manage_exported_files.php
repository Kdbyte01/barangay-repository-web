<?php
include '../../includes/db_connect.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'fetch') {
    $sql = "SELECT * FROM exported_files ORDER BY export_date DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['file_name'] . "</td>";
            echo "<td>" . $row['export_type'] . "</td>";
            echo "<td>" . $row['export_date'] . "</td>";
            echo "<td><button class='btn btn-danger btn-sm' onclick='deleteExportedFile(" . $row['id'] . ")'>Delete</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No exported files found</td></tr>";
    }
} elseif ($action == 'delete') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM exported_files WHERE id = $id";
        $conn->query($sql);
    }
} elseif ($action == 'clear') {
    $sql = "DELETE FROM exported_files";
    $conn->query($sql);
}

$conn->close();
