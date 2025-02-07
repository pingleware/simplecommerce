<?php

$content <<<END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Shopping at <?php echo strtoupper($settings['sitename']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333333;
        }

        p {
            color: #666666;
        }

        .download-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .download-links a {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            color: #ffffff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .download-links a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Download Shopping at <?php echo strtoupper($settings['sitename']); ?></h1>
        <p>Stay connected on the go with our app. Download now for Android, Apple (iOS and MacOS Universal), Windows, and Linux.</p>
        
        <div class="download-links">
            <a href="#" target="_blank">Download for Android</a>
            <a href="#" target="_blank">Download for iOS</a>
            <a href="#" target="_blank" title="for both Intel and Apple Silicon">Download for MacOS Universal</a>
            <a href="#" target="_blank" title="for both Intel and Arm">Download for Windows</a>
            <a href="#" target="_blank" title="for both Intel and ARM">Download for Linux</a>
        </div>
    </div>
</body>
</html>
END;

$content_textonlt <<<END
END;