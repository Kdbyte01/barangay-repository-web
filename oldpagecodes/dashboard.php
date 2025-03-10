<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barangay_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$username = $_SESSION['username'];
$sql = "SELECT fullname, role, profile_image FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script>
        function loadContent(page) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', page, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('main-content').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <div class="profile-section">
            <?php if ($user['profile_image']): ?>
                <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-pic">
            <?php else: ?>
                <img src="default_profile.png" alt="Default Profile Image" class="profile-pic">
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
            <p><?php echo htmlspecialchars($user['role']); ?></p>
        </div>
        <p class="menu-title">MENU</p>
        <button class="menu-item" onclick="loadContent('dashboard_overview.php')">Dashboard</button>
        <button class="menu-item" onclick="loadContent('add_report.php')">Add Report</button>
        <button class="menu-item" onclick="loadContent('add_income.php')">Add Income</button>
        <button class="menu-item" onclick="loadContent('add_rao_programs.php')">Add Rao Programs</button>
        <button class="menu-item" onclick="loadContent('barangay_transaction.php')">Barangay Transaction</button>
        <button class="menu-item" onclick="loadContent('officials_profile.php')">Officials Profile</button>
        <button class="menu-item" onclick="loadContent('manage_events.php')">Events</button>
        <button class="menu-item" onclick="loadContent('manage_users.php')">Manage User</button>
        <button class="menu-item" onclick="loadContent('manage_files.php')">Files</button>
        <button class="logout-button" onclick="location.href='../homepage/logout.php'">LogOut</button>
    </div>
    <div class="main-content" id="main-content">
        <h1>Welcome, Admin!</h1>
        <p>Select an option from the menu to get started.</p>
    </div>
</body>

</html>