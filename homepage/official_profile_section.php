<?php
include '../includes/db_connect.php';

// Fetch official profiles from the database
$sql = "SELECT * FROM brgy_officials";
$result = $conn->query($sql);
?>

<link rel="stylesheet" href="official_profile_section.css">

<div id="official-profiles-section" class="scroll-section">
    <div class="container mt-5">
        <h2 class="mb-4">Barangay Officials</h2>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <?php if ($row['profile_image']): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($row['profile_image']); ?>" class="card-img-top profile-pic" alt="Profile Image">
                        <?php else: ?>
                            <img src="default_profile.png" class="card-img-top profile-pic" alt="Default Profile Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'] . ' ' . $row['name_extension']); ?></h5>
                            <p class="card-text">Position: <?php echo htmlspecialchars($row['position']); ?></p>
                            <p class="card-text">Chairmanship: <?php echo htmlspecialchars($row['chairmanship']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>