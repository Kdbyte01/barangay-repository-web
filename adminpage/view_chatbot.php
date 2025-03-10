<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../homepage/login.php");
    exit();
}

include '../includes/db_connect.php';

// Fetch chatbot prompts and their responses
$sql = "SELECT p.id, p.question, p.category, r.response 
        FROM chatbot_prompts p 
        LEFT JOIN chatbot_responses r ON p.id = r.question_id";
$result = $conn->query($sql);

$prompts = [];
while ($row = $result->fetch_assoc()) {
    $prompts[$row['id']]['question'] = $row['question'];
    $prompts[$row['id']]['category'] = $row['category'];
    $prompts[$row['id']]['responses'][] = $row['response'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="view_chatbot.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h3 class="mb-4 text-center">Chatbot Management</h3>
        <div class="card">
            <div class="card-header bg-dark text-white">Chatbot Prompts</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Question</th>
                            <th>Responses</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 0;
                        foreach ($prompts as $prompt):
                            if ($count >= 5) break;
                            $count++;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prompt['question']); ?></td>
                                <td>
                                    <?php foreach ($prompt['responses'] as $response): ?>
                                        <?php echo htmlspecialchars($response) . "<br>"; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td><?php echo htmlspecialchars($prompt['category']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="proceed-btn">
                    <button id="proceedToEditChatbot" class="btn btn-primary"><i class="fas fa-edit"></i> Proceed to Edit</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#proceedToEditChatbot').click(function() {
                window.location.href = 'chatbot/chatbot.php';
            });
        });
    </script>
</body>

</html>