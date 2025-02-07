<?php
require 'settings.php';

if (isset($_POST['login'])) {
    $username = htmlentities($_POST['usrname']);
    // file deepcode ignore InsecureHash: SEE OFFICIAL USERS GUIDE REFERENCE INSECUREHASH 
    $password = htmlentities($_POST['psw']);

    // Lockout resolultion: SEE OFFICIAL USERS GUIDE REFERENCE #202501050904
    
    // Use prepared statements to prevent SQL injection
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();

    // Fetch the user from the database
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if (password_verify($password,$user['password'])) {
        $file = $user['file'];
        $_SESSION['is_admin'] = true;
        $_SESSION['user_id'] = $user['id']; // Optionally store user ID
        $_SESSION['user_email'] = $user['email']; // Optionally store email
        header("Location: " . htmlentities($file));
        exit;
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

            <form class="w3-container" action="/login.php" method="post">
                <div class="w3-section">
                <label><b>Username</b></label>
                <input class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Enter Username" name="usrname" required>
                <label><b>Password</b></label>
                <input class="w3-input w3-border" type="password" placeholder="Enter Password" name="psw" required>
                <button class="w3-button w3-block w3-green w3-section w3-padding" name="login" type="submit">Login</button>
                <input class="w3-check w3-margin-top" type="checkbox" checked="checked"> Remember me
                </div>
            </form>

            <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
                <a href="index.php" class="w3-button w3-red">Cancel</a>
                <span class="w3-right w3-padding w3-hide-small">Forgot <a href="#">password?</a></span>
            </div>
        </div>
    </body>
</html>