<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

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
    <link href="view_official_profiles.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5 content">
        <h3 class="mb-4 text-center">Barangay Official Profiles</h3>
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Profile</th>
                    <th>LastName</th>
                    <th>FirstName</th>
                    <th>Position</th>
                    <th>Chairmanship</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($row['profile_image']): ?>
                                <img src="../uploads/profiles/<?php echo htmlspecialchars($row['profile_image']); ?>" class="table-profile-pic" alt="Profile Image">
                            <?php else: ?>
                                <img src="default_profile.png" class="table-profile-pic" alt="Default Profile Image">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><?php echo htmlspecialchars($row['chairmanship']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="proceed-btn">
            <button class="btn btn-primary" id="proceedToEdit"><i class="fas fa-edit"></i> Proceed to Edit</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEdit').click(function() {
                window.location.href = 'officialProfile.php';
            });
        });
    </script>
</body>

</html>