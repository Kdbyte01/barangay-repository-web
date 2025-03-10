<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Bulatok</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="chatbot.css">
    <link href="homepagev3.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Barangay Bulatok</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="#home-section">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#about-section">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#events-section">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="#services-section">Services</a></li>
                <li class="nav-item"><a class="btn btn-danger text-white" href="login.php">Log In</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sections -->
    <?php include 'homepage/home_section.php'; ?>
    <?php include 'homepage/about_section.php'; ?>
    <?php include 'homepage/events_section.php'; ?>
    <?php include 'homepage/services_section.php'; ?>

    <!-- Chat Button -->
    <img src="\uploads\messagelogo.png" class="chat-button" id="chat-button" alt="Chat with us">

    <!-- Chat Container -->
    <div class="chat-container" id="chat-container">
        <button class="btn btn-danger close-chat" id="close-chat">X</button>
        <div class="row">
            <div class="col-md-8">
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
                <ul class="question-list list-group" id="question-list" style="display: none;"></ul>
            </div>
        </div>
    </div>

    <!-- Scroll Up Button -->
    <button class="btn btn-primary scroll-up-btn" id="scroll-up-btn">Scroll Up</button>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#chat-button').click(function() {
                $('#chat-container').show();
                $('#chat-button').hide();
            });

            $('#close-chat').click(function() {
                $('#chat-container').hide();
                $('#chat-button').show();
            });

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

            // Scroll Up Button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('#scroll-up-btn').fadeIn();
                } else {
                    $('#scroll-up-btn').fadeOut();
                }

                // Show footer when scrolled to the bottom
                if ($(window).scrollTop() + $(window).height() == $(document).height()) {
                    $('footer').show();
                } else {
                    $('footer').hide();
                }
            });

            $('#scroll-up-btn').click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 600);
                return false;
            });

            // Smooth scrolling for navbar links
            $('.navbar-nav a').on('click', function(event) {
                if (this.hash !== "") {
                    event.preventDefault();
                    var hash = this.hash;
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top
                    }, 800, function() {
                        window.location.hash = hash;
                    });
                }
            });
        });
    </script>
</body>

<footer>
    <p>&copy; 2023 Barangay Bulatok. All rights reserved.</p>
    <p>Contact us: <a href="mailto:info@barangaybulatok.com" style="color: white;">info@barangaybulatok.com</a></p>
    <p>Address: <a href="https://www.google.com/maps/place/Zone+2+Malipayon,+Bulatok,+Pagadian+City,+7016" target="_blank" style="color: white;">
            <span>&#128205;</span> Zone 2 Malipayon, Bulatok, Pagadian City, 7016</a></p>
</footer>

</html>