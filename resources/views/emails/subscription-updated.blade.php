<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #D32F2F; /* A subtle red */
        }
        p {
            font-size: 16px;
        }
        a.button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #4CAF50; /* Green to indicate action */
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a.button:hover {
            background-color: #388E3C; /* Darker green for hover effect */
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>Your Subscription Has Been Updated, {{$user->name}}</h1>
        <p>We wanted to let you know that there has been a change to your subscription status. Please check your account details for more information.</p>
        <p>If this was an error or if you have any questions, we are here to help. Feel free to email us directly at:</p>
        <p><a href="mailto:support@gcode.cash" class="button">Email Support</a></p>
        <p>Thank you for being a valued member of our community.</p>
    </div>
</body>
</html>
