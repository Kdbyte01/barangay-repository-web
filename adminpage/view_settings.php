<?php
// filepath: /C:/xampp/htdocs/BarangayWeb/adminpage/view_settings.php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

// Fetch existing images
$sql = "SELECT id, file_path FROM carousel_images LIMIT 4";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Settings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin_dashboard.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .card {
            border: 2px solid #343a40;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>View Carousel Images</h2>
        <p>Here you can view the carousel images.</p>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?php echo htmlspecialchars($row['file_path']); ?>" class="card-img-top" alt="Carousel Image">
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <button id="proceedToEditSettings" class="btn btn-primary">Proceed to Edit</button>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEditSettings').click(function() {
                window.location.href = 'settings.php';
            });
        });
    </script>
</body>

</html>