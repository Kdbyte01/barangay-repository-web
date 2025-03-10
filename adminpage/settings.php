<?php
// filepath: /C:/xampp/htdocs/BarangayWeb/adminpage/settings.php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

$uploadDir = '../uploads/carousel/';
$errors = [];
$success = '';

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['carousel_image'])) {
    $uploadFile = $uploadDir . basename($_FILES['carousel_image']['name']);
    if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], $uploadFile)) {
        $sql = "INSERT INTO carousel_images (file_path, upload_date) VALUES (?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $uploadFile);
        $stmt->execute();
        $stmt->close();
        $success = 'Image uploaded successfully.';
    } else {
        $errors[] = 'Failed to upload image.';
    }
}

// Handle image update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_image']) && isset($_FILES['new_carousel_image'])) {
    $id = $_POST['update_image'];
    $uploadFile = $uploadDir . basename($_FILES['new_carousel_image']['name']);
    if (move_uploaded_file($_FILES['new_carousel_image']['tmp_name'], $uploadFile)) {
        $sql = "SELECT file_path FROM carousel_images WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($filePath);
        $stmt->fetch();
        $stmt->close();

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $sql = "UPDATE carousel_images SET file_path=?, upload_date=NOW() WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $uploadFile, $id);
        $stmt->execute();
        $stmt->close();
        $success = 'Image updated successfully.';
    } else {
        $errors[] = 'Failed to upload new image.';
    }
}

// Handle image deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "SELECT file_path FROM carousel_images WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $sql = "DELETE FROM carousel_images WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $success = 'Image deleted successfully.';
}

// Fetch existing images
$sql = "SELECT id, file_path FROM carousel_images LIMIT 4";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .card {
            border: 2px solid #343a40;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Manage Carousel Images</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        <form action="settings.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="carousel_image">Upload New Image</label>
                <input type="file" class="form-control-file" id="carousel_image" name="carousel_image" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        <hr>
        <h3>Existing Images</h3>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?php echo htmlspecialchars($row['file_path']); ?>" class="card-img-top" alt="Carousel Image">
                        <div class="card-body text-center">
                            <form action="settings.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="update_image" value="<?php echo $row['id']; ?>">
                                <div class="form-group">
                                    <input type="file" class="form-control-file" name="new_carousel_image" required>
                                </div>
                                <button type="submit" class="btn btn-warning">Update</button>
                                <a href="settings.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>