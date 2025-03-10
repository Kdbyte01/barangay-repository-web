<?php
include '../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Barangay Bulatok</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="about_style.css">
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

    <!-- About Us Section -->
    <div class="container about-section mt-5">
        <div class="row">
            <div class="col-md-4 text-left" style="margin-left: 0; padding-left: 0;"> <!-- Changed to col-md-4 and aligned to the left -->
                <img src="/uploads/logos/brgylogo.png" alt="Barangay Logo" class="img-fluid logo-img">
                <h3 class="mt-3">BARANGAY BULATOK</h3>
                <p><span>&#128205;</span> Zone 2 Malipayon, Bulatok, Pagadian City, 7016</p>
                <p><span>&#9993;</span> brgy.bulatok@gmail.com</p>
                <p><span>&#128222;</span> 0953-894-6017</p>
            </div>
            <div class="col-md-8">
                <h2 class="section-title">ABOUT US</h2>
                <h4 class="sub-title">Mission</h4>
                <ul>
                    <li>Promote Competitiveness: Empower residents with skills and resources.</li>
                    <li>Project-Oriented Development: Implement sustainable projects.</li>
                    <li>Generosity and Community Spirit: Foster support and unity.</li>
                    <li>Enhance Health and Well-being: Promote physical, mental, and social health.</li>
                </ul>
                <h4 class="sub-title">Vision</h4>
                <ul>
                    <li>To be a model barangay in the nation, renowned for its competitive spirit, sustainability, and commitment to community well-being.</li>
                </ul>
                <p></p>
            </div>
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