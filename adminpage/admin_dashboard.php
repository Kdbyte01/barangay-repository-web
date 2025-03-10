<?php
session_start();

// Check if the 'dan' cookie exists
if (!isset($_COOKIE['dan'])) {
    // Redirect to login page if the cookie does not exist
    header("Location: ../homepage/login.php");
    exit();
}

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
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
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin_dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
                    // Reinitialize the click event for the "Proceed to Edit" button after loading new content
                    initializeProceedToEditButtons();
                }
            };
            xhr.send();
        }

        function initializeProceedToEditButtons() {
            $('#proceedToEditEvents').click(function() {
                window.location.href = 'manage_events.php';
            });

            $('#proceedToEditFiles').click(function() {
                window.location.href = 'manage_files.php';
            });

            $('#proceedToEditUsers').click(function() {
                window.location.href = 'manage_users.php';
            });

            $('#proceedToEdit').click(function() {
                window.location.href = 'officialProfile.php';
            });

            $('#proceedToEditSettings').click(function() {
                window.location.href = 'settings.php';
            });

            $('#proceedToEditChatbot').click(function() {
                window.location.href = 'chatbot/chatbot.php';
            });

            $('#proceedToEditTransactions').click(function() {
                window.location.href = 'transactionv2/transactionv2.php'; // Ensure this path is correct
            });

            $('#proceedToEditLoginHistory').click(function() {
                window.location.href = 'view_login_history.php';
            });
        }

        $(document).ready(function() {
            $('#menuBtn').click(function() {
                $(this).toggleClass('active');
                $('.dropdown-menu').toggle();
                adjustSidebarHeight();
            });

            $('#manageUsersBtn').click(function() {
                loadContent('view_users.php');
                setActiveButton(this);
            });

            $('#manageEventsBtn').click(function() {
                loadContent('view_events.php');
                setActiveButton(this);
            });

            $('#manageFilesBtn').click(function() {
                loadContent('view_files.php');
                setActiveButton(this);
            });

            $('#viewGraphsBtn').click(function() {
                $('#main-content').html('<h3>View Graphs</h3><div class="row"><div class="col-md-6"><canvas id="graph1"></canvas></div><div class="col-md-6"><canvas id="graph2"></canvas></div></div><hr><div id="calendar"></div>');
                loadGraphs();
                loadCalendar();
                setActiveButton(this);
            });

            $('#settingsBtn').click(function() {
                loadContent('view_settings.php');
                setActiveButton(this);
            });

            $('#officialProfilesBtn').click(function() {
                loadContent('view_official_profiles.php');
                setActiveButton(this);
            });

            $('#chatbotBtn').click(function() {
                loadContent('view_chatbot.php');
                setActiveButton(this);
            });

            $('#manageTransactionsBtn').click(function() {
                loadContent('view_transaction.php'); // Load the view-only interface
                setActiveButton(this);
            });

            $('#viewLoginHistoryBtn').click(function() {
                loadContent('view_login_history_view.php');
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

        function adjustSidebarHeight() {
            var sidebar = document.querySelector('.sidebar');
            var dropdownMenu = document.querySelector('.dropdown-menu');
            var logoutSection = document.querySelector('.logout-section');

            if (dropdownMenu.style.display === 'flex') {
                logoutSection.style.marginTop = dropdownMenu.offsetHeight + 'px';
            } else {
                logoutSection.style.marginTop = 'auto';
            }
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
            <p><?php echo $user['role'] == 'admin' ? 'Barangay Treasurer' : ucfirst(htmlspecialchars($user['role'])); ?></p>
        </div>
        <a href="#" id="viewGraphsBtn" class="active"><i class="fas fa-chart-bar"></i> Dashboard</a>

        <!-- Menu Button -->
        <div class="dropdown">
            <a href="#" id="menuBtn" class="dropdown-toggle"><i class="fas fa-bars"></i> Menu</a>
            <div class="dropdown-menu">
                <a href="#" id="manageTransactionsBtn"><i class="fas fa-file-invoice-dollar"></i> Transactions</a>
                <a href="#" id="officialProfilesBtn"><i class="fas fa-user-tie"></i> Official Profiles</a>
                <a href="#" id="manageUsersBtn"><i class="fas fa-users"></i> Manage Users</a>
                <a href="#" id="manageEventsBtn"><i class="fas fa-calendar-alt"></i> Manage Events</a>
                <a href="#" id="manageFilesBtn"><i class="fas fa-folder"></i> Manage Files</a>
                <a href="#" id="settingsBtn"><i class="fas fa-cogs"></i> Settings</a>
                <a href="#" id="chatbotBtn"><i class="fas fa-robot"></i> Chatbot Management</a>
            </div>
        </div>

        <a href="#" id="viewLoginHistoryBtn"><i class="fas fa-history"></i> View Login History</a>
        <div class="logout-section">
            <a href="logout.php" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="main-content" id="main-content">

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

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_out'])) {
    // Delete the 'dan' cookie
    setcookie('dan', '', time() - 3600, '/'); // Set expiration time to the past to delete

    // Redirect to login page
    header("Location: ../homepage/login.php");
    exit();
}
?>