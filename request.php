<?php
if (isset($_POST['send_giftcard'])) {
    
    try {
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        $giftcard_coupon = $settings['giftcard_coupon_test'];
        if ($stripe_mode == "live") {
            $giftcard_coupon = $settings['giftcard_coupon_live'];
        }

        $stripe_session = [
            'customer_email' => htmlentities($_POST['sender']),
            'success_url' => $_SERVER['HTTP_ORIGIN'] . '/giftcard.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $settings['cancel_url'],
            'line_items' => [
                [
                    'price' => $giftcard_coupon,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'metadata' => $_POST
        ];
    
        $result = $stripe->checkout->sessions->create($stripe_session);
        header('Location: ' . htmlentities($result->url));
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle exception
        error_log('Error creating Stripe session: ' . $e->getMessage());
    }
    exit;


    /**
     * 1. check if recipient is an exsiting customer and retrieve customer id
     * 2. if new customer, create a customer from recipient email and retrieve customer id
     * 3. create a voucher for the recipient customer id
     * 4. create a charge against the gitfcard product, referencing the voucher id in the metadata
     *    use the giftcard.php as the success_url with SESSION_ID, and giftcard.php?cancel for cancel_url
     * 5. Upon success, retrieve session and voucher, then Send an email to recipient of their gift
     * 6. Show conformation to buyer/gifter, with a confirmation email.
     * 7. on cancel, delete the voucher.
     */
} else if (isset($_GET['session_id'])) {
    try {
        $session_id = $_GET['session_id'];

        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        /**
         * Retrieve session
         */
        $session = $stripe->checkout->sessions->retrieve(
            $session_id,
            []
        );

        $id = $session->id;
        $metadata = $session->metadata;
        $amount = $session->amount_total;
        $payment_intent = $session->payment_intent;

        /**
         * Create a coupon amount off
         * 
         * SEE OFFICIAL USER GUIDE CODE REFERENCE: MAX_REDEMPTIONS FOR PROMOTIONS
         */
        $coupon_session = [
            'amount_off' => $amount,
            'currency' => 'USD',
            'metadata' => [
                'sender' => $metadata->sender,
                'receiver' => $metadata->receiver,
                'message' => $metadata->message,
                'payment_intent' => $payment_intent
            ],
            'max_redemptions' => 1,
            'name' => 'Gift Card Coupon'
        ];

        $coupon = $stripe->coupons->create($coupon_session);

        /**
         * Update the payment intent for this session with the coupon
         */
        $stripe->paymentIntents->update(
            $payment_intent,
            ['metadata' => ['coupon' => $coupon->id]]
        );

        /**
         * Create a promotional code for the coupon and send the CODE parameter
         * to the receiver
         * 
         * SEE OFFICIAL USER GUIDE CODE REFERENCE: MAX_REDEMPTIONS FOR PROMOTIONS
         */
        $promotion = $stripe->promotionCodes->create([
            'coupon' => $coupon->id,
            'max_redemptions' => 1,
            'active' => true
        ]);

        /**
         * Update tracker
         */
        $stmt = $db->prepare('SELECT session_id,status,notes FROM tracker WHERE session_id=:session_id');
        $stmt->bindValue(':session_id', $session_id);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
    
        if ($row === false) {
            $stmt = $db->prepare('INSERT INTO tracker (session_id,status,notes) VALUES (:session_id,:status,:notes)');
            $stmt->bindValue(':session_id', $session_id);
            $stmt->bindValue(':status', 'Delivered');
            $stmt->bindValue(':notes', 'coupon created and sent');
            $stmt->execute();
        }

        // send coupon to $metadata->receive with the $metadata->message
        $stmt = $db->prepare('SELECT value FROM settings WHERE name=:name;');
        $stmt->bindValue(':name', 'giftcard_page_content');
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        $modifiedString = str_replace("{coupon_code}", $promotion->code, $row['value']);
        $modifiedString = str_replace("{amount}", number_format($amount/100,2), $modifiedString);
        $modifiedString = str_replace("{sender_email}", $metadata->sender, $modifiedString);
        $modifiedString = str_replace("{url}", $_SERVER['SERVER_NAME'], $modifiedString);
        
        // Send HTML mail
        $to = $metadata->receiver;
        $subject = $settings['giftcard_email_subject'];

        $headers  = "From: " . strip_tags($settings['noreply_email']) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($settings['fa-envelope']) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message = $modifiedString;

        // file deepcode ignore EmailContentInjection: not applicable
        if (mail($to, $subject, $message, $headers)) {
            error_log("mail sent successfully");
        } else {
            error_log("Failed to send email.");
        }

        /**
         * Additional optional processing
         * 
         * providing extra incentivizing processing, for example
         * when a purchase exceeds a minimum threshold, the extra.php
         * can automatically add the customer to a free services where normally
         * that service has a fee.
         */
        if (file_exists('extra.php')) {
            require('extra.php');
        }

        // Redirect to success.php with session_id
        header('Location: success.php?session_id=' . $session_id);
        exit;
    } catch(Exception $error) {
        error_log(json_encode($error));
    }
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'newsletter_subscribe') {
        /**
         * $_POST[newsletter_email] => inglepatrick@yahoo.com
         * 
         * 1. check if already a customer, 
         *    curl -G https://api.stripe.com/v1/customers/search -u "sk_test_..." --data-urlencode query="email:'$_POST[newsletter_email]'"
         * 2. if not a customer, create a new customer based on email, 
         *    curl https://api.stripe.com/v1/customers -u "sk_test_..." --data-urlencode email="$_POST[newsletter_email]"
         * 3. then from data->id, create a new entry in newsletter_subscriptions,
         *      INSERT INTO newsletter_subscriptions (stripe_customer_id) VALUES (:cusid);
         * no need to check if existing as the stripe_customer_id is UNIQUE to prevent duplicates
         */
        require "vendor/autoload.php";
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        $customer_email = htmlentities($_POST['newsletter_email']);
        $customer = $stripe->customers->search([
            'query' => 'email:\''.$customer_email.'\'',
        ]);
        $cusid = "";
        if (count($customer->data) > 0) {
            $cusid = $customer->data[0]->id;
        } else {
            $customer = $stripe->customers->create([
                'email' => $customer_email,
            ]);
            $cusid = $customer->data[0]->id;
        }
        if ($cusid !== "") {
            // if duplicate, will fall thru
            $stmt = $db->prepare("INSERT INTO newsletter_subscriptions (stripe_customer_id) VALUES (:cusid)");
            $stmt->bindValue(":cusid",$cusid,SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}

?>