<?php
require 'settings.php';

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require('request.php');

// Fetch categories
$categories = $db->query('SELECT * FROM categories ORDER BY name ASC;');
if (!$categories) {
    die("Failed to fetch categories: " . $db->lastErrorMsg());
}

// Fetch homepage products
$homepage_products = $db->query('SELECT * FROM products WHERE homepage = 1 ORDER BY available_date DESC LIMIT 8;');
if (!$homepage_products) {
    die("Failed to fetch homepage products: " . $db->lastErrorMsg());
}
// Initialize the total count for homepage products
$total_homepage_products = 0;
$products = [];

// Iterate through the result set to count rows
while ($row = $homepage_products->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
    $total_homepage_products++;
}

// Count items in the cart
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html>
    <?php require('header.php'); ?>
    <body class="w3-content" style="max-width:1200px">

        <!-- Sidebar/menu -->
        <nav class="w3-sidebar w3-bar-block w3-white w3-collapse w3-top" style="z-index:3;width:250px" id="mySidebar">
            <div class="w3-container w3-display-container w3-padding-16">
                <i onclick="w3_close()" class="fa fa-remove w3-hide-large w3-button w3-display-topright"></i>
                <h3 class="w3-wide w3-small"><b><?php echo strtoupper($settings['sitename']); ?></b></h3>
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
        <div class="w3-bar-item w3-padding-24 w3-wide w3-small"><?php echo strtoupper($settings['sitename']); ?></div>
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
                <p class="w3-left"><?php echo $settings['siteslogan']; ?></p>
                <p class="w3-right">
                <a href="cart.php" class="fa fa-shopping-cart w3-margin-right"><sup><?php echo '('.$cart_count.')'; ?></sup></a>
                <i class="fa fa-search" id="search-button"><br/><form method="post" action="search.php" name="search-form" id="search-form"><input type="search" class="w3-input" style="display: none;" name="search" id="search" placeholder="Search keywords..." /></form></i>
                </p>
            </header>

            <!-- Image header -->
            <div class="w3-display-container w3-container">
                <img src="images/store-logo.jpg" alt="Store Logo" style="width:100%">
                <div class="w3-display-topleft w3-text-white" style="padding:24px 48px"></div>
            </div>

            <div class="w3-container w3-text-grey" id="jeans">
                <p><?php echo $total_homepage_products; ?> item(s)</p>
            </div>

            <!-- Product grid -->
            <div class="w3-row w3-grayscale">
                <div class="w3-col l3 s6">
                    <div class="w3-container">
                        <?php if (isset($products[0])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[0]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[0]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[0]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[0]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[0]['name']; ?><br><b>$<?php echo $products[0]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                        <?php if (isset($products[4])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[4]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[4]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[4]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[4]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[4]['name']; ?><br><b>$<?php echo $products[4]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="w3-col l3 s6">
                    <div class="w3-container">
                        <?php if (isset($products[1])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[1]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[1]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[1]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[1]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[1]['name']; ?><br><b>$<?php echo $products[1]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                        <?php if (isset($products[5])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[5]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[5]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[5]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[5]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[5]['name']; ?><br><b>$<?php echo $products[5]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="w3-col l3 s6">
                    <div class="w3-container">
                    <?php if (isset($products[2])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[2]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                <?php 
                                    if (strtotime($products[2]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[2]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[2]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[2]['name']; ?><br><b>$<?php echo $products[2]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                        <?php if (isset($products[6])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[6]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[6]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[6]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[6]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[6]['name']; ?><br><b>$<?php echo $products[6]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="w3-col l3 s6">
                    <div class="w3-container">
                        <?php if (isset($products[3])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[3]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[3]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[3]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[3]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[3]['name']; ?><br><b>$<?php echo $products[3]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                        <?php if (isset($products[7])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo $products[7]['image']; ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft">
                                    <?php 
                                    if (strtotime($products[7]['available_date'] . " +{$settings['DAYS_FOR_NEW']} days") >= time()) {
                                        echo $products[7]['tag'];
                                    }
                                    ?>
                                </span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo $products[7]['id']; ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo $products[7]['name']; ?><br><b>$<?php echo $products[7]['price']; ?></b></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Subscribe section -->
            <div class="w3-container w3-black w3-padding-32">
                <h1>Subscribe</h1>
                <form action="mailto:<?php echo $settings['contact-email']; ?>" method="post" enctype="text/plain"  target="_blank">
                    <p>To get special offers and VIP treatment:</p>
                    <p><input class="w3-input w3-block w3-border" type="text" placeholder="Enter e-mail"></p>
                    <input type="hidden" name="Subject" value="<?php echo strtoupper($settings['sitename']); ?> newsletter optin" />
                    <input type="hidden" name="Message" value="Please add me to your newsletter mailing list." />
                    <button type="submit" class="w3-button w3-red w3-margin-bottom" onclick="document.getElementById('newsletter').style.display='none'">Subscribe</button>
                </form>
            </div>
            
        <?php require('footer.php'); ?>

        <script type="text/javascript" src="js/fp.min.js"></script>
        <script>
            const fpPromise = FingerprintJS.load();

            window.onload = function() {
                fpPromise
                .then(fp => fp.get())
                .then(result => {
                    const visitorId = result.visitorId; // Unique fingerprint
                    localStorage.setItem('visitorId',visitorId);
                });
            }
            document.getElementById("emailForm").addEventListener("submit", function (event) {
                // Allow time for the email client to open
                setTimeout(() => {
                    // Reset the form fields
                    event.target.reset();
                }, 1000); // Delay to ensure email client is triggered first
            });
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
