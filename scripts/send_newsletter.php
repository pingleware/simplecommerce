<?php
require "vendor/autoload.php";
require "settings.php";
require "functions.php";

$stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
$stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
$stripe_secret = $stripe_test_secret;
$stripe_mode = $settings['stripe_mode'];
if ($stripe_mode == "live") {
    $stripe_secret = $stripe_live_secret;
}

$stripe = new \Stripe\StripeClient($stripe_secret);

$results = $db->query('SELECT * FROM newsletters');
$newsletters = [];

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    if ($row['distributed'] == 0 && $row['release_date'] == date("Y-m-d")) {
        $newsletters[] = $row;
    }
}
//echo '<pre>'; print_r($newsletters); echo '</pre>';

$stmt = $db->prepare("SELECT * FROM newsletter_subscriptions");
$result = $stmt->execute();
while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $customer = $stripe->customers->retrieve($row['stripe_customer_id'], []);
    $email = $customer->email;
    //print_r($email); 

    foreach($newsletters as $newsletter) {
        $content = json_decode(base64_decode($newsletter['content']));
        //print($content);

        // Send HTML mail
        $to = $email;
        $subject = "MY-BUY-IT-NOW.COM NEWSLETTER";

        $headers  = "From: " . strip_tags($settings['noreply_email']) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message = $content;

        // file deepcode ignore EmailContentInjection: not applicable
        if (mail($to, $subject, $message, $headers)) {
            $error_meesage = "mail sent successfully";
        } else {
            $error_message = "Failed to send email.";
        }

    }
}

// update distributed=1 to indicate newsletter was sent
foreach($newsletters as $newsletter) {
    $stmt = $db->prepare("UPDATE newsletters SET distributed=1 WHERE id=:id;");
    $stmt->bindValue(':id',$newsletter['id']);
    $stmt->execute();
}
/*
*/
?>