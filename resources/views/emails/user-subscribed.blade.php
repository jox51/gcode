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
            color: #4CAF50; /* A welcoming green */
        }
        p {
            font-size: 16px;
        }
        a.button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #4CAF50; /* Consistent green for positive actions */
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
        <h1>Welcome Aboard, {{$user->name}}!</h1>
        <p>Thank you for subscribing! We're excited to have you join our community. You've just taken the first step towards experiencing the best we have to offer.</p>
        <p>We encourage you to explore our features and get involved. If you have any questions or need assistance, our support team is just an email away.</p>
        <p><a href="mailto:support@gcode.cash" class="button">Contact Support</a></p>
        <p>Get ready to dive in and enjoy your new subscription!</p>
    </div>
</body>
</html>
