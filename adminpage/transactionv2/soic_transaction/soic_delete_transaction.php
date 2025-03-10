<?php
session_start();
include '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $query = "DELETE FROM soic_transaction WHERE id='$id'";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Record deleted successfully";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $query . "<br>" . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
    }

    mysqli_close($conn);
    header("Location: soic.php");
    exit();
}
