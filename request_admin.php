<?php
require("functions_admin.php");

// Add a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = htmlspecialchars($_POST['name']);
        $price = (float) $_POST['price'];
        $image = null;
        $category_id = $_POST['category_id'];
        $tag = $_POST['tag'];
        $homepage = $_POST['homepage'];
        $tax_behavior = htmlentities($_POST['tax_behavior']);

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = 'images/' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $image = $imagePath;
            }
        }

        $stripe_id = addItemToStripe("usd",$price * 100,urlencode($name),$tax_behavior);

        $stmt = $db->prepare('INSERT INTO products (name, price, image, category_id, tag, homepage, stripe_id) VALUES (:name, :price, :image, :category_id, :tag, :homepage, :stripe_id)');
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':image', $image);
        $stmt->bindValue(':category_id', $category_id);
        $stmt->bindValue(':tag', $tag);
        $stmt->bindValue(':homepage', $homepage);
        $stmt->bindValue(':stripe_id', $stripe_id);
        $stmt->execute();
    } else if ($_POST['action'] == 'order_notification_tracking') {
        require "settings.php";
        require "vendor/autoload.php";
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        $session = $stripe->checkout->sessions->retrieve($_POST['order-notification-sessionid'],[]);

        $to = $session->customer_email;
        $subject = $settings['sitename'] . " ORDER NOTIFICATION";

        $headers  = "From: " . strip_tags($settings['noreply_email']) . "\r\n";
        $headers .= "To: " . strip_tags($to) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Notification</title>
  <style>
    /* Inline CSS for email compatibility */
    body {
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
      font-family: Arial, sans-serif;
      color: #333;
    }
    .email-container {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      padding: 20px;
      border: 1px solid #dddddd;
    }
    .header {
      text-align: center;
      padding-bottom: 20px;
      border-bottom: 1px solid #dddddd;
    }
    .header h1 {
      margin: 0;
      color: #0073e6;
    }
    .content {
      padding: 20px 0;
    }
    .order-details {
      width: 100%;
      border-collapse: collapse;
    }
    .order-details th,
    .order-details td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #dddddd;
    }
    .footer {
      text-align: center;
      font-size: 12px;
      color: #777777;
      padding-top: 20px;
      border-top: 1px solid #dddddd;
    }
    @media only screen and (max-width: 600px) {
      .email-container {
        width: 100% !important;
      }
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <h1>Order Notification</h1>
    </div>
    <div class="content">
      <p>Dear Valued Customer,</p>
      <p>Thank you for your order! Please find the details of your shipment below:</p>
      <table class="order-details">
        <tr>
          <th>Service Type</th>
          <td>'.$_POST['order-notification-svctype'].'</td>
        </tr>
        <tr>
          <th>Package Type</th>
          <td>'.$_POST['order-notification-pkgtype'].'</td>
        </tr>
        <tr>
          <th>Tracking Number</th>
          <td>'.$_POST['order-notification-trackingno'].'</td>
        </tr>
      </table>
      <p>You can use the tracking number above to monitor your shipment’s progress. If you have any questions or require further assistance, please do not hesitate to contact our customer support.</p>
      <p>Thank you for choosing our service!</p>
      <p>Sincerely,<br>'.$settings['sitename'].'</p>
    </div>
    <div class="footer">
      <p>If you need further assistance, simply reply to <b>'.$settings['contact-email'].'</b> or call our support hotline.</p>
    </div>
  </div>
</body>
</html>';

        // file deepcode ignore EmailContentInjection: not applicable
        if (mail($to, $subject, $message, $headers)) {
            $error_meesage = "mail sent successfully";
        } else {
            $error_message = "Failed to send email.";
        }
        error_log($error_message);
    } else if ($_POST['action'] == 'usps_clicknship_export') {
        require "settings.php";
        require "vendor/autoload.php";
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        $session = $stripe->checkout->sessions->retrieve($_POST['session_id'],[]);
    
        $label = [
            "Reference ID" => $_POST["customer"],
            "Shipping Date" => date("m/d/Y"),
            "Item Description" => "",
            "Item Quantity" => "",
            "Item Weight (lb)" => $_POST["usps_pkgwgtlb"],
            "Item Weight (oz)" => $_POST["usps_pkgwgtoz"],
            "Item Value" => "",
            "HS Tariff #" => "",
            "Country of Origin" => "US",
            "Sender First Name" => $_POST["usps_firstname"],
            "Sender Middle Initial" => $_POST["usps_middleinitial"],
            "Sender Last Name" => $_POST["usps_lastname"],
            "Sender Company/Org Name" => "",
            "Sender Address Line 1" => $_POST["usps_addressline1"],
            "Sender Address Line 2" => $_POST["usps_addressline2"],
            "Sender Address Line 3" => "",
            "Sender Address Town/City" => $_POST["usps_addresstowncity"],
            "Sender State" => $_POST["usps_state"],
            "Sender Country" => "US",
            "Sender ZIP Code" => $_POST["usps_zipcode"],
            "Sender Urbanization Code" => "",
            "Ship From Another ZIP Code" => "",
            "Sender Email" => $settings["contact-email"],
            "Sender Cell Phone" => "",
            "Recipient Country" => $session->customer_details->address->country,
            "Recipient First Name" => $session->customer_details->name,
            'Recipient Middle Initial',
            "Recipient Last Name" => $session->customer_details->name,
            "Recipient Company/Org Name" => "",
            "Recipient Address Line 1" => $session->customer_details->address->line1,
            "Recipient Address Line 2" => $session->customer_details->address->line2,
            "Recipient Address Line 3" => "",
            "Recipient Address Town/City" => $session->customer_details->address->city,
            "Recipient Province" => "",
            "Recipient State" => $session->customer_details->address->state,
            "Recipient ZIP Code" => $session->customer_details->address->postal_code,
            "Recipient Urbanization Code" => "",
            "Recipient Phone" => "",
            "Recipient Email" => $session->customer_email,
            "Service Type" => $_POST["usps_svctype"],
            "Package Type" => $_POST["usps_pkgtype"],
            "Package Weight (lb)" => $_POST["usps_pkgwgtlb"],
            "Package Weight (oz)" => $_POST["usps_pkgwgtoz"],
            "Length" => $_POST["usps_length"],
            "Width" => $_POST["usps_width"],
            "Height" => $_POST["usps_height"],
            "Girth" => $_POST["usps_girth"],
            "Insured Value" => "",
            "Contents" => "",
            "Contents Description" => "",
            "Package Comments" => "",
            "Customs Form Reference #" => "",
            "License #" => "",
            "Certificate #" => "",
            "Invoice #" => $session['invoice']
        ];

        $f = fopen('php://memory', 'w'); 
        fputcsv($f, array_keys($label), ",");
        fputcsv($f, $label, ","); 
        // reset the file pointer to the start of the file
        fseek($f, 0);
        // tell the browser it's going to be a csv file
        header('Content-Type: text/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="usps.csv";');
        // make php send the generated csv lines to the browser
        fpassthru($f);
        //header("location: admin.php");
        exit;
    } else if ($_POST['action'] === 'save_settings' ) {
        // Remove the 'action' key from $_POST
        unset($_POST['action']);

        foreach($_POST as $key => $value) {
            if (!str_contains($key, "_description")) {
                if (str_contains($key, "stripe_") && $key !== "stripe_mode") {
                    $keyPath = getSSHKeysPath();
                    $publicKey = getSSHKey($keyPath[1]);
                    $privateKey = getSSHKey($keyPath[0]);
                    if (!isBase64($value)) {
                        // if stripe keys are base64, they are already encoded and no changes made.
                        // no need to re-encode or problems arise
                        $stripeApiKey = $value;
                        $value = base64_encode(encrypt($stripeApiKey));    
                    }
                }
                $stmt = $db->prepare('UPDATE settings SET value=:value WHERE name=:name');
                $stmt->bindValue(':name',$key);
                $stmt->bindValue(':value',$value);
                $stmt->execute();
            }
        }

        // Include settings.php
        require 'settings.php';
    } else if ($_POST['action'] === 'update_order_status') {
        $stmt = $db->prepare('UPDATE tracker SET status=:status,notes=:notes WHERE id=:id');
        $stmt->bindValue(':id',$_POST['order-id']);
        $stmt->bindValue(':status', $_POST['order-status']);
        $stmt->bindValue(':notes', $_POST['order-notes'].'<br/>');
        $stmt->execute();
    } else if ($_POST['action'] === 'add_user') {
        if (isset($_FILES['user-file']) && $_FILES['user-file']['error'] === UPLOAD_ERR_OK) {
            $imagePath = basename($_FILES['user-file']['name']);
            if (file_exists($imagePath) === false) {
                if (move_uploaded_file($_FILES['user-file']['tmp_name'], $imagePath)) {
                    $image = $imagePath;
                }    
            } else {
                $image = $imagePath;
            }
        }

        $password = password_hash(htmlentities($_POST['password']), PASSWORD_BCRYPT);

        $stmt = $db->prepare('INSERT INTO users (name,email,password,is_approved,file) VALUES (:name,:email,:password,:is_admin,:file)');
        $stmt->bindValue(':name', htmlentities($_POST['username']));
        $stmt->bindValue(':email', htmlentities($_POST['email']));
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':is_admin', htmlentities($_POST['is_admin']));
        $stmt->bindValue(':file', htmlentities($_FILES['user-file']['name']));
        $stmt->execute();
    } else if ($_POST['action'] === 'update_user') {
        $system_files = [
            "email.php",
            "index.php",
            "cart.php",
            "checkout.php",
            "admin.php",
            "product.php",
            "success.php",
            "cancel.php",
            "functions.php",
            "stores.php",
            "shipping.php",
            "payment.php",
            "giftcard.php",
            "return.php",
            "faqs.php",
            "settings.php",
            "about.php",
            "connect.php",
            "setup.php",
            "login.php",
            "ajax.php",
            "logout.php",
            "functions_admin.php",
            "footer.php",
            "header.php",
            "request_admin.php",
            "request.php",
            "unsubscribe.php",
            "search.php",
            "error.php",
            "download.php",
            "sitemap.php",
            "api.php",
        ];

        $stmt = $db->prepare("SELECT file FROM users WHERE id=:id");
        $stmt->bindValue(':id',htmlentities($_POST['id']));
        $result = $stmt->execute();

        $user = $result->fetchArray(SQLITE3_ASSOC);
        if (isset($user['file']) && !empty($user['file'])) {
            unlink(basename($user['file']));
        }

        if (isset($_FILES['user-file']) && $_FILES['user-file']['error'] === UPLOAD_ERR_OK && !in_array(htmlentities($_POST['file']),$system_files)) {
            $imagePath = basename($_FILES['user-file']['name']);
            if (move_uploaded_file($_FILES['user-file']['tmp_name'], $imagePath)) {
                $image = $imagePath;
            }
        }

        $password = password_hash(htmlentities($_POST['password']), PASSWORD_BCRYPT);

        $stmt = $db->prepare('UPDATE users SET name=:username,email=:email,is_approved=:is_admin,file=:file WHERE id=:id');
        $stmt->bindValue(":id", htmlentities($_POST['id']));
        $stmt->bindValue(":username", htmlentities($_POST['username']));
        $stmt->bindValue(":email", htmlentities($_POST['email']));
        $stmt->bindValue(":is_admin", htmlentities($_POST['is_admin']));
        $stmt->bindValue(":file", htmlentities($_FILES['user-file']['name']));
        $stmt->execute();
    } else if ($_POST['action'] === 'delete_user') {
        $system_files = [
            "email.php",
            "index.php",
            "cart.php",
            "checkout.php",
            "admin.php",
            "product.php",
            "success.php",
            "cancel.php",
            "functions.php",
            "stores.php",
            "shipping.php",
            "payment.php",
            "giftcard.php",
            "return.php",
            "faqs.php",
            "settings.php",
            "about.php",
            "connect.php",
            "setup.php",
            "login.php",
            "ajax.php",
            "logout.php",
            "functions_admin.php",
            "footer.php",
            "header.php",
            "request_admin.php",
            "request.php",
            "unsubscribe.php",
            "search.php",
            "error.php",
            "download.php",
            "sitemap.php",
            "api.php",
        ];

        if (htmlentities($_POST['id']) !== 1) {
            $stmt = $db->prepare('DELETE FROM users WHERE id=:id');
            $stmt->bindValue(':id',htmlentities($_POST['id']));
            $stmt->execute();
            if (file_exists(htmlentities($_POST['file'])) && !in_array(htmlentities($_POST['file']),$system_files)) {
                unlink(basename($_POST['file']));
            }    
        }
    } else if ($_POST['action'] === 'change_user_password') {
        $id = htmlentities($_POST['id']);
        $current_password = htmlentities($_POST['current-password']);
        $new_password = htmlentities($_POST['new-password']);
        $repeat_password = htmlentities($_POST['repeat-password']);

        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user && password_verify($current_password, $user['password']) && $new_password === $repeat_password) {
            // Update the password
            $stmt = $db->prepare('UPDATE users SET password = :new_password WHERE id = :id');
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':new_password', password_hash($new_password, PASSWORD_BCRYPT));
            $stmt->execute();

            // Log the user out
            session_unset();
            session_destroy();

            header('Location: login.php');
            exit;
        } else {
            error_log("Password change failed. Please check your input.");
        }
    } else if ($_POST['action'] === 'save_user_file') {
        file_put_contents(basename($_POST['codeeditor-filename']),$_POST['contents']);
    } else if ($_POST['action'] === 'delete') {
        // Delete a product
        $id = (int) $_POST['id'];
        $db->exec("DELETE FROM products WHERE id = $id");
    } else if ($_POST['action'] === 'add_new_category') {

        if (isset($_FILES['category-image']) && $_FILES['category-image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = 'images/' . basename($_FILES['category-image']['name']);
            if (file_exists($imagePath) === false) {
                if (move_uploaded_file($_FILES['category-image']['tmp_name'], $imagePath)) {
                    $image = $imagePath;
                }    
            } else {
                $image = $imagePath;
            }
        }

        $stmt = $db->prepare('INSERT INTO categories (name,image,provider) VALUES (:name,:image,:provider)');
        $stmt->bindValue(':name', htmlentities($_POST['category-name']));
        $stmt->bindValue(':image', $imagePath);
        $stmt->bindValue(':provider', htmlentities($_POST['category-provider']));
        $stmt->execute();
    } else if ($_POST['action'] === 'update_category') {
        $stmt = $db->prepare('UPDATE categories SET name=:name,provider=:provider WHERE id=:id');
        $stmt->bindValue(':id',htmlentities($_POST['id']));
        $stmt->bindValue(':name',htmlentities($_POST['category-name']));
        $stmt->bindValue(':provider',htmlentities($_POST['category-provider']));
        $stmt->execute();
    } else if ($_POST['action'] === 'delete_category') {
        $stmt = $db->prepare('DELETE FROM categories WHERE id=:id');
        $stmt->bindValue(':id',htmlentities($_POST['id']));
        $stmt->execute();
    } else if ($_POST['action'] === 'import_stripe_product') {
        $image = null;
        $category_id = $_POST['category_id'];
        $tag = $_POST['tag'];
        $homepage = $_POST['homepage'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = 'images/' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $image = $imagePath;
            }
        } else if (isset($_POST['image'])) {
            $image = htmlentities($_POST['image']);
        }

        $stripe_id = htmlentities($_POST['price']);

        $name = getProduct(htmlentities($_POST['product']))['name'];
        $price = number_format(getPrice(htmlentities($_POST['price']))["unit_amount"] / 100,2);


        $stmt = $db->prepare('INSERT INTO products (name, price, image, category_id, tag, homepage, stripe_id) VALUES (:name, :price, :image, :category_id, :tag, :homepage, :stripe_id)');
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':image', $image);
        $stmt->bindValue(':category_id', $category_id);
        $stmt->bindValue(':tag', $tag);
        $stmt->bindValue(':homepage', $homepage);
        $stmt->bindValue(':stripe_id', $stripe_id);
        $stmt->execute();
    } else if ($_POST['action'] === 'update') {
        // Update a product
        $id = (int) $_POST['id'];
        $name = htmlspecialchars($_POST['name']);
        $price = (float) $_POST['price'];
        $image = null;
        $tag = $_POST['tag'];
        $homepage = $_POST['homepage'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = 'images/' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $image = $imagePath;
            }
        }

        $stmt = $db->prepare('UPDATE products SET name = :name, price = :price, image = COALESCE(:image, image), tag = :tag, homepage = :homepage WHERE id = :id');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
        $stmt->bindValue(':image', $image, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':tag', $tag, SQLITE3_TEXT);
        $stmt->bindValue(':homepage', $homepage, SQLITE3_INTEGER);
        $stmt->execute();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['coupon_view_sendemail'])) {
        $stmt = $db->prepare('SELECT value FROM settings WHERE name=:name;');
        $stmt->bindValue(':name', 'giftcard_page_content');
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        $modifiedString = str_replace("{coupon_code}", $_POST['id'], $row['value']);
        $modifiedString = str_replace("{amount}", number_format($_POST['amount_off']/100,2), $modifiedString);
        $modifiedString = str_replace("{sender_email}", $_POST['sender'], $modifiedString);
        $modifiedString = str_replace("{url}", $_SERVER['SERVER_NAME'], $modifiedString);

        // Send HTML mail
        $to = $_POST['receiver'];
        $subject = $settings['giftcard_email_subject'];

        $headers  = "From: " . strip_tags($settings['noreply_email']) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message = $modifiedString;

        // file deepcode ignore EmailContentInjection: not applicable
        if (mail($to, $subject, $message, $headers)) {
            $error_meesage = "mail sent successfully";
        } else {
            $error_message = "Failed to send email.";
        }
    } else if (isset($_POST['coupon_edit_submit'])) {
        require "vendor/autoload.php";
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        $metadata = [];

        if (isset($_POST['message'])) {
            $metadata['message'] = $_POST['message'];
        }
        if (isset($_POST['payment_intent'])) {
            $metadata['payment_intent'] = $_POST['payment_intent'];
        }
        if (isset($_POST['receiver'])) {
            $metadata['received'] = $_POST['receiver'];
        }
        if (isset($_POST['sender'])) {
            $metadata['sender'] = $_POST['sender'];
        }

        $stripe->coupons->update($_POST['coupon_edit_code'], 
            [
                'name' => $_POST['coupon_edit_name'],
                'metadata' => $metadata
            ]
        );
    } else if (isset($_POST['coupon_delete_submit'])) {
        require "vendor/autoload.php";
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
        $stripe = new \Stripe\StripeClient($stripe_secret);

        $stripe->coupons->delete(htmlentities($_POST['coupon_code']), []);

    } else if ($_POST['create_newsletter']) {
        $number_of_products = htmlentities($_POST['newsletter_total_products']);
        $new_arrivals = "";
        for ($i = 1; $i <= $number_of_products; $i++) {
            $product_name = htmlentities($_POST['newsletter_new_arrival_'.$i]);
            // file deepcode ignore Sqli: $_POST parameter is sanitize previously
            $result = $db->query("SELECT * FROM products WHERE name='$product_name'");
            $product = $result->fetchArray(SQLITE3_ASSOC);
            $new_arrivals .= '<div class="product"><img src="'.$settings['siteurl'].'/'.$product["image"].'" width="100" height="100" alt="'.$product['name'].'"><p><strong>'.$product['name'].'</strong></p><p>$'.number_format($product['price'],2).'</p></div>';
        }
        $newsletter_html = $settings['newsletter_page_content'];
        $modifiedString = str_replace("{new_arrivals}", $new_arrivals, $newsletter_html);
        $modifiedString = str_replace("{sitename}", $settings['sitename'], $modifiedString);
        $modifiedString = str_replace("{siteurl}", $settings['siteurl'], $modifiedString);
        $modifiedString = str_replace("{facebook_url}", $settings['fa-facebook-official'], $modifiedString);
        $modifiedString = str_replace("{instagram_url}", $settings['fa-instagram'], $modifiedString);
        $modifiedString = str_replace("{twitter_url}", $settings['fa-twitter'], $modifiedString);
        $modifiedString = str_replace("{unsubscribe_url}", $settings['unsubscribe_url'], $modifiedString);
        $encoded = base64_encode(json_encode($modifiedString));
        $release_date = $_POST['newsletter_release_date'];

        $stmt = $db->prepare("INSERT INTO newsletters (release_date,content) VALUES (:release_date,:content)");
        $stmt->bindValue(":release_date",$release_date);
        $stmt->bindValue(":content", $encoded);
        $stmt->execute();

        // Redirect to the same page to clear POST data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit; // Ensure script stops execution after redirect
    } else if (isset($_POST['delete_newsletter'])) {
        $stmt = $db->prepare("DELETE FROM newsletters WHERE id=:id");
        $stmt->bindValue(":id",$_POST['newsletter-delete-id']);
        $stmt->execute();
    } else if (isset($_POST['order_delete_submit'])) {
        $stmt = $db->prepare("DELETE FROM tracker WHERE id=:id;");
        $stmt->bindValue(":id",htmlentities($_POST['order_id']));
        $stmt->execute();
    } else if (isset($_POST['resend_newsletter'])) {
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

        $newsletter_id = htmlentities($_POST['newsletter_id']);
        
        $stmt = $db->prepare('SELECT * FROM newsletters WHERE id=:id');
        $stmt->bindValue(":id",$newsletter_id);
        $result = $stmt->execute();
        $newsletter = $result->fetchArray(SQLITE3_ASSOC);


        $customer = $stripe->customers->retrieve(htmlentities($_POST['customer_id']), []);
        $email = $customer->email;


        $content = json_decode(base64_decode($newsletter['content']));
        
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
        header("Location: admin.php");
        exit;
    }
}
?>