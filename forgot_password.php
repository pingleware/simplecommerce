<?php
require 'settings.php';
require 'connect.php';

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_var($_POST["username"], FILTER_SANITIZE_EMAIL);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(50)); // Generate secure token
        $hashedToken = password_hash($token, PASSWORD_BCRYPT);
        
        // Store token in the database with expiration time (1 hour)
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
        $stmt->execute([$hashedToken, $user['id']]);
        
        // Send reset link to user
        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
        $resetLink = "https://" . $host . "/reset_password.php?token=" . urlencode($token);
        
        // file deepcode ignore EmailContentInjection: not applicable
        $status = mail($email, "Password Reset", "Click the link to reset your password: $resetLink");
        if ($status) {
            $message = "A password reset link has been sent to your email.";
        } else {
            $message = "Problem sending email.";
        }
    } else {
        $message = "No user found with that username.";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo strtoupper($settings['sitename']); ?>: Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    </head>
    <body>
        <div class="w3-container w3-card-4 w3-animate-zoom">
            <div class="w3-center"><br>
                <img src="https://www.w3schools.com/w3css/img_avatar4.png" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top">
            </div>

            <form class="w3-container" method="post">
                <div class="w3-section"><?php echo $message; ?></div>
                <div class="w3-section">
                    <label><b>Username</b></label>
                    <input class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Enter Username" name="usrname" required>
                    <button class="w3-button w3-block w3-green w3-section w3-padding" name="reset_password" type="submit">Reset Password</button>
                </div>
            </form>
        </div>
    </body>
</html>
