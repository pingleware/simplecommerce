<?php
require 'settings.php';
require 'functions.php';

$error = "";

if (isset($_POST['unsubscribe'])) {
    require "vendor/autoload.php";
    $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
    $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
    $stripe_secret = $stripe_test_secret;
    $stripe_mode = $settings['stripe_mode'];
    if ($stripe_mode == "live") {
        $stripe_secret = $stripe_live_secret;
    }
    $stripe = new \Stripe\StripeClient($stripe_secret);

    $customer_email = htmlentities($_POST['email']);
    $error = "<b>$customer_email</b> not found nor is a subscriber?";

    $customer = $stripe->customers->search([
        'query' => 'email:\''.$customer_email.'\'',
    ]);

    // Extract customer IDs from API response
    $customerIds = [];
    foreach ($customer->data as $_customer) {
        $stmt = $db->prepare("SELECT stripe_customer_id FROM newsletter_subscriptions WHERE stripe_customer_id=:stripe_customer_id");
        $stmt->bindValue(":stripe_customer_id", $_customer->id);
        $result = $stmt->execute();
        $existing = $result->fetchArray(SQLITE3_ASSOC);
        if (isset($existing['stripe_customer_id'])) {
            $customerIds[] = $existing['stripe_customer_id'];
            $error = "<b>$customer_email</b> unsubscribed successfully.";
        }
    }
    foreach($customerIds as $id) {
        $stmt = $db->prepare("DELETE FROM newsletter_subscriptions WHERE stripe_customer_id=:stripe_customer_id");
        $stmt->bindValue(":stripe_customer_id", $id);
        $result = $stmt->execute();
    }
}

$categories = $db->query('SELECT * FROM categories ORDER BY name ASC');

// Count items in the cart
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo strtoupper($settings['sitename']); ?>: Stores</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style>
            .w3-sidebar a {
                font-family: "Roboto", sans-serif;
            }
            body,h1,h2,h3,h4,h5,h6,.w3-wide {
                font-family: "Montserrat", sans-serif;
            }
            .progress-bar {
                display: flex;
                width: 100%;
                height: 30px;
                border: 1px solid #ccc;
                border-radius: 5px;
                overflow: hidden;
            }
            .segment {
                flex: 1;
                text-align: center;
                line-height: 30px;
                color: white;
                font-size: 12px;
                transition: background-color 0.3s ease;
            }
            .completed {
                background-color: #4CAF50;
            }
            .active {
                background-color: #FF9800;
            }
            .pending {
                background-color: #ddd;
                color: #555;
            }
        </style>
    </head>
    <body class="w3-content" style="max-width:1200px">
        <!-- Sidebar/menu -->
        <nav class="w3-sidebar w3-bar-block w3-white w3-collapse w3-top" style="z-index:3;width:250px" id="mySidebar">
            <div class="w3-container w3-display-container w3-padding-16">
                <i onclick="w3_close()" class="fa fa-remove w3-hide-large w3-button w3-display-topright"></i>
                <h3 class="w3-wide w3-small"><a href="index.php" style="text-decoration:none;"><b><?php echo strtoupper($settings['sitename']); ?></b></a></h3>
            </div>
            <div class="w3-padding-64 w3-small w3-text-grey" style="font-weight:bold">
            <?php while ($category = $categories->fetchArray()): ?>
                <a href="product.php?id=<?php echo $category['id']; ?>" class="w3-bar-item w3-button"><?php echo ucfirst($category['name']); ?></a>
            <?php endwhile; ?>
            </div>
            <a href="#footer" class="w3-bar-item w3-button w3-padding">Contact</a> 
            <a href="javascript:void(0)" class="w3-bar-item w3-button w3-padding" onclick="document.getElementById('newsletter').style.display='block'">Newsletter</a> 
            <a href="#footer"  class="w3-bar-item w3-button w3-padding">Subscribe</a>
        </nav>

        <!-- Top menu on small screens -->
        <header class="w3-bar w3-top w3-hide-large w3-black w3-xlarge">
        <div class="w3-bar-item w3-padding-24 w3-wide"><a href="index.php" style="text-decoration:none;"><?php echo strtoupper($settings['sitename']); ?></a></div>
        <a href="javascript:void(0)" class="w3-bar-item w3-button w3-padding-24 w3-right" onclick="w3_open()"><i class="fa fa-bars"></i></a>
        </header>

        <!-- Overlay effect when opening sidebar on small screens -->
        <div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

        <!-- !PAGE CONTENT! -->
        <div class="w3-main" style="margin-left:250px">

            <!-- Push down content on small screens -->
            <div class="w3-hide-large" style="margin-top:83px"></div>
            
            <!-- Top header -->
            <header class="w3-container w3-xlarge">
                <p class="w3-left">Unsubscribe from Newsletter</p>
                <p class="w3-right">
                <a href="cart.php" class="fa fa-shopping-cart w3-margin-right"><sup><?php echo '('.$cart_count.')'; ?></sup></a>
                <i class="fa fa-search" id="search-button"><br/><form method="post" action="search.php" name="search-form" id="search-form"><input type="search" class="w3-input" style="display:none;" name="search" id="search" placeholder="Search keywords..." /></form></i>
                </p>
            </header>

            <!-- Image header -->
            <div class="w3-display-container w3-container">
                <img src="images/unsubscribed.jpg" alt="Jeans" style="width:100%">
                <div class="w3-display-topleft w3-text-white" style="padding:24px 48px">
                </div>
            </div>

            <div class="w3-row w3-grayscale">
                <h1>Unsubscribe from Newsletter</h1>
                <?php echo $error; ?>
                <br/>
                <form action="unsubscribe.php" method="post" class="w3-black">
                    <h1>Unsubscribe</h1>
                    <p>To remove your email from our special offers and VIP treatment list:</p>
                    <p><input class="w3-input w3-block w3-border" name="email" type="text" placeholder="Enter e-mail" value="<?php echo isset($_GET['email']) ? $_GET['email'] : ''; ?>" style="width:100%" required></p>
                    <button type="submit" name="unsubscribe" class="w3-button w3-red w3-margin-bottom">Unsubscribe</button>
                </form>
            </div>
           
        <?php require('footer.php'); ?>

        <script>
            // Accordion 
            function myAccFunc() {
                var x = document.getElementById("demoAcc");
                if (x.className.indexOf("w3-show") == -1) {
                    x.className += " w3-show";
                } else {
                    x.className = x.className.replace(" w3-show", "");
                }
            }

            // Open and close sidebar
            function w3_open() {
                document.getElementById("mySidebar").style.display = "block";
                document.getElementById("myOverlay").style.display = "block";
            }
            
            function w3_close() {
                document.getElementById("mySidebar").style.display = "none";
                document.getElementById("myOverlay").style.display = "none";
            }

            document.getElementById("search-button").addEventListener("click",function(e){
                e.preventDefault();
                if (document.getElementById("search").style.display == "none") {
                    document.getElementById("search").style.display = "block";
                } else {
                    document.getElementById("search").style.display = "none";
                }
            })

            document.getElementById("search").addEventListener("focusout",function(e){
                e.preventDefault();
                if (this.value.trim() !== "") { // Prevents empty submissions
                    document.getElementById("search-form").submit();
                }
            })
        </script>
    </body>
</html>
