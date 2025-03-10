<?php
session_start();
include '../includes/db_connect.php';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $logout_time = date('Y-m-d H:i:s');

    // Update the logout time in the login_history table
    $sql = "UPDATE login_history SET logout_time=? WHERE username=? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $logout_time, $username);
    $stmt->execute();
    $stmt->close();
}

// Destroy the session and delete the cookie
session_destroy();
setcookie('dan', '', time() - 3600, '/');

// Redirect to login page
header("Location: \homepage\login.php");
exit();
