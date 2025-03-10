<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

// Fetch events from the database
$sql = "SELECT * FROM events";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="view_events.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4 text-center">View Events</h3>
        <div class="card">
            <div class="card-header bg-dark text-white">Events</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td><img src='../uploads/" . htmlspecialchars($row['image']) . "' alt='Event Image' style='width: 100px;'></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="proceed-btn">
                    <button class="btn btn-primary" id="proceedToEditEvents"><i class="fas fa-edit"></i> Proceed to Edit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEditEvents').click(function() {
                window.location.href = 'manage_events.php';
            });
        });
    </script>
</body>

</html>