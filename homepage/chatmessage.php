<?php
// Include the database connection file
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['query'])) {
        $query = $_POST['query'];
        $stmt = $conn->prepare("SELECT response FROM chatbot_responses WHERE question_id = (SELECT id FROM chatbot_prompts WHERE question = ? LIMIT 1)");
        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $responses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($responses) {
            $response = $responses[array_rand($responses)]['response'];
            echo $response;
        } else {
            echo "Sorry, I don't understand that question.";
        }
        exit();
    } elseif (isset($_POST['get_questions'])) {
        $stmt = $conn->prepare("SELECT question FROM chatbot_prompts WHERE category = 'specific'");
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row['question'];
        }
        $stmt->close();
        echo json_encode($questions);
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="chatbot.css">
</head>

<body>
    <div class="container">
        <h2 class="text-center my-4">Chat with our Bot</h2>
        <div class="chat-container">
            <div class="row">
                <div class="col-md-8">
                    <button id="clear-btn" class="btn btn-danger mb-2">Clear</button>
                    <div class="chat-box" id="chat-box"></div>
                    <div class="input-group">
                        <input type="text" id="chat-input" class="chat-input form-control" placeholder="Type your message here...">
                        <div class="input-group-append">
                            <button id="send-btn" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <button id="show-questions-btn" class="btn btn-secondary mb-2">Show Questions</button>
                    <div class="question-list-container">
                        <ul class="question-list list-group" id="question-list" style="display: none;"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#send-btn').click(function() {
                var query = $('#chat-input').val();
                if (query.trim() !== '') {
                    $('#chat-box').append('<div class="chat-message user-message"><strong>You:</strong> ' + query + '</div>');
                    $('#chat-input').val('');

                    $.ajax({
                        url: 'chatmessage.php',
                        type: 'POST',
                        data: {
                            query: query
                        },
                        success: function(response) {
                            $('#chat-box').append('<div class="chat-message bot-message"><strong>Bot:</strong> ' + response + '</div>');
                            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                        }
                    });
                }
            });

            $('#chat-input').keypress(function(e) {
                if (e.which == 13) {
                    $('#send-btn').click();
                }
            });

            $('#show-questions-btn').click(function() {
                $.ajax({
                    url: 'chatmessage.php',
                    type: 'POST',
                    data: {
                        get_questions: true
                    },
                    success: function(response) {
                        var questions = JSON.parse(response);
                        var questionList = $('#question-list');
                        questionList.empty();
                        questions.forEach(function(question) {
                            questionList.append('<li class="list-group-item">' + question + '</li>');
                        });
                        questionList.show();
                    }
                });
            });

            $('#question-list').on('click', 'li', function() {
                var query = $(this).text();
                $('#chat-box').append('<div class="chat-message user-message"><strong>You:</strong> ' + query + '</div>');

                $.ajax({
                    url: 'chatmessage.php',
                    type: 'POST',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        $('#chat-box').append('<div class="chat-message bot-message"><strong>Bot:</strong> ' + response + '</div>');
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                    }
                });
            });

            // Clear chat box
            $('#clear-btn').click(function() {
                $('#chat-box').empty();
            });
        });
    </script>
</body>

</html>