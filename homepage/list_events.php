<?php
session_start();

include '../includes/db_connect.php';

// Fetch upcoming events from the database
$sql = "SELECT * FROM events WHERE date >= CURDATE()";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="list_events.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Barangay Bulatok</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="homepagev3.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="list_events.php">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="list_files.php">Services</a></li>
                <li class="nav-item"><a class="btn btn-danger text-white" href="login.php">Log In</a></li>
            </ul>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="mb-4">Upcoming Events</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card event-card">
                            <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top event-image" alt="Event Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($row['date']); ?></p>
                                <p class="card-text"><strong>Time:</strong> <?php echo htmlspecialchars($row['time']); ?></p>
                                <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        No upcoming events found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

<footer style="background-color: #8b0000; color: white; padding: 20px; text-align: center; position: fixed; width: 100%; bottom: 0;">
    <p>&copy; 2023 Barangay Bulatok. All rights reserved.</p>
    <p>Contact us: <a href="mailto:info@barangaybulatok.com" style="color: white;">info@barangaybulatok.com</a></p>
    <p>Address: <a href="https://www.google.com/maps/place/Zone+2+Malipayon,+Bulatok,+Pagadian+City,+7016" target="_blank" style="color: white;">
            <span>&#128205;</span> Zone 2 Malipayon, Bulatok, Pagadian City, 7016</a></p>
</footer>

</html>