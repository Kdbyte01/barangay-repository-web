<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barangay Bulatok</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="header-bar"></div>
        <img src="/uploads/logos/brgylogo.png" alt="Barangay Logo" class="logo">
        <h2>Login</h2>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form action="authenticate.php" method="post">
            <div class="input-group">
                <label for="username">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </label>
            </div>
            <div class="input-group">
                <label for="password">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6-6V9c0-3.31-2.69-6-6-6S6 5.69 6 9v2H4v12h16V11h-2zm-2 0H8V9c0-2.21 1.79-4 4-4s4 1.79 4 4v2z" />
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                </label>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>

        <button onclick="location.href='homepagev5.php'" class="back-button">Back to Homepage</button>
    </div>

    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var toggleIcon = document.querySelector(".toggle-password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>