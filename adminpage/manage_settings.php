<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
// if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
//     header("Location: ../homepage/login.php");
//     exit();
// }

// Include the database connection file
include '../includes/db_connect.php';
$error_message = "";

// Handle form submissions for adding, updating, and deleting settings
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_setting'])) {
        $setting_id = $_POST['setting_id'];
        $setting_key = $_POST['setting_key'];
        $setting_value = $_POST['setting_value'];

        $sql = "UPDATE settings SET setting_key=?, setting_value=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $setting_key, $setting_value, $setting_id);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_settings.php");
        exit();
    }

    if (isset($_POST['add_setting'])) {
        $setting_key = $_POST['setting_key'];
        $setting_value = $_POST['setting_value'];

        // Check for duplicate setting key
        $sql = "SELECT * FROM settings WHERE setting_key=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $setting_key);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Duplicate found
            $error_message = "Setting key already exists. Please choose another.";
        } else {
            // No duplicate found, proceed with insertion
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $setting_key, $setting_value);
            $stmt->execute();
            $stmt->close();

            header("Location: manage_settings.php");
            exit();
        }
    }

    if (isset($_POST['delete_setting'])) {
        $setting_id = $_POST['setting_id'];

        $sql = "DELETE FROM settings WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $setting_id);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_settings.php");
        exit();
    }
}

// Fetch all settings
$sql = "SELECT * FROM settings";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Settings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="manage_settings.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Settings</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="action-icons mb-4">
            <button class="btn btn-primary" id="addSettingBtn"><i class="fas fa-plus"></i> Add Setting</button>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="transactionv2/financial_transaction/financial_transactionv2.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Transaction</a>
        </div>

        <!-- Hidden Form for Adding/Editing Settings -->
        <div id="settingForm" class="hidden">
            <form action="manage_settings.php" method="post" id="settingFormElement">
                <input type="hidden" id="settingId" name="setting_id">
                <div class="form-group">
                    <label for="setting_key">Setting Key:</label>
                    <input type="text" class="form-control" id="setting_key" name="setting_key" required>
                </div>
                <div class="form-group">
                    <label for="setting_value">Setting Value:</label>
                    <input type="text" class="form-control" id="setting_value" name="setting_value" required>
                </div>
                <button type="submit" class="btn btn-success" id="saveSettingBtn"><i class="fas fa-save"></i> Save Setting</button>
                <button type="button" class="btn btn-secondary" id="cancelBtn"><i class="fas fa-times"></i> Cancel</button>
            </form>
        </div>

        <hr>

        <!-- Settings Table -->
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Setting Key</th>
                    <th>Setting Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['setting_key']); ?></td>
                        <td><?php echo htmlspecialchars($row['setting_value']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm editBtn" data-id="<?php echo $row['id']; ?>" data-key="<?php echo htmlspecialchars($row['setting_key']); ?>" data-value="<?php echo htmlspecialchars($row['setting_value']); ?>"><i class="fas fa-edit"></i> Edit</button>
                            <form action="manage_settings.php" method="post" style="display:inline;">
                                <input type="hidden" name="setting_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_setting" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#addSettingBtn').click(function() {
                $('#settingForm').removeClass('hidden');
                $('#settingId').val('');
                $('#setting_key').val('');
                $('#setting_value').val('');
                $('#settingFormElement').attr('action', 'manage_settings.php');
                $('#saveSettingBtn').attr('name', 'add_setting');
            });

            $('#cancelBtn').click(function() {
                $('#settingForm').addClass('hidden');
            });

            $('.editBtn').click(function() {
                $('#settingForm').removeClass('hidden');
                $('#settingId').val($(this).data('id'));
                $('#setting_key').val($(this).data('key'));
                $('#setting_value').val($(this).data('value'));
                $('#settingFormElement').attr('action', 'manage_settings.php');
                $('#saveSettingBtn').attr('name', 'update_setting');
            });

            $('#settingFormElement').submit(function(event) {
                var isValid = true;
                $('#settingFormElement input[required]').each(function() {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>