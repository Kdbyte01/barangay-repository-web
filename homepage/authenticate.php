<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            $user_role = $user['role'];

            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Set cookie for 1 hour
            $user_data = json_encode(['username' => $username, 'role' => $user_role]);
            setcookie('dan', $user_data, time() + 1 * 3600, '/');

            // Log the login time
            $login_time = date('Y-m-d H:i:s');
            $sql = "INSERT INTO login_history (username, login_time) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $login_time);
            $stmt->execute();

            if ($user['role'] == 'admin') {
                header("Location: ../adminpage/admin_dashboard.php");
            } else if ($user['role'] == 'user') {
                header("Location: ../userpage/user_dashboard.php");
            } else {
                header("Location: ../homepage/homepagev3.php");
            }
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to login page with error message
    $_SESSION['error_message'] = $error_message;
    header("Location: login.php");
    exit();
}
