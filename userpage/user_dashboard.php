<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: ../homepage/login.php");
    exit();
}

// Include the database connection file
include '../includes/db_connect.php';

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
    <title>User Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="user_dashboard.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
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

        $(document).ready(function() {
            $('#viewGraphsBtn').click(function() {
                $('#main-content').html('<h3>View Graphs</h3><div class="row"><div class="col-md-6"><canvas id="graph1"></canvas></div><div class="col-md-6"><canvas id="graph2"></canvas></div></div><div id="calendar"></div>');
                loadGraphs();
                loadCalendar();
                setActiveButton(this);
            });

            $('#officialProfilesBtn').click(function() {
                loadContent('view_official_profiles.php');
                setActiveButton(this);
            });

            $('#barangayTransactionsBtn').click(function() {
                loadContent('view_transactions.php');
                setActiveButton(this);
            });

            // Trigger the click event for the "View Graphs" button on page load
            $('#viewGraphsBtn').trigger('click');
        });

        function setActiveButton(button) {
            $('.sidebar a').removeClass('active');
            $(button).addClass('active');
        }

        function loadGraphs() {
            var ctx1 = document.getElementById('graph1').getContext('2d');
            var ctx2 = document.getElementById('graph2').getContext('2d');

            var graph1 = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                    datasets: [{
                        label: 'Dataset 1',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            var graph2 = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                    datasets: [{
                        label: 'Dataset 2',
                        data: [28, 48, 40, 19, 86, 27, 90],
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function loadCalendar() {
            var calendarEl = document.getElementById('calendar');
            calendarEl.style.maxWidth = '350px'; // Set max-width to 350px
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 320,
                dateClick: function(info) {
                    $('#eventDate').val(info.dateStr);
                    $('#eventModal').modal('show');
                }
            });
            calendar.render();
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
            <p><?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
        </div>
        <a href="#" id="viewGraphsBtn" class="active">Dashboard</a>
        <a href="#" id="officialProfilesBtn">Official Profile</a>
        <a href="#" id="barangayTransactionsBtn">Barangay Transactions</a>
        <a href="logout.php" id="logoutBtn">Logout</a>
    </div>
    <div class="main-content" id="main-content">
        <h3>Welcome to the User Dashboard</h3>
        <p>Select an option from the sidebar to get started.</p>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <div class="form-group">
                            <label for="eventDate">Date</label>
                            <input type="text" class="form-control" id="eventDate" readonly>
                        </div>
                        <div class="form-group">
                            <label for="eventDescription">Event Description</label>
                            <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#eventForm').on('submit', function(event) {
            event.preventDefault();
            var date = $('#eventDate').val();
            var description = $('#eventDescription').val();
            alert('Event on ' + date + ': ' + description);
            $('#eventModal').modal('hide');
        });
    </script>
</body>

</html>