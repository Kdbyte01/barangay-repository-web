<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $conn = new mysqli("localhost", "root", "", "barangay_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT file_path FROM carousel_images WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_path = $row['file_path'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $sql = "DELETE FROM carousel_images WHERE id = $id";
                if ($conn->query($sql) === TRUE) {
                    echo "The file " . htmlspecialchars(basename($file_path)) . " has been deleted.";
                } else {
                    echo "Error deleting record: " . $conn->error;
                }
            } else {
                echo "Sorry, there was an error deleting your file.";
            }
        } else {
            echo "File does not exist.";
        }
    } else {
        echo "Record not found.";
    }

    $conn->close();
}
