<?php
include '../includes/db_connect.php';

// Fetch upcoming events from the database
$sql = "SELECT * FROM events WHERE date >= CURDATE()";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="events_section.css">

<div id="events-section" class="scroll-section">
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
</div>