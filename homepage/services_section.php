<?php
include '../includes/db_connect.php';

// Fetch files from the database
$sql = "SELECT id, file_name FROM files";
$result = $conn->query($sql);
?>

<div id="services-section" class="scroll-section">
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
</div>