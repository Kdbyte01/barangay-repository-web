<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

$error_message = '';
$message = '';

// Handle form submission for adding or editing an official
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_official'])) {
    $id = $_POST['id'];
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'] ?? '';
    $name_extension = $_POST['name_extension'] ?? '';
    $position = $_POST['position'];
    $chairmanship = $_POST['chairmanship'] ?? '';

    // Check if a new profile image is uploaded
    if (!empty($_FILES['profile_image']['name'])) {
        $profile_image = $_FILES['profile_image']['name'];
        $target_dir = "../uploads/profiles/";
        $target_file = $target_dir . basename($profile_image);
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);

        if ($id) {
            $sql = "UPDATE brgy_officials SET lastname=?, firstname=?, middlename=?, name_extension=?, position=?, chairmanship=?, profile_image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $lastname, $firstname, $middlename, $name_extension, $position, $chairmanship, $profile_image, $id);
        } else {
            $sql = "INSERT INTO brgy_officials (lastname, firstname, middlename, name_extension, position, chairmanship, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $lastname, $firstname, $middlename, $name_extension, $position, $chairmanship, $profile_image);
        }
    } else {
        if ($id) {
            $sql = "UPDATE brgy_officials SET lastname=?, firstname=?, middlename=?, name_extension=?, position=?, chairmanship=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $lastname, $firstname, $middlename, $name_extension, $position, $chairmanship, $id);
        } else {
            $sql = "INSERT INTO brgy_officials (lastname, firstname, middlename, name_extension, position, chairmanship) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $lastname, $firstname, $middlename, $name_extension, $position, $chairmanship);
        }
    }

    if ($stmt->execute()) {
        $message = $id ? "Official updated successfully" : "Official added successfully";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle deletion of an official
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM brgy_officials WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: officialProfile.php");
    exit();
}

$sql = "SELECT * FROM brgy_officials";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Official Profiles</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="officialProfile.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <h2 class="text-center">Barangay Officials</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="action-icons mb-4">
            <button class="btn btn-primary" id="addOfficialBtn"><i class="fas fa-plus"></i> Add Official</button>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Hidden Form for Adding/Editing Officials -->
        <div id="officialForm" class="hidden">
            <form action="officialProfile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="officialId" name="id">
                <input type="hidden" name="edit_official" value="1">
                <div class="form-group">
                    <label for="lastname">Lastname:</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="firstname">Firstname:</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="middlename">Middlename:</label>
                    <input type="text" class="form-control" id="middlename" name="middlename">
                </div>
                <div class="form-group">
                    <label for="name_extension">Name Extension:</label>
                    <input type="text" class="form-control" id="name_extension" name="name_extension">
                </div>
                <div class="form-group">
                    <label for="position">Position:</label>
                    <select class="form-control" id="position" name="position" required>
                        <option value="Brgy. Captain">Brgy. Captain</option>
                        <option value="Councilor">Councilor</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="Secretary">Secretary</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="chairmanship">Chairmanship:</label>
                    <input type="text" class="form-control" id="chairmanship" name="chairmanship">
                </div>
                <div class="form-group">
                    <label for="profile_image">Profile Image:</label>
                    <input type="file" class="form-control-file" id="profile_image" name="profile_image" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Official</button>
                <button type="button" class="btn btn-secondary" id="cancelBtn"><i class="fas fa-times"></i> Cancel</button>
            </form>
        </div>

        <hr>

        <!-- Official Profiles Table -->
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Profile</th>
                    <th>LastName</th>
                    <th>FirstName</th>
                    <th>Position</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($row['profile_image']): ?>
                                <img src="../uploads/profiles/<?php echo htmlspecialchars($row['profile_image']); ?>" class="profile-pic" alt="Profile Image">
                            <?php else: ?>
                                <img src="default_profile.png" class="profile-pic" alt="Default Profile Image">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm editBtn" data-id="<?php echo $row['id']; ?>" data-lastname="<?php echo htmlspecialchars($row['lastname']); ?>" data-firstname="<?php echo htmlspecialchars($row['firstname']); ?>" data-middlename="<?php echo htmlspecialchars($row['middlename']); ?>" data-name_extension="<?php echo htmlspecialchars($row['name_extension']); ?>" data-position="<?php echo htmlspecialchars($row['position']); ?>" data-chairmanship="<?php echo htmlspecialchars($row['chairmanship']); ?>"><i class="fas fa-edit"></i> Edit</button>
                            <a href="officialProfile.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#addOfficialBtn').click(function() {
                $('#officialForm').removeClass('hidden');
                $('#officialId').val('');
                $('#lastname').val('');
                $('#firstname').val('');
                $('#middlename').val('');
                $('#name_extension').val('');
                $('#position').val('');
                $('#chairmanship').val('');
                $('#profile_image').val('');
            });

            $('#cancelBtn').click(function() {
                $('#officialForm').addClass('hidden');
            });

            $('.editBtn').click(function() {
                $('#officialForm').removeClass('hidden');
                $('#officialId').val($(this).data('id'));
                $('#lastname').val($(this).data('lastname'));
                $('#firstname').val($(this).data('firstname'));
                $('#middlename').val($(this).data('middlename'));
                $('#name_extension').val($(this).data('name_extension'));
                $('#position').val($(this).data('position'));
                $('#chairmanship').val($(this).data('chairmanship'));
            });
        });
    </script>
</body>

</html>