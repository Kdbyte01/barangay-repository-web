<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Include the database connection file
include '../includes/db_connect.php';

// Fetch login history
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_search = isset($_GET['date_search']) ? $_GET['date_search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'login_time';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$sql = "SELECT lh.*, u.role FROM login_history lh JOIN users u ON lh.username = u.username WHERE lh.username LIKE ? AND lh.login_time LIKE ? ORDER BY $sort $order";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$date_search_param = "%$date_search%";
$stmt->bind_param("ss", $search_param, $date_search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="view_login_history.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h2 class="text-center">Login History</h2>
            <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <form method="GET" action="view_login_history.php" class="mb-3">
            <div class="input-group mb-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by username" value="<?php echo htmlspecialchars($search); ?>">
                <input type="date" name="date_search" class="form-control form-control-sm" value="<?php echo htmlspecialchars($date_search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>
        </form>
        <form method="POST" action="view_login_history.php" class="mb-3">
            <button class="btn btn-danger btn-sm" type="submit" name="clear_history"><i class="fas fa-trash-alt"></i> Clear History</button>
        </form>
        <div class="card">
            <div class="card-header bg-dark text-white">Login History</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th><a href="?search=<?php echo htmlspecialchars($search); ?>&date_search=<?php echo htmlspecialchars($date_search); ?>&sort=username&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Username</a></th>
                            <th>Role</th>
                            <th><a href="?search=<?php echo htmlspecialchars($search); ?>&date_search=<?php echo htmlspecialchars($date_search); ?>&sort=login_time&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Login Time</a></th>
                            <th><a href="?search=<?php echo htmlspecialchars($search); ?>&date_search=<?php echo htmlspecialchars($date_search); ?>&sort=logout_time&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Logout Time</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['role'] == 'admin' ? 'Treasurer' : 'Encoder'); ?></td>
                                <td><?php echo htmlspecialchars($row['login_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['logout_time']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>

<?php
// Handle clear history request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_history'])) {
    $sql = "DELETE FROM login_history";
    $conn->query($sql);
    header("Location: view_login_history.php");
    exit();
}

$stmt->close();
$conn->close();
?>