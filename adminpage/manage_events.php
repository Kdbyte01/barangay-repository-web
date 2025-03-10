<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

$success_message = "";
$error_message = "";

// Handle form submissions for adding, updating, and deleting events
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_event'])) {
        $title = $_POST['title'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $image = $_FILES['image']['name'];

        // Handle file upload
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO events (title, date, time, location, description, image) VALUES ('$title', '$date', '$time', '$location', '$description', '$image')";
            if ($conn->query($sql) === TRUE) {
                $success_message = "New event created successfully.";
            } else {
                $error_message = "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            $error_message = "Error uploading file.";
        }
    } elseif (isset($_POST['update_event'])) {
        $event_id = $_POST['event_id'];
        $title = $_POST['title'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $image = $_FILES['image']['name'];

        if ($image) {
            // Handle file upload
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $sql = "UPDATE events SET title='$title', date='$date', time='$time', location='$location', description='$description', image='$image' WHERE id=$event_id";
            } else {
                $error_message = "Error uploading file.";
                exit();
            }
        } else {
            $sql = "UPDATE events SET title='$title', date='$date', time='$time', location='$location', description='$description' WHERE id=$event_id";
        }

        if ($conn->query($sql) === TRUE) {
            $success_message = "Event updated successfully.";
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['delete_event'])) {
        $event_id = $_POST['event_id'];
        $sql = "DELETE FROM events WHERE id=$event_id";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Event deleted successfully.";
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['move_to_history'])) {
        $eventId = $_POST['event_id'];
        $sql = "UPDATE events SET date = DATE_SUB(date, INTERVAL 1 YEAR) WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_events.php");
        exit();
    }
}

// Fetch upcoming events from the database
$sql = "SELECT * FROM events WHERE date >= CURDATE()";
$upcomingEvents = $conn->query($sql);

// Fetch event history from the database
$sql = "SELECT * FROM events WHERE date < CURDATE()";
$eventHistory = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="manage_events.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Events</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="action-icons mb-4">
            <button class="btn btn-primary" id="toggleAddEventForm"><i class="fas fa-plus"></i> Add Event</button>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Add Event Form -->
        <div class="card mb-4 hidden" id="addEventForm">
            <div class="card-header">Add New Event</div>
            <div class="card-body">
                <form id="addEventFormContent" action="manage_events.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="time">Time</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Event Image</label>
                        <input type="file" class="form-control-file" id="image" name="image" required>
                    </div>
                    <button type="submit" name="add_event" class="btn btn-success"><i class="fas fa-upload"></i> Add Event</button>
                </form>
            </div>
        </div>

        <!-- Edit Event Form -->
        <div class="card mb-4 hidden" id="editEventForm">
            <div class="card-header">Edit Event</div>
            <div class="card-body">
                <form id="editEventFormContent" action="manage_events.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="edit_event_id" name="event_id">
                    <div class="form-group">
                        <label for="edit_title">Event Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_date">Date</label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_time">Time</label>
                        <input type="time" class="form-control" id="edit_time" name="time" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_location">Location</label>
                        <input type="text" class="form-control" id="edit_location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="current_image">Current Image</label>
                        <img id="current_image" src="" alt="Current Event Image" style="width: 100px; display: block; margin-bottom: 10px;">
                    </div>
                    <div class="form-group">
                        <label for="edit_image">Event Image</label>
                        <input type="file" class="form-control-file" id="edit_image" name="image">
                    </div>
                    <button type="submit" name="update_event" class="btn btn-success"><i class="fas fa-save"></i> Update Event</button>
                </form>
            </div>
        </div>

        <!-- Upcoming Events Table -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Upcoming Events</div>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($upcomingEvents->num_rows > 0) {
                            while ($row = $upcomingEvents->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td><img src='../uploads/" . htmlspecialchars($row['image']) . "' alt='Event Image' style='width: 100px;'></td>";
                                echo "<td>
                                    <button class='btn btn-warning btn-sm editEvent' data-id='" . $row['id'] . "' data-title='" . htmlspecialchars($row['title']) . "' data-date='" . htmlspecialchars($row['date']) . "' data-time='" . htmlspecialchars($row['time']) . "' data-location='" . htmlspecialchars($row['location']) . "' data-description='" . htmlspecialchars($row['description']) . "' data-image='" . htmlspecialchars($row['image']) . "'><i class='fas fa-edit'></i> Edit</button>
                                    <form method='post' style='display:inline-block;'>
                                        <input type='hidden' name='event_id' value='" . $row['id'] . "'>
                                        <button type='submit' name='move_to_history' class='btn btn-warning btn-sm'><i class='fas fa-archive'></i> Move to History</button>
                                    </form>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No upcoming events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Event History Table -->
        <div class="card">
            <div class="card-header bg-dark text-white">Event History</div>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($eventHistory->num_rows > 0) {
                            while ($row = $eventHistory->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td><img src='../uploads/" . htmlspecialchars($row['image']) . "' alt='Event Image' style='width: 100px;'></td>";
                                echo "<td>
                                    <form method='post' style='display:inline-block;'>
                                        <input type='hidden' name='event_id' value='" . $row['id'] . "'>
                                        <button type='submit' name='delete_event' class='btn btn-danger btn-sm'><i class='fas fa-trash-alt'></i> Delete</button>
                                    </form>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No event history found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#toggleAddEventForm').click(function() {
                $('#addEventForm').toggle();
                $('#editEventForm').hide();
                $('#addEventFormContent')[0].reset();
            });

            $(document).on('click', '.editEvent', function() {
                const eventId = $(this).data('id');
                const title = $(this).data('title');
                const date = $(this).data('date');
                const time = $(this).data('time');
                const location = $(this).data('location');
                const description = $(this).data('description');
                const image = $(this).data('image');

                $('#editEventForm').show();
                $('#addEventForm').hide();
                $('#edit_event_id').val(eventId);
                $('#edit_title').val(title);
                $('#edit_date').val(date);
                $('#edit_time').val(time);
                $('#edit_location').val(location);
                $('#edit_description').val(description);
                $('#current_image').attr('src', '../uploads/' + image);
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>