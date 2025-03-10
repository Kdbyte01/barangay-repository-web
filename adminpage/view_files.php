<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Correct the include path to the database connection file
include '../includes/db_connect.php';

// Fetch files from the database
$sql = "SELECT id, file_name FROM files"; // Ensure the correct column names
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Files</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="view_files.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4 text-center">View Files</h3>
        <div class="card">
            <div class="card-header bg-dark text-white">Files</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <!-- Add more columns as needed -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['file_name']) . "</td>";
                                // Add more columns as needed
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No files found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="proceed-btn">
                    <button class="btn btn-primary" id="proceedToEditFiles"><i class="fas fa-edit"></i> Proceed to Edit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEditFiles').click(function() {
                window.location.href = 'manage_files.php';
            });
        });
    </script>
</body>

</html>