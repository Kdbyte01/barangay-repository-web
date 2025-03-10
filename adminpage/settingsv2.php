<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barangay_name = $_POST['barangay_name'];
    $barangay_address = $_POST['barangay_address'];
    $barangay_city = $_POST['barangay_city'];
    $barangay_province = $_POST['barangay_province'];
    $barangay_treasurer = $_POST['barangay_treasurer'];
    $province_no = $_POST['province_no'];
    $scki_no = $_POST['scki_no'];
    $barangay_encoder = $_POST['barangay_encoder'];

    $settings = [
        'barangay_name' => $barangay_name,
        'barangay_address' => $barangay_address,
        'barangay_city' => $barangay_city,
        'barangay_province' => $barangay_province,
        'barangay_treasurer' => $barangay_treasurer,
        'province_no' => $province_no,
        'scki_no' => $scki_no,
        'barangay_encoder' => $barangay_encoder
    ];

    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
        $stmt->close();
    }

    $message = "Settings saved successfully.";
}

$sql = "SELECT setting_key, setting_value FROM settings";
$result = $conn->query($sql);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Settings</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="barangay_name">Barangay Name:</label>
                <input type="text" class="form-control" id="barangay_name" name="barangay_name" value="<?php echo $settings['barangay_name'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="barangay_address">Barangay Address:</label>
                <input type="text" class="form-control" id="barangay_address" name="barangay_address" value="<?php echo $settings['barangay_address'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="barangay_city">Barangay City:</label>
                <input type="text" class="form-control" id="barangay_city" name="barangay_city" value="<?php echo $settings['barangay_city'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="barangay_province">Barangay Province:</label>
                <input type="text" class="form-control" id="barangay_province" name="barangay_province" value="<?php echo $settings['barangay_province'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="barangay_treasurer">Barangay Treasurer:</label>
                <input type="text" class="form-control" id="barangay_treasurer" name="barangay_treasurer" value="<?php echo $settings['barangay_treasurer'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="province_no">Province No:</label>
                <input type="text" class="form-control" id="province_no" name="province_no" value="<?php echo $settings['province_no'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="scki_no">SCKI No:</label>
                <input type="text" class="form-control" id="scki_no" name="scki_no" value="<?php echo $settings['scki_no'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="barangay_encoder">Barangay Encoder:</label>
                <input type="text" class="form-control" id="barangay_encoder" name="barangay_encoder" value="<?php echo $settings['barangay_encoder'] ?? ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</body>

</html>