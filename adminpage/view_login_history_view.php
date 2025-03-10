<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Include the database connection file
include '../includes/db_connect.php';

// Fetch login history
$sql = "SELECT lh.username, lh.login_time, lh.logout_time, u.role FROM login_history lh JOIN users u ON lh.username = u.username ORDER BY lh.login_time DESC LIMIT 10";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History View</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="view_login_history_view.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Login History</h2>
        <div class="card">
            <div class="card-header bg-dark text-white">Login History</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['role'] == 'admin' ? 'Treasurer' : 'Encoder'); ?></td>
                                <td><?php echo htmlspecialchars($row['login_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['logout_time']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="proceed-btn">
                    <button class="btn btn-primary" id="proceedToEditLoginHistory"><i class="fas fa-edit"></i> Proceed to Edit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEditLoginHistory').click(function() {
                window.location.href = 'view_login_history.php';
            });
        });
    </script>
</body>

</html>