<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Include the database connection file
include '../includes/db_connect.php';

// Allowed file types
$allowed_file_types = ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf', 'txt', 'csv'];

$error_message = "";

// Handle form submissions for adding and deleting files
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "../uploads/files/";
    if (isset($_FILES["file"])) {
        $file_name = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = pathinfo($target_file, PATHINFO_EXTENSION);

        if (isset($_POST['add_file'])) {
            if (in_array($file_type, $allowed_file_types)) {
                if ($_FILES["file"]["error"] === UPLOAD_ERR_OK) {
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                        $sql = "INSERT INTO files (file_name) VALUES ('$file_name')";
                        if ($conn->query($sql) === TRUE) {
                            $error_message = "File uploaded successfully.";
                        } else {
                            $error_message = "Error inserting file into database: " . $conn->error;
                        }
                    } else {
                        $error_message = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error_message = interpretFileUploadError($_FILES["file"]["error"]);
                }
            } else {
                $error_message = "Invalid file type. Only DOC, DOCX, PPT, PPTX, XLS, XLSX, PDF, TXT, and CSV files are allowed.";
            }
        }
    } elseif (isset($_POST['delete_file'])) {
        $file_id = $_POST['file_id'];
        $sql = "SELECT file_name FROM files WHERE id=$file_id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $file_path = $target_dir . $row['file_name'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $sql = "DELETE FROM files WHERE id=$file_id";
            $conn->query($sql);
        }
    }
}

// Fetch all files
$sql = "SELECT * FROM files";
$result = $conn->query($sql);

function interpretFileUploadError($error_code)
{
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "The file you are trying to upload is too large. Please ensure it is within the allowed size limit.";
        case UPLOAD_ERR_PARTIAL:
            return "The file was only partially uploaded. Please try again.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded. Please select a file to upload.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "The server is missing a temporary folder. Please contact the administrator.";
        case UPLOAD_ERR_CANT_WRITE:
            return "There was an error writing the file to disk. Please try again.";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload. Please contact the administrator.";
        default:
            return "An unknown error occurred during file upload. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Files</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="manage_files.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Files</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="action-icons mb-4">
            <button class="btn btn-primary" id="toggleAddFileForm"><i class="fas fa-plus"></i> Add File</button>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Add File Form -->
        <div class="card mb-4 hidden" id="addFileForm">
            <div class="card-header">Add New File</div>
            <div class="card-body">
                <form id="addFileFormContent" action="manage_files.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file">Select File:</label>
                        <input type="file" class="form-control-file" id="file" name="file" required>
                    </div>
                    <p>Valid file types: DOC, DOCX, PPT, PPTX, XLS, XLSX, PDF, TXT, CSV</p>
                    <button type="submit" name="add_file" class="btn btn-success"><i class="fas fa-upload"></i> Add File</button>
                </form>
            </div>
        </div>

        <!-- Files Table -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Files</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['file_name']); ?></td>
                                <td>
                                    <form action="manage_files.php" method="post" style="display:inline;">
                                        <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_file" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
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
            $('#toggleAddFileForm').click(function() {
                $('#addFileForm').toggle();
                $('#addFileFormContent')[0].reset();
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>