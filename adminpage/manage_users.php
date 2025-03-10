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
$error_message = "";

// Handle form submissions for adding, updating, and deleting users
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "../uploads/profiles/";
    $profile_image = null;
    $target_file = null;

    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == UPLOAD_ERR_OK) {
        $profile_image = basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $profile_image;
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
    }

    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $fullname = $_POST['fullname'];
        $age = $_POST['age'];
        $address = $_POST['address'];
        $gmail = $_POST['gmail'];
        $username = $_POST['username'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $role = $_POST['role'];

        if ($profile_image) {
            $sql = "UPDATE users SET fullname=?, age=?, address=?, gmail=?, username=?, password=?, role=?, profile_image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissssssi", $fullname, $age, $address, $gmail, $username, $password, $role, $profile_image, $user_id);
        } else {
            if ($password) {
                $sql = "UPDATE users SET fullname=?, age=?, address=?, gmail=?, username=?, password=?, role=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sisssssi", $fullname, $age, $address, $gmail, $username, $password, $role, $user_id);
            } else {
                $sql = "UPDATE users SET fullname=?, age=?, address=?, gmail=?, username=?, role=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sissssi", $fullname, $age, $address, $gmail, $username, $role, $user_id);
            }
        }

        $stmt->execute();
        $stmt->close();

        header("Location: manage_users.php");
        exit();
    }

    if (isset($_POST['add_user'])) {
        $fullname = $_POST['fullname'];
        $age = $_POST['age'];
        $address = $_POST['address'];
        $gmail = $_POST['gmail'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        // Check for duplicate username or email
        $sql = "SELECT * FROM users WHERE username=? OR gmail=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $gmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Duplicate found
            $error_message = "Username or Gmail already exists. Please choose another.";
        } else {
            // No duplicate found, proceed with insertion
            $sql = "INSERT INTO users (fullname, age, address, gmail, username, password, role, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissssss", $fullname, $age, $address, $gmail, $username, $password, $role, $profile_image);
            $stmt->execute();
            $stmt->close();

            header("Location: manage_users.php");
            exit();
        }
    }

    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        $sql = "DELETE FROM users WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_users.php");
        exit();
    }
}

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="manage_users.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Users</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="action-icons mb-4">
            <button class="btn btn-primary" id="addUserBtn"><i class="fas fa-plus"></i> Add User</button>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Hidden Form for Adding/Editing Users -->
        <div id="userForm" class="hidden">
            <form action="manage_users.php" method="post" enctype="multipart/form-data" id="userFormElement">
                <input type="hidden" id="userId" name="user_id">
                <div class="form-group">
                    <label for="fullname">Fullname:</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="age">Age:</label>
                    <input type="number" class="form-control" id="age" name="age" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="form-group">
                    <label for="gmail">Gmail Account:</label>
                    <input type="email" class="form-control" id="gmail" name="gmail" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="profile_image">Profile Image:</label>
                    <input type="file" class="form-control-file" id="profile_image" name="profile_image" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success" id="saveUserBtn"><i class="fas fa-save"></i> Save User</button>
                <button type="button" class="btn btn-secondary" id="cancelBtn"><i class="fas fa-times"></i> Cancel</button>
            </form>
        </div>

        <hr>

        <!-- Users Table -->
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Fullname</th>
                    <th>Age</th>
                    <th>Address</th>
                    <th>Gmail</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Profile Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['gmail']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo $row['profile_image'] ? "<img src='../uploads/profiles/" . htmlspecialchars($row['profile_image']) . "' alt='Profile Image' class='profile-pic'>" : "No Image"; ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm editBtn" data-id="<?php echo $row['id']; ?>" data-fullname="<?php echo htmlspecialchars($row['fullname']); ?>" data-age="<?php echo htmlspecialchars($row['age']); ?>" data-address="<?php echo htmlspecialchars($row['address']); ?>" data-gmail="<?php echo htmlspecialchars($row['gmail']); ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>" data-role="<?php echo htmlspecialchars($row['role']); ?>"><i class="fas fa-edit"></i> Edit</button>
                            <form action="manage_users.php" method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
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
            $('#addUserBtn').click(function() {
                $('#userForm').removeClass('hidden');
                $('#userId').val('');
                $('#fullname').val('');
                $('#age').val('');
                $('#address').val('');
                $('#gmail').val('');
                $('#username').val('');
                $('#password').val('');
                $('#role').val('');
                $('#profile_image').val('');
                $('#userFormElement').attr('action', 'manage_users.php');
                $('#saveUserBtn').attr('name', 'add_user');
            });

            $('#cancelBtn').click(function() {
                $('#userForm').addClass('hidden');
            });

            $('.editBtn').click(function() {
                $('#userForm').removeClass('hidden');
                $('#userId').val($(this).data('id'));
                $('#fullname').val($(this).data('fullname'));
                $('#age').val($(this).data('age'));
                $('#address').val($(this).data('address'));
                $('#gmail').val($(this).data('gmail'));
                $('#username').val($(this).data('username'));
                $('#password').val('');
                $('#role').val($(this).data('role'));
                $('#userFormElement').attr('action', 'manage_users.php');
                $('#saveUserBtn').attr('name', 'update_user');
            });

            $('#userFormElement').submit(function(event) {
                var isValid = true;
                $('#userFormElement input[required]').each(function() {
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