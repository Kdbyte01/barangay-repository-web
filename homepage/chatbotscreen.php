<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Screen</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="chatbotscreen.css">
</head>

<body>

    <div class="container mt-5">
        <div class="parent">
            <div class="div1">
                <button id="clear-btn" class="btn btn-danger mb-2">Clear</button>
                <div class="chat-box" id="chat-box"></div>
            </div>
            <div class="div2">
                <div class="input-group">
                    <input type="text" id="chat-input" class="chat-input form-control" placeholder="Type your message here...">
                    <div class="input-group-append">
                        <button id="send-btn" class="btn btn-primary">Send</button>
                    </div>
                </div>
            </div>
            <div class="div3">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle mb-2" type="button" id="show-questions-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Show Questions
                    </button>
                    <div class="dropdown-menu" aria-labelledby="show-questions-btn">
                        <div class="question-list-container">
                            <ul class="question-list list-group" id="question-list"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div4">
                <h5>Contact Us</h5>
                <form id="contact-form">
                    <div class="form-group">
                        <label for="contact-name">Name:</label>
                        <input type="text" class="form-control" id="contact-name" name="contact-name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-email">Email:</label>
                        <input type="email" class="form-control" id="contact-email" name="contact-email" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-question">Question:</label>
                        <textarea class="form-control" id="contact-question" name="contact-question" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

            // Handle contact form submission
            $('#contact-form').submit(function(event) {
                event.preventDefault();
                var name = $('#contact-name').val();
                var email = $('#contact-email').val();
                var question = $('#contact-question').val();

                $.ajax({
                    url: 'contact.php',
                    type: 'POST',
                    data: {
                        name: name,
                        email: email,
                        question: question
                    },
                    success: function(response) {
                        alert('Your message has been sent successfully!');
                        $('#contact-form')[0].reset();
                    }
                });
            });
        });
    </script>
</body>

</html>