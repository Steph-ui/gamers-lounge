<?php
session_start();

if (isset($_GET['gamesubmit'])) {
    $gameuser = $_GET['gameuser'];
    $username = $_GET['username'];
}
?>

<?php
// Get the usernames from the URL parameters
$username = $_GET['username'] ?? 'Anonymous';
$gameuser = $_GET['gameuser'] ?? 'Anonymous';

// Define the dynamic chat log file based on usernames
$chat_log_file = $username . '_' . $gameuser . '_chat.txt';

// Check if a message is submitted via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['sender'])) {
    $message = trim($_POST['message']);
    $sender = $_POST['sender']; // Get the actual sender of the message
    if (!empty($message)) {
        $timestamp = date('Y-m-d H:i:sa');

        // Create the chat entry based on the sender's identity
        $chat_entry = "<div class='" . ($sender === $username ? 'user-message' : 'gameuser-message') . " message'>
                        <strong>{$sender}:</strong> {$message}<br><small>{$timestamp}</small></div>\n";

        // Save the message to the dynamic chat file
        file_put_contents($chat_log_file, $chat_entry, FILE_APPEND);
    }
    exit; // End execution after handling AJAX request
}

// Function to load chat messages (via AJAX request)
if (isset($_GET['load_chat'])) {
    if (file_exists($chat_log_file)) {
        echo nl2br(file_get_contents($chat_log_file)); // Read and display the chat log
    } else {
        echo '<p>No messages yet!</p>';
    }
    exit; // End execution after loading chat
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <style>
        hr {
            width: 80%; /* Set the width of the hr, smaller than 100% */
            margin: 20px auto; /* Center the hr and add vertical margin */
            border: none; /* Remove default border */
            height: 1px; /* Set the height of the line */
            background-color: #407342; /* Set the color of the hr */
        }
    
     .container {
          font-family: "Quicksand", system-ui;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            font-family: Arial, sans-serif;
        }

        .text {
            font-size: 15px;
            font-weight: bold;
            color:#407342;
            text-transform:uppercase;
            font-family: "Quicksand", system-ui;
        }

        .button-group {
            display: flex;
            gap: 15px; /* Space between buttons */
        }

        .btn {
             font-family: "Quicksand", system-ui;
            padding: 10px 10px;
            border: none;
            color: white;
            font-size: 16px;
            border-radius:15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-lost {
            background-color: #ff4c4c; /* Red for Lost */
        }

        .btn-lost:hover {
            background-color: #e63939; /* Darker red on hover */
        }

        .btn-won {
            background-color: #407342; /* Green for Won */
        }

        .btn-won:hover {
            background-color: #3e8e41; /* Darker green on hover */
        }
    a{
        color:white;
    }
        body {
            font-family: "Quicksand", system-ui;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .chat-container {
            width: 100%;
            height: 100vh;
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background-color: #407342;
            padding: 8px;
            color: white;
            text-align: right;
            font-size: 12px;
             text-decoration: underline;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .chat-box {
            height: 400px;
            padding: 15px;
            overflow-y: auto;
            border-bottom: 1px solid #ddd;
            background-color: #fafafa;
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
            font-size: 15px;
            line-height: 1.4;
        }
        .user-message {
            background-color: #e6ffe6;
            text-align: right;
        }
        .gameuser-message {
            background-color: #f1f1f1;
            text-align: left;
        }
        .chat-input {
            font-family: "Quicksand", system-ui;
            padding: 12px;
            border: 1px solid #ddd;
            width: calc(100% - 130px);
            border-radius: 30px;
            margin: 10px 0 10px 10px;
            font-size: 15px;
        }
        .send-btn {
                        font-family: "Quicksand", system-ui;
            background-color: #407342;
            color: white;
            padding: 12px 30px;
            border: none;
            cursor: pointer;
            border-radius: 30px;
            font-size: 15px;
            transition: background-color 0.3s ease;
        }
        .send-btn:hover {
            background-color: #e6ffe6;
              color: #407342;
        }
        form {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
        }
        @media (max-width: 600px) {
            .chat-box {
                height: 450px;
            }
            .send-btn {
                padding: 10px 20px;
            }
            .chat-input {
                font-size: 14px;
                width: calc(100% - 115px);
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script>
    var isUserScrolling = false;  // Flag to detect if the user is scrolling manually

    // Function to send message via AJAX
    function sendMessage() {
        var message = $("input[name='message']").val();
        var sender = $("input[name='sender']").val(); // Get the actual sender's identity

        if (message.trim() === '') return; // Don't send empty messages

        $.post('', {message: message, sender: sender}, function() {
            $("input[name='message']").val(''); // Clear input field after sending
            loadMessages(); // Refresh chat box with new message
        });
    }

    // Function to load messages via AJAX
    function loadMessages() {
        var chatBox = $('#chat-box');
        var scrollPosition = chatBox.scrollTop();
        var scrollHeight = chatBox.prop('scrollHeight');
        var containerHeight = chatBox.outerHeight();

        $.get('', {load_chat: 1}, function(data) {
            chatBox.html(data); // Load the chat log dynamically

            // Only scroll to the bottom if the user is not scrolling manually and is already near the bottom
            if (!isUserScrolling && (scrollPosition + containerHeight + 100 >= scrollHeight)) {
                chatBox.scrollTop(chatBox[0].scrollHeight); // Scroll to the bottom if near the bottom
            }
        });
    }

    // Detect when the user is scrolling
    $(document).ready(function() {
        var chatBox = $('#chat-box');

        // Detect scroll event
        chatBox.on('scroll', function() {
            var scrollPosition = chatBox.scrollTop();
            var scrollHeight = chatBox.prop('scrollHeight');
            var containerHeight = chatBox.outerHeight();

            // Set isUserScrolling to true when the user is scrolling up
            if (scrollPosition + containerHeight < scrollHeight) {
                isUserScrolling = true;
            }

            // If the user is back at the bottom, allow automatic scrolling again
            if (scrollPosition + containerHeight + 100 >= scrollHeight) {
                isUserScrolling = false;
            }
        });

        // Auto-refresh chat messages every 2 seconds
        setInterval(loadMessages, 2000);

        // Handle sending messages on button click or Enter key
        $('.send-btn').on('click', sendMessage);
        $("input[name='message']").on('keypress', function(e) {
            if (e.which === 13) { // Enter key pressed
                sendMessage();
                return false;
            }
        });

        loadMessages(); // Load messages on page load
    });
</script>

</head>
<body>
   

<div class="chat-container">
 <div class="header">
<p><a >go back</a></p>
    </div>
    <div class="chat-box" id="chat-box">
        <!-- Chat log will be dynamically loaded here via AJAX -->
    </div>
    <form method="POST" action="">
        <input type="hidden" name="sender" value="<?php echo $_SESSION['usname']; ?>"> <!-- Sender identity -->
        <input type="text" name="message" class="chat-input" placeholder="Type your message..." required>
        <button type="button" class="send-btn">Send</button>
    </form>
    
  
   <div class="container">
    <div class="text">
        Game Result
    </div>
   <form method="POST" action="redirect_2.php">
        <div class="button-group">
        <button type="submit" name="lost" value='LOST' class="btn btn-lost">Lost</button>
        <button type="submit" name="won" value='WON' class="btn btn-won">Won</button>
    </div>
   </form>
</div>
</div> 




</body>
</html>
