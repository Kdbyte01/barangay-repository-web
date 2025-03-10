<?php
session_start();

// Include the database connection file
include '../../includes/db_connect.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $question = $_POST['question'];
        $responses = isset($_POST['responses']) ? $_POST['responses'] : [];
        $category = $_POST['category'];

        $stmt = $conn->prepare("INSERT INTO chatbot_prompts (question, category) VALUES (?, ?)");
        $stmt->bind_param("ss", $question, $category);
        $stmt->execute();
        $question_id = $stmt->insert_id;
        $stmt->close();

        foreach ($responses as $response) {
            $stmt = $conn->prepare("INSERT INTO chatbot_responses (question_id, response) VALUES (?, ?)");
            $stmt->bind_param("is", $question_id, $response);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $question = $_POST['question'];
        $responses = isset($_POST['responses']) ? $_POST['responses'] : [];
        $category = $_POST['category'];

        $stmt = $conn->prepare("UPDATE chatbot_prompts SET question = ?, category = ? WHERE id = ?");
        $stmt->bind_param("ssi", $question, $category, $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM chatbot_responses WHERE question_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        foreach ($responses as $response) {
            $stmt = $conn->prepare("INSERT INTO chatbot_responses (question_id, response) VALUES (?, ?)");
            $stmt->bind_param("is", $id, $response);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];

        // Delete related responses first
        $stmt = $conn->prepare("DELETE FROM chatbot_responses WHERE question_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Then delete the prompt
        $stmt = $conn->prepare("DELETE FROM chatbot_prompts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all prompts
$result = $conn->query("SELECT * FROM chatbot_prompts");
$prompts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="chatbot.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <a href="../admin_dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h2 class="text-center">Chatbot Management</h2>
        <form method="POST" action="chatbot.php">
            <div class="form-group">
                <label for="question">Question</label>
                <input type="text" class="form-control" id="question" name="question" required>
            </div>
            <div class="form-group">
                <label for="responses">Responses</label>
                <textarea class="form-control" id="responses" name="responses[]" rows="3" required></textarea>
                <button type="button" class="btn btn-secondary mt-2" id="add-response"><i class="fas fa-plus"></i> Add Another Response</button>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="general">General</option>
                    <option value="specific">Specific</option>
                </select>
            </div>
            <button type="submit" name="add" class="btn btn-success"><i class="fas fa-save"></i> Add</button>
        </form>

        <!-- Prompts List -->
        <div class="card mt-4">
            <div class="card-header bg-dark text-white">Prompts List</div>
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Question</th>
                            <th>Responses</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prompts as $prompt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prompt['question']); ?></td>
                                <td>
                                    <?php
                                    $stmt = $conn->prepare("SELECT response FROM chatbot_responses WHERE question_id = ?");
                                    $stmt->bind_param("i", $prompt['id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $responses = $result->fetch_all(MYSQLI_ASSOC);
                                    $stmt->close();
                                    foreach ($responses as $response) {
                                        echo htmlspecialchars($response['response']) . "<br>";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($prompt['category']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?php echo $prompt['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?php echo $prompt['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $prompt['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $prompt['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?php echo $prompt['id']; ?>">Edit Prompt</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?php echo $prompt['id']; ?>">
                                                <div class="form-group">
                                                    <label for="question<?php echo $prompt['id']; ?>">Question</label>
                                                    <input type="text" class="form-control" id="question<?php echo $prompt['id']; ?>" name="question" value="<?php echo htmlspecialchars($prompt['question']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="responses<?php echo $prompt['id']; ?>">Responses</label>
                                                    <?php
                                                    $stmt = $conn->prepare("SELECT response FROM chatbot_responses WHERE question_id = ?");
                                                    $stmt->bind_param("i", $prompt['id']);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $responses = $result->fetch_all(MYSQLI_ASSOC);
                                                    $stmt->close();
                                                    foreach ($responses as $response): ?>
                                                        <textarea class="form-control mt-2" name="responses[]" rows="3" required><?php echo htmlspecialchars($response['response']); ?></textarea>
                                                    <?php endforeach; ?>
                                                    <button type="button" class="btn btn-secondary mt-2" id="add-response-edit<?php echo $prompt['id']; ?>"><i class="fas fa-plus"></i> Add Another Response</button>
                                                </div>
                                                <div class="form-group">
                                                    <label for="category<?php echo $prompt['id']; ?>">Category</label>
                                                    <select class="form-control" id="category<?php echo $prompt['id']; ?>" name="category">
                                                        <option value="general" <?php echo $prompt['category'] == 'general' ? 'selected' : ''; ?>>General</option>
                                                        <option value="specific" <?php echo $prompt['category'] == 'specific' ? 'selected' : ''; ?>>Specific</option>
                                                    </select>
                                                </div>
                                                <button type="submit" name="edit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
            $('#add-response').click(function() {
                $('#responses').after('<textarea class="form-control mt-2" name="responses[]" rows="3" required></textarea>');
            });
            <?php foreach ($prompts as $prompt): ?>
                $('#add-response-edit<?php echo $prompt['id']; ?>').click(function() {
                    $(this).before('<textarea class="form-control mt-2" name="responses[]" rows="3" required></textarea>');
                });
            <?php endforeach; ?>
        });
    </script>
</body>

</html>