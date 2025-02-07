<?php
require 'settings.php';

$categories = $db->query('SELECT * FROM categories ORDER BY name ASC');


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $id = intval($_GET['id']);
    $_SESSION['cart'][] = $id;
    header('Location: cart.php');
}

$cartItems = array_count_values($_SESSION['cart']);
$total = 0;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Shopping Cart</title>
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
            @keyframes flash {
                0% { opacity: 1; }
                50% { opacity: 0.5; }
                100% { opacity: 1; }
            }

            .flash-button {
                background-color: red;
                color: white;
                border: none;
                cursor: pointer;
                animation: flash 1s infinite;
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
                <img src="images/cart-page.jpg" alt="CART" style="width:100%">
                <div class="w3-display-topleft w3-text-white" style="padding:24px 48px">
                </div>
            </div>

            <h2>Your Cart</h2>
            <?php if (!empty($cartItems)): ?>
                <table class="w3-table w3-bordered w3-white">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                    <?php $stripe_id_missing = false; ?>
                    <?php foreach ($_SESSION['cart'] as $index => $id):
                        $quantity = $cartItems[$id];
                        $product = $db->querySingle("SELECT * FROM products WHERE id = $id", true);
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;

                        $stripe_id_missing = !isset($product['stripe_id']);

                        $title = ""; $flashing = "";
                        if ($stripe_id_missing) {
                            $title = "This product is unavailbe? Please remove from cart!";
                            $flashing = "flash-button";
                        }
                    ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $quantity; ?></td>
                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                        <td><button class="w3-button w3-red <?php echo $flashing; ?>" title="<?php echo $title; ?>" onclick="removeItem(this)" data-index="<?php echo $index; ?>" data-id="<?php echo $id; ?>">Remove</button></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
                <?php if ($stripe_id_missing) : ?>
                    <button class="w3-button w3-block w3-green" disabled>Proceed to Checkout</button>
                <?php else : ?>
                    <a href="checkout.php" class="w3-button w3-block w3-green">Proceed to Checkout</a>
                <?php endif; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>

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

            function removeItem(element) {
                const cart_index = element.getAttribute("data-index");  
                console.log(cart_index); 
                
                const myHeaders = new Headers();
                myHeaders.append("Origin", location.origin);

                const formdata = new FormData();
                formdata.append("action", "remove_cart_item");
                formdata.append("cart_index", `${cart_index}`);

                const requestOptions = {
                    method: "POST",
                    headers: myHeaders,
                    body: formdata,
                    redirect: "follow"
                };

                fetch("/ajax.php", requestOptions)
                .then((response) => response.json())
                .then(function(result){
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.error);
                    }
                })
                .catch((error) => console.error(error));
                
            }
        </script>
    </body>
</html>
