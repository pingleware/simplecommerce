<?php
require 'settings.php';

$keyword = "";

if (isset($_POST['search'])) {
    $keyword = $_POST['search'];
} else {
    header("Location: index.php");
}

global $current_page;

// Pagination settings
$items_per_page = $settings['items_per_page'];
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Calculate offset
$offset = ($current_page - 1) * $items_per_page;

$keyword = $db->escapeString($keyword); // Use SQLite3 escaping to prevent SQL injection

$products = [];
$results = $db->query('SELECT * FROM products WHERE name LIKE "%'.$keyword.'%" ORDER BY available_date DESC LIMIT '.$items_per_page.' OFFSET '.$offset.';');
while ($product = $results->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $product;
}
$total_products = count($products);

// Fetch categories
$categories = $db->query('SELECT * FROM categories ORDER BY name ASC;');


// Count items in the cart
$cart_count = 0;
if (is_array($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo strtoupper($settings['sitename']); ?>: Search Results</title>
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
            
            <!-- Top header -->
            <header class="w3-container w3-xlarge">
                <p class="w3-left">Search Results</p>
                <p class="w3-right">
                <a href="cart.php" class="fa fa-shopping-cart w3-margin-right"><sup><?php echo '('.$cart_count.')'; ?></sup></a>
                <i class="fa fa-search" id="search-button"><br/><form method="post" action="search.php" name="search-form" id="search-form"><input type="search" class="w3-input" style="display:none;" name="search" id="search" placeholder="Search keywords..." /></form></i>
                </p>
            </header>

            <!-- Image header -->
            <div class="w3-display-container w3-container">
                <img src="images/search.jpg" alt="Jeans" style="width:100%">
                <div class="w3-display-topleft w3-text-white" style="padding:24px 48px">
                </div>
            </div>

            <div class="w3-container w3-text-grey" id="provider">
                <p>Search Results</p>
                <p><?php echo $total_products; ?> item(s)</p>
            </div>

            <!-- Product grid -->
            <?php $total_pages = $total_products / 8; ?>
            <div class="w3-row w3-grayscale">
                <div class="w3-col l3 s6">
                    <div class="w3-container">
                    <?php if (isset($products[0])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[0]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[0]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[0]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[0]['name']); ?><br><b>$<?php echo htmlentities($products[0]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                    <?php if (isset($products[4])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[4]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[4]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[4]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[4]['name']); ?><br><b>$<?php echo htmlentities($products[4]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                </div>

                <div class="w3-col l3 s6">
                    <div class="w3-container">
                    <?php if (isset($products[1])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[1]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[1]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[1]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[1]['name']); ?><br><b>$<?php echo htmlentities($products[1]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                    <?php if (isset($products[5])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[5]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[5]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[5]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[5]['name']); ?><br><b>$<?php echo htmlentities($products[5]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                </div>

                <div class="w3-col l3 s6">
                    <div class="w3-container">
                        <?php if (isset($products[2])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[2]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[2]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[2]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[2]['name']); ?><br><b>$<?php echo htmlentities($products[2]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                    <?php if (isset($products[6])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[6]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[6]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[6]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[6]['name']); ?><br><b>$<?php echo htmlentities($products[6]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                </div>

                <div class="w3-col l3 s6">
                    <div class="w3-container">
                    <?php if (isset($products[3])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[3]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[3]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[3]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[3]['name']); ?><br><b>$<?php echo htmlentities($products[3]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                    <div class="w3-container">
                    <?php if (isset($products[7])) : ?>
                            <div class="w3-display-container">
                                <img src="<?php echo htmlentities($products[0]['image']); ?>" style="width:200px; height:200px;">
                                <span class="w3-tag w3-display-topleft"><?php echo htmlentities($products[0]['tag']); ?></span>
                                <div class="w3-display-middle w3-display-hover">
                                    <a href="cart.php?action=add&id=<?php echo htmlentities($products[0]['id']); ?>" class="w3-button w3-black">Add to cart <i class="fa fa-shopping-cart"></i></a>
                                </div>
                            </div>
                            <p><?php echo htmlentities($products[0]['name']); ?><br><b>$<?php echo htmlentities($products[0]['price']); ?></b></p>                        
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pagination Links -->
                <div class="w3-bar w3-center w3-margin-top">
                    <!-- Previous Page Link -->
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>" class="w3-button w3-light-grey">&laquo; Previous</a>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>" 
                        class="w3-button <?php echo $page == $current_page ? 'w3-blue' : 'w3-light-grey'; ?>">
                        <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Page Link -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>" class="w3-button w3-light-grey">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>

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
