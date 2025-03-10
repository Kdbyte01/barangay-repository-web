<?php
include '../includes/db_connect.php';

// Fetch carousel images
$sql = "SELECT file_path FROM carousel_images LIMIT 4";
$result = $conn->query($sql);

// Fetch files from the database
$sql_files = "SELECT id, file_name FROM files";
$result_files = $conn->query($sql_files);

// Fetch upcoming events from the database
$sql_events = "SELECT * FROM events WHERE date >= CURDATE()";
$result_events = $conn->query($sql_events);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Residents Portal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="chatbot.css">
    <link href="homepagev5.css" rel="stylesheet">
    <style>
        html,
        body {
            overflow: auto;
            /* Enable scrolling */
            scrollbar-width: none;
            /* For Firefox */
            -ms-overflow-style: none;
            /* For Internet Explorer and Edge */
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none;
            /* Hide scrollbar for Chrome, Safari, and Opera */
        }

        .scroll-sections {
            min-height: 100vh;
            /* Full screen size */
            padding: 50px 0;
            position: relative;
        }

        .scroll-section {
            min-height: 100vh;
            /* Full screen size */
            padding: 50px 0;
            position: relative;
        }

        .scroll-section:not(:first-child)::before {
            content: '';
            display: block;
            width: 100%;
            height: 5px;
            background-color: red;
            position: absolute;
            top: 0;
            left: 0;
        }

        .scroll-up-btn {
            position: fixed;
            bottom: 50px;
            right: 20px;
            display: none;
            z-index: 1000;
        }

        footer {
            background-color: #8b0000;
            color: white;
            padding: 20px;
            text-align: center;
            width: 100%;
            position: relative;
            /* Change to fixed */
            bottom: 0;
            left: 0;
        }

        .event-card {
            transition: transform 0.3s ease;
        }

        .event-card:hover {
            transform: scale(1.05);
        }

        .question-list-container {
            max-height: 300px;
            /* Adjust the height as needed */
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
        }

        .question-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .footer-design {
            background-color: white;
            color: black;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            border-radius: 5px;

        }

        .footer-design a {
            color: white;
            text-decoration: none;
        }

        .footer-design .btn {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .footer-design .btn:hover {
            background-color: #0056b3;
        }

        .stay-connected {
            background-color: #8b0000;
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            border-radius: 5px;
        }

        .stay-connected a {
            color: #007bff;
            text-decoration: none;
        }

        .stay-connected a:hover {
            text-decoration: underline;
        }

        .chat-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            cursor: pointer;
        }

        .chat-container {
            display: none;
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 300px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .chat-box {
            height: 200px;
            overflow-y: auto;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .user-message {
            text-align: right;
        }

        .bot-message {
            text-align: left;
        }

        .close-chat {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8b0000;">
        <a class="navbar-brand" href="#">BRGY Residents Portal</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="#home-section"><i class="fas fa-home"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#about-section"><i class="fas fa-info-circle"></i> About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#events-section"><i class="fas fa-calendar-alt"></i> Events</a></li>
                <li class="nav-item"><a class="nav-link" href="#services-section"><i class="fas fa-concierge-bell"></i> Services</a></li>
                <li class="nav-item"><a class="btn btn-danger text-white" href="login.php"><i class="fas fa-sign-in-alt"></i> Log In</a></li>
            </ul>
        </div>
    </nav>

    <?php include 'home_section.php'; ?>
    <?php include 'about_section.php'; ?>
    <?php include 'official_profile_section.php'; ?>
    <?php include 'events_section.php'; ?>
    <?php include 'services_section.php'; ?>

    <!-- Chat Button -->
    <img src="\uploads\messagelogo.png" class="chat-button chat-icon" id="chat-button" alt="Chat with us">

    <!-- Chat Container -->
    <div class="chat-container" id="chat-container">
        <button class="btn btn-danger close-chat" id="close-chat">X</button>
        <div class="row">
            <div class="col-md-8">
                <div class="chat-box" id="chat-box"></div>
                <div class="input-group">
                    <input type="text" id="chat-input" class="chat-input form-control" placeholder="Type your message here...">
                    <div class="input-group-append">
                        <button id="send-btn" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
                        <button id="clear-btn" class="btn btn-danger ml-2"><i class="fas fa-trash-alt"></i> Clear</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <button id="show-questions-btn" class="btn btn-secondary mb-2"><i class="fas fa-question-circle"></i> Show Questions</button>
                <div class="question-list-container">
                    <ul class="question-list list-group" id="question-list" style="display: none;"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Stay Connected Section -->
    <div class="footer-design">
        <h4>Stay Connected with Us</h4>
        <p>To receive the latest updates, please follow our Facebook page. Your support helps us serve you better!</p>
        <a href="https://www.facebook.com/profile.php?id=61561574998468&mibextid=LQQJ4dhttps://www.facebook.com/profile.php?id=61561574998468&mibextid=LQQJ4d" target="_blank" class="btn btn-primary"><i class="fab fa-facebook"></i> Follow Our Facebook Page</a>
    </div>

    <div class="stay-connected">
        <p>Contact us: <a href="mailto:info@barangaybulatok.com" style="color: #007bff;">info@barangaybulatok.com</a></p>
        <p>Address: <a href="https://www.google.com/maps/place/Zone+2+Malipayon,+Bulatok,+Pagadian+City,+7016" target="_blank" style="color: #007bff;">
                <span>&#128205;</span> Zone 2 Malipayon, Bulatok, Pagadian City, 7016</a></p>
        <p>&copy; 2024 Barangay Bulatok Portal. All rights reserved.</p>
    </div>


    <!-- Scroll Up Button -->
    <button class="btn btn-primary scroll-up-btn" id="scroll-up-btn"><i class="fas fa-arrow-up"></i> Scroll Up</button>

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

            // Clear chat box
            $('#clear-btn').click(function() {
                $('#chat-box').empty();
            });

            // Scroll Up Button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('#scroll-up-btn').fadeIn();
                } else {
                    $('#scroll-up-btn').fadeOut();
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

</html>