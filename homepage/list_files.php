<?php
include '../includes/db_connect.php';

// Fetch files from the database
$sql = "SELECT id, file_name FROM files";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloadable Files</title>
    <link href="list_files.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="homepagev3.css" rel="stylesheet">
    <link href="list_files.css" rel="stylesheet">
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
        <h2 class="mb-4">Downloadable Files</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card file-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['file_name']); ?></h5>
                                <a href="../uploads/files/<?php echo htmlspecialchars($row['file_name']); ?>" class="btn btn-primary" download>Download</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        No files available for download.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

<footer style="background-color: #8b0000; color: white; padding: 20px; text-align: center; position: fixed; width: 100%; bottom: 0;">
    <p>&copy; 2023 Barangay Bulatok. All rights reserved.</p>
    <p>Contact us: <a href="mailto:info@barangaybulatok.com" style="color: white;">info@barangaybulatok.com</a></p>
    <p>Address: <a href="https://www.google.com/maps/place/Zone+2+Malipayon,+Bulatok,+Pagadian+City,+7016" target="_blank" style="color: white;">
            <span>&#128205;</span> Zone 2 Malipayon, Bulatok, Pagadian City, 7016</a></p>
</footer>

</html>