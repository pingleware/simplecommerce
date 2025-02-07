<?php
require 'settings.php';
require 'functions.php';
require "vendor/autoload.php";

$categories = $db->query('SELECT * FROM categories ORDER BY name ASC');


if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cartItems = array_count_values($_SESSION['cart']);
$total = 0;

$payment_options = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['place_order'])) {
        $line_items = [];
        for ($i = 1; $i <= $_POST['total_products']; $i++) {
            if (isset($_POST['product_id_' . $i]) && isset($_POST['product_qty_' . $i])) {
                array_push($line_items, [
                    'price' => $_POST['product_id_' . $i],
                    'quantity' => $_POST['product_qty_' . $i]
                ]);    
            }
        }        

        $stripe_session = [
            'customer_email' => htmlentities($_POST['email']),
            'success_url' => $settings['success_url'] . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $settings['cancel_url'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'invoice_creation' => [
                'enabled' => true
            ],
            'allow_promotion_codes' => true,
            'shipping_address_collection' => [
                'allowed_countries' => ['US'], //  ensures the customer provides a shipping address.
            ]
        ];
        
        try {
            $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
            $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
            $stripe_secret = $stripe_test_secret;
            $stripe_mode = $settings['stripe_mode'];
            if ($stripe_mode == "live") {
                $stripe_secret = $stripe_live_secret;
            }
            $stripe = new \Stripe\StripeClient($stripe_secret);
            $result = $stripe->checkout->sessions->create($stripe_session);
            header('Location: ' . htmlentities($result->url));
            $_SESSION['cart'] = [];    
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle exception
            error_log('Error creating Stripe session: ' . $e->getMessage());
            header("Location: error.php?error=" . base64_encode(json_encode($e->getMessage())) . "&session=" . base64_encode(json_encode($stripe_session)));
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo strtoupper($settings['sitename']); ?>: Checkout</title>
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
            
            <!-- Image header -->
            <div class="w3-display-container w3-container">
                <img src="images/checkout-page.jpg" alt="CART" style="width:100%">
                <div class="w3-display-topleft w3-text-white" style="padding:24px 48px">
                </div>
            </div>

            <h2>Checkout</h2>
            <form action="checkout.php" class="w3-container w3-card w3-white w3-padding" method="POST">
                <h3>Billing Contact</h3>               
                <label for="email">Email</label>
                <input class="w3-input w3-border" type="email" id="email" name="email" value="<?php echo htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                <p class="w3-small w3-center"><b>Billing details including payment and shiping information will be collected by payment processor. We do not store your personal information on our public servers!</b></p>
                <h3>Order Summary</h3>
                <table class="w3-table w3-bordered">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                    <?php $count=1; ?>
                    <input type="hidden" name="total_products" value="<?php echo count($cartItems); ?>" />
                    <?php foreach ($cartItems as $id => $quantity):
                        $product = $db->querySingle("SELECT * FROM products WHERE id = $id", true);
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                    ?>
                    <input type="hidden" name="product_name_<?php echo $count; ?>" value="<?php echo $product['name']; ?>" />
                    <input type="hidden" name="product_id_<?php echo $count; ?>" value="<?php echo $product['stripe_id']; ?>" />
                    <input type="hidden" name="product_qty_<?php echo $count; ?>" value="<?php echo $quantity; ?>" />
                    <input type="hidden" name="product_price_<?php echo $count; ?>" value="<?php echo number_format($product['price'], 2); ?>" />
                    <?php $count++; ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $quantity; ?></td>
                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </table>
                <input type="hidden" name="total" value="<?php echo number_format($total, 2); ?>" />
                <button type="submit" name="place_order" class="w3-button w3-green w3-block w3-margin-top">Place Order and Proceed to Payment Processor</button>
            </form>
            <a href="cart.php" class="w3-button w3-block w3-blue w3-margin-top">Back to Cart</a>
            <br/>

            <!-- Subscribe section -->
            <div class="w3-container w3-black w3-padding-32">
                <h1>Subscribe</h1>
                <p>To get special offers and VIP treatment:</p>
                <p><input class="w3-input w3-border" type="text" placeholder="Enter e-mail" style="width:100%"></p>
                <button type="button" class="w3-button w3-red w3-margin-bottom">Subscribe</button>
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
        </script>
    </body>
</html>
