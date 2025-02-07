<?php
require 'settings.php';
require 'functions_admin.php';


if (!isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

require("request_admin.php");

$prices = getPrices();
$stripe_products = getProducts();
$disputes = getDisputes();
$coupons = getCoupons();
$promotion_codes = getPromotions();
?>
<!DOCTYPE html>
<html>
<head> 
    <title><?php echo strtoupper($settings['sitename']); ?>: Administrator</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        editor {
            width: 100%;
            height: 500px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body class="w3-light-grey">
    <div class="w3-container">
        <h2 class="w3-center"><?php echo strtoupper($settings['sitename']); ?>: Admin Panel</h2>
        <div class="w3-bar w3-black" style="display: flex; align-items: center;">
            <button class="w3-bar-item w3-button tablinks w3-pale-yellow" onclick="openTab(event,'Orders')">Orders</button>
            <button class="w3-bar-item w3-button tablinks" onclick="openTab(event,'Products')">Products</button>
            <button class="w3-bar-item w3-button tablinks" onclick="openTab(event,'Newsletters')">Newsletters</button>
            <button class="w3-bar-item w3-button tablinks" onclick="openTab(event,'Pages')">Pages</button>
            <button class="w3-bar-item w3-button tablinks" onclick="openTab(event,'Users')">Users</button>
            <button class="w3-bar-item w3-button tablinks" onclick="openTab(event,'Settings')">Settings</button>
            <button class="w3-bar-item w3-button" style="margin-left: auto;" onclick="window.location.href='logout.php'">Logout</button>
        </div>

        <div id="Orders" class="tabs">
            <fieldset>
                <legend>Orders</legend>
                <div class="w3-bar w3-black">
                    <button class="w3-bar-item w3-button w3-pale-yellow ordertablinks" onclick="openTab(event,'orders-received','orders-status','ordertablinks')">Received&nbsp;<span id="total-received" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button ordertablinks" onclick="openTab(event,'orders-processing','orders-status','ordertablinks')">Processing&nbsp;<span id="total-processing" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button ordertablinks" onclick="openTab(event,'orders-shipped','orders-status','ordertablinks')">Shipped&nbsp;<span id="total-shipped" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button ordertablinks" onclick="openTab(event,'orders-outfordelivery','orders-status','ordertablinks')">Out for Delivery&nbsp;<span id="total-outfordelivery" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button ordertablinks" onclick="openTab(event,'orders-delivered','orders-status','ordertablinks')">Delivered&nbsp;<span id="total-delivered" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button ordertablinks" onclick="openTab(event,'orders-disputes','orders-status','ordertablinks')">Disputes&nbsp;<span id="total-disputes" class="w3-badge w3-white"><?php if (isset($disputes['count']) && $disputes['count'] > 0) { echo $disputes['count']; } else { echo "0"; } ?></span></button>
                </div>

                <!-- Oreders: received -->
                <div id="orders-received" class="orders-status">
                    <fieldset>
                        <legend>Order Received</legend>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersReceivedTable"></tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="orders-received-pagination-controls" class="w3-center"></div>

                    </fieldset>
                </div>
                <!-- Oreders: processing -->
                <div id="orders-processing" class="orders-status" style="display:none;">
                    <fieldset>
                        <legend>Processing</legend>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersProcessingTable">
                            </tbody>

                            <!-- Pagination Links -->
                            <div id="orders-processing-pagination-controls" class="w3-center"></div>

                        </table>
                    </fieldset>
                </div>
                <!-- Oreders: shipped -->
                <div id="orders-shipped" class="orders-status" style="display:none;">
                    <fieldset>
                        <legend>Shipped</legend>
                        <p class="w3-panel"><b>ShipStation</b> will only pull orders that have been placed in <b>SHIPPED</b> status.</p>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersShippedTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="orders-shipped-pagination-controls" class="w3-center"></div>

                    </fieldset>
                    <!-- USPS CLICK-N-SHIP EXPORT DIALOG -->
                    <div id="export-usps-dialog" class="w3-modal">
                        <div class="w3-modal-content">
                            <div class="w3-container w3-white">
                                <h2 class="w3-blue">USPS Click-n-Ship Export</h2>
                                <form method="post">
                                    <input type="hidden" name="action" value="usps_clicknship_export" />
                                    <fieldset>
                                        <legend>Sender</legend>
                                        <label for="">First Name</label>
                                        <input type="text" class="w3-input w3-block" name="usps_firstname" id="usps_firstname" value="" required />
                                        <label for="">Midddle Initial</label>
                                        <input type="text" class="w3-input w3-block" name="usps_middleinitial" id="usps_middleinitial" value="" />
                                        <label for="">Last Name</label>
                                        <input type="text" class="w3-input w3-block" name="usps_lastname" id="usps_lastname" value="" required />
                                        <label for="">Address Line 1</label>
                                        <input type="text" class="w3-input w3-block" name="usps_addressline1" id="usps_addressline1" value="" required />
                                        <label for="">Address Line 2</label>
                                        <input type="text" class="w3-input w3-block" name="usps_addressline2" id="usps_addressline2" value="" required />
                                        <label for="">Address Town/City</label>
                                        <input type="text" class="w3-input w3-block" name="usps_addresstowncity" id="usps_addresstowncity" value="" required />
                                        <label for="">State</label>
                                        <input type="text" class="w3-input w3-block" name="usps_state" id="usps_state" value="" required />
                                        <label for="">ZIP Code</label>
                                        <input type="text" class="w3-input w3-block" name="usps_zipcode" id="usps_zipcode" value="" required />
                                    </fieldset>
                                    <fieldset>
                                        <legend>Orders</legend>
                                        <table class="w3-table w3-striped w3-container w3-light-grey">
                                            <tbody id="uspsOrdersTable"></tbody>
                                        </table>
                                    </fieldset>
                                    <br/>
                                    <input type="submit" class="w3-button w3-block w3-black" value="Submit" />
                                </form>
                                <br/>
                                <a href="#" target="_blank" class="w3-button w3-block w3-yellow" id="usps_viewcustomer">View Customer</a>
                                <br/>
                                <a href="https://cnsb.usps.com/file-upload/define-data" target="_blank" class="w3-button w3-block w3-blue">USPS Click-n-Ship Import Manager</a>
                                <br/>
                                <button class="w3-button w3-block w3-orange" onclick="document.getElementById('export-usps-dialog').style.display='none';">Cancel</button>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Oreders: outfordelivery -->
                <div id="orders-outfordelivery" class="orders-status" style="display:none;">
                    <fieldset>
                        <legend>Out for Delivery</legend>
                        <p class="w3-panel">The order is in possession with the shipping carrier. <b>ShipStation</b> will update on a shipnotify event.</p>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersOutForDeliveryTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="orders-outfordelivery-pagination-controls" class="w3-center"></div>
                        
                        <!-- Order Notification Dialog -->
                        <div id="order-notification-dialog" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <h2 class="w3-yellow">Order Notification</h2>
                                    <form method="post">
                                        <input type="hidden" name="action" value="order_notification_tracking" />
                                        <label for="order-notification-sessionid">Session ID</label>
                                        <input type="text" class="w3-input w3-block w3-light-grey" name="order-notification-sessionid" id="order-notification-sessionid" value="" readonly />
                                        <label for="order-notification-svctype">Service Type</label>
                                        <input type="text" class="w3-input w3-block" name="order-notification-svctype" id="order-notification-svctype" value="" required />
                                        <label for="order-notification-pkgtype">Package Type</label>
                                        <input type="text" class="w3-input w3-block" name="order-notification-pkgtype" id="order-notification-pkgtype" value="" required />
                                        <label for="order-notification-trackingno">Tracking Number</label>
                                        <input type="text" class="w3-input w3-block" name="order-notification-trackingno" id="order-notification-trackingno" value="" required />
                                        <br/>
                                        <input type="submit" name="order_notification" class="w3-button w3-block w3-black" value="Submit" />
                                    </form>
                                    <br/>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('order-notification-dialog').style.display='none';">Cancel</button>
                                    <br/>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <!-- Oreders: delivered -->
                <div id="orders-delivered" class="orders-status" style="display:none;">
                    <fieldset>
                        <legend>Delivered</legend>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersDeliveredTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="orders-delivered-pagination-controls" class="w3-center"></div>

                   </fieldset>
                </div>
                <!-- Oreders: disputes -->
                <div id="orders-disputes" class="orders-status" style="display:none;">
                    <fieldset>
                        <legend>Disputes</legend>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Respond By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="disputesTable">
                            <?php 
                            // SEE OFFICIAL USER GUIDE REFERENCE "disputes undefined"
                            if (count($disputes) > 0 && isset($disputes['count'])) {
                                if ($disputes['count'] > 0) {
                                    foreach($disputes->autoPagingIterator() as $dispute) {
                                        $base64 = base64_encode(json_encode($dispute));
                                ?>
                                <tr>
                                    <td><?php echo $dispute->id; ?></td>
                                    <td><?php echo '$ '.number_format($dispute->amount / 100,2); ?></td>
                                    <td><?php echo date("Y-m-d H:i:s", $dispute->evidence_details->due_by); ?></td>
                                    <td>
                                        <select class="w3-input" onclick="return disputeAction(this)" data-id="<?php echo $dispute['id']; ?>" data-dispute="<?php echo $base64; ?>">
                                            <option value="">Select</option>
                                            <option value="view">View</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr><td colspan="3" class="w3-center"><b>No disputes!</b></td></tr>
                                <?php
                                }    
                            }
                            ?> 
                            </tbody>                   
                        </table>

                        <!-- Pagination Links -->
                        <div id="disputes-pagination-controls" class="w3-center">
                            <?php if ($disputes->has_more) : ?>
                                <button class="w3-button w3-blue" onclick="window.location.reload();">Reload</button>
                                &nbsp;
                                <button class="w3-button w3-blue" id="dispute-pagination-next" data-dispute-next="<?php echo $disputes->data[count($disputes->data) - 1]->id; ?>" onclick="updateDisputes(this);">Next</button>
                            <?php endif; ?>
                        </div>

                    </fieldset>
                </div>
            </fieldset>
            <!-- DELETE ORDER DIALOG -->
            <div id="order-delete-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <form method="post">
                            <h2 class="w3-red">Delete Order</h2>
                            <input type="hidden" name="order_id" id="order-delete-id" value="0" />
                            <label for="order-delete-session-id">Session ID</label>
                            <input type="text" class="w3-inut w3-block w3-light-grey" id="order-delete-session-id" value="" readonly />
                            <br/>
                            <input type="submit" class="w3-button w3-block w3-black" name="order_delete_submit" value="Submit" />
                        </form>
                        <br/>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('order-delete-dialog').style.display='none'">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- STATUS DIALOG -->
            <div id="status-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-yellow">Update Status</h2>
                        <form method="post" action="admin.php">
                            <input type="hidden" name="action" value="update_order_status" />
                            <input type="hidden" name="order-id" id="order-id" value="0" />
                            <label for="order-status">Status</label>
                            <select class="w3-input w3-block" id="order-status" name="order-status">
                                <option value="">Select</option>
                                <option value="Order Received">Order Received</option>
                                <option value="Processing">Processing</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Out for Delivery">Out for Delivery</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Archived">Archived [hidden]</option>
                            </select>
                            <label for="order-notes">Notes</label>
                            <textarea class="w3-input w3-block" id="order-notes" name="order-notes" rows="10"></textarea>
                            <br/>
                            <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                            <br/>
                            <button onclick="document.getElementById('status-dialog').style.display='none';" class="w3-button w3-block w3-orange">Cancel</button>
                            <br/>
                            <p><b>IMPORTANT:</b> Do not add any PII or tracking numbers in the notes field as the tracker form is available without any security.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="Products" class="tabs" style="display:none">
            <fieldset>
                <legend>Products &amp; Categories</legend>
                <div class="w3-bar w3-black">
                    <button class="w3-bar-item w3-button w3-pale-yellow productstablinks" onclick="openTab(event,'products-product','products-categories','productstablinks')">Products&nbsp;<span id="total-products" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button productstablinks" onclick="openTab(event,'products-category','products-categories','productstablinks')">Category&nbsp;<span id="total-categories" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button productstablinks" onclick="openTab(event,'products-coupons','products-categories','productstablinks')">Coupons&nbsp;<span id="total-coupons" class="w3-badge w3-white"><?php if (isset($coupons['count']) && $coupons['count'] > 0) { echo $coupons['count']; } else { echo "0"; } ?></span></button>
                    <button class="w3-bar-item w3-button productstablinks" onclick="openTab(event,'products-promotions','products-categories','productstablinks')">Promotions&nbsp;<span id="total-promotions" class="w3-badge w3-white"><?php if (isset($promotion_codes['count']) && $promotion_codes['count'] > 0) { echo $promotion_codes['count']; } else { echo "0"; } ?></span></button>
                </div>
                <!-- Products: products -->
                <div id="products-product" class="products-categories">
                    <fieldset>
                        <legend>Products</legend>
                        <!-- TODO: remove this feature and in the request code
                        <button class="w3-button w3-block w3-green" onclick="document.getElementById('product-dialog').style.display='block';">Add New Product</button>
                        -->
                        <br/>
                        <button class="w3-button w3-block w3-purple" onclick="document.getElementById('import-product-dialog').style.display='block';">Import Product from Stripe</button>
                        <br/>
                        <h3>Manage Products</h3>
                        <table class="w3-table w3-bordered w3-white">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="product-pagination-controls" class="w3-center"></div>

                        <!-- PRODUCT DIALOG -->
                        <div id="product-dialog" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <h2 class="w3-green">Add Product</h2>
                                    <p>When adding a product, a corresponding product and price object is also created on STRIPE.</p>
                                    <form class="w3-container w3-card w3-white w3-padding" method="POST" enctype="multipart/form-data" action="admin.php" >
                                        <input type="hidden" name="action" value="add">
                                        <label for="name">Name</label>
                                        <input class="w3-input w3-block w3-border" type="text" id="name" name="name" required>

                                        <label for="price">Price</label>
                                        <input class="w3-input w3-block w3-border" type="number" step="0.01" id="price" name="price" required>

                                        <label for="stripe_price">Stripe Price ID</label>
                                        <select class="w3-input w3-block w3-border" name="price_id" id="stripe_price">
                                            <option value="">Select</option>
                                            <?php
                                            // SEE OFFICIAL USER GUIDE REFERENCE "prices undefined"
                                            if (isset($prices['data'])) {
                                                foreach($prices['data'] as $price) {
                                                    $recurring = " each";
                                                    if (isset($price['recurring'])) {
                                                        $recurring = ' recurring at '. $price['recurring']['interval_count'] . ' per ' . $price['recurring']['interval'];
                                                    }
                                                    $unit_amount = number_format($price['unit_amount'] / 100, 2);
                                                    ?>
                                                        <option value="<?php echo htmlentities($price['id']); ?>">$<?php echo htmlentities($unit_amount).htmlentities($recurring); ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>

                                        <label for="category">Category:</label>
                                        <select id="category" name="category_id" class="w3-input w3-block" required>
                                        </select>
                                        
                                        <label for="image">Image</label>
                                        <input class="w3-input w3-block w3-border" type="file" id="image" name="image" accept="image/*">

                                        <label for="tag">Tag</label>
                                        <input class="w3-input w3-block w3-border" id="tag" name="tag" placeholder="add tag to display on product image like New, On Sale, Featured, etc." />

                                        <label for="homepage">On Homepage</label>
                                        <input class="w3-input w3-block w3-border" type="number" id="homepage" name="homepage" placeholder="use 1 for homepage placement, a maximum of 8 products can appear on the homepage" />

                                        <label for="exempt">Tax Exempt</label>
                                        <input type="checkbox" class="w3-input w3-block w3-border" id="tax_behavior" name="tax_behavior" value="exclusive" />

                                        <button type="submit" name="add_product" class="w3-button w3-block w3-black w3-margin-top">Submit</button>
                                        </br>
                                    </form>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('product-dialog').style.display='none';">Cancel</button>
                                    <br/>
                                </div>
                            </div> 
                        </div>
                        <!-- IMPORT PRODUCT DIALOG -->
                        <div id="import-product-dialog" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <h2 class="w3-purple">Import Product from Stripe</h2>
                                    <form method="post" enctype="multipart/form-data" action="admin.php">
                                        <input type="hidden" name="action" value="import_stripe_product" />
                                        <label for="import-product-products">Products</label>
                                        <select class="w3-input w3-block w3-border" id="import-product-products" name="product" onchange="importProductChange(this)">
                                            <option value="">Select</option>
                                            <?php 
                                                // SEE OFFICIAL USER GUIDE REFERENCE "stripe_products undefined"
                                                if (isset($stripe_products['data'])) {
                                                    foreach($stripe_products['data'] as $product) {
                                                        echo '<option value="'.htmlentities($product['id']).'" data-product="'.base64_encode(json_encode($product)).'">'.htmlentities($product['name']).'</option>';
                                                    }    
                                                }
                                            ?>
                                        </select>
                                        <label for="import-product-prices">Prices</label>
                                        <select class="w3-input w3-block w3-border" id="import-product-prices" name="price">
                                            <option value="">Select</option>
                                            <?php
                                            // SEE OFFICIAL USER GUIDE REFERENCE "prices undefined"
                                            if (isset($prices['data'])) {
                                                foreach($prices['data'] as $price) {
                                                    $recurring = " each";
                                                    if (isset($price['recurring'])) {
                                                        $recurring = ' recurring at '. $price['recurring']['interval_count'] . ' per ' . $price['recurring']['interval'];
                                                    }
                                                    $unit_amount = number_format($price['unit_amount'] / 100, 2);
                                                    $tax_behavior = $price['tax_behavior'];
                                                    ?>
                                                        <option value="<?php echo htmlentities($price['id']); ?>">$<?php echo htmlentities($unit_amount).htmlentities($recurring).', Tax Behavior: '.htmlentities($tax_behavior); ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>

                                        <label for="import-product-category">Category:</label>
                                        <select id="import-product-category" name="category_id" class="w3-input w3-block" required>
                                        </select>

                                        <label for="import-product-image">Image</label>
                                        <input class="w3-input w3-block w3-border" type="file" id="import-product-image" name="image" accept="image/*">

                                        <label for="import-product-tag">Tag</label>
                                        <input class="w3-input w3-block w3-border" id="import-product-tag" name="tag" placeholder="add tag to display on product image like New, On Sale, Featured, etc." />

                                        <label for="import-product-homepage">On Homepage</label>
                                        <input class="w3-input w3-block w3-border" type="number" id="import-product-homepage" name="homepage" placeholder="use 1 for homepage placement, a maximum of 8 products can appear on the homepage" />
                                        <br/>
                                        <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                                        <br/>
                                    </form>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('import-product-dialog').style.display='none';">Cancel</button>
                                    <br/>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <!-- Products: categories -->
                <div id="products-category" class="products-categories" style="display:none;">
                    <fieldset>
                        <legend>Categories</legend>
                        <button class="w3-button w3-block w3-blue" onclick="document.getElementById('category-dialog').style.display='block';"">Add New Category</button>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Provider</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="categoryTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="category-pagination-controls" class="w3-center"></div>

                        <!-- NEW CATEGORY DIALOG -->
                        <div id="category-dialog" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <form method="post" enctype="multipart/form-data" action="admin.php">
                                        <h2 class="w3-blue">Add New Category</h2>
                                        <input type="hidden" name="action" value="add_new_category" />
                                        <label for="name">Name</label>
                                        <input type="text" class="w3-input w3-block" name="category-name" value="" required />
                                        <label for="image">Image</label>
                                        <input type="file" class="w3-input w3-block" name="category-image" value="" required />
                                        <label for="provider">Provider</label>
                                        <input type="text" class="w3-input w3-block" name="category-provider" value="" />
                                        <br/>
                                        <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                                        <br/>
                                    </form>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('category-dialog').style.display='none';"">Cancel</button>
                                    <br/>
                                </div>
                            </div>
                        </div>
                        <!-- UPDATE CATEGORY DIALOG -->
                        <div id="update-category-dialog" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <form method="post" action="admin.php">
                                        <h2 class="w3-blue">Update Existing Category</h2>
                                        <p>Due to security reasons, the image cannot be change. To change the image, first delete the selected category and then create a new category with the new image.</p>
                                        <input type="hidden" name="action" value="update_category" />
                                        <input type="hidden" name="id" id="category-id" value="0" />
                                        <label for="name">Name</label>
                                        <input type="text" class="w3-input w3-block" name="category-name" id="category-name" value="" required />
                                        <label for="provider">Provider</label>
                                        <input type="text" class="w3-input w3-block" name="category-provider" id="category-provider" value="" />
                                        <br/>
                                        <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                                        <br/>
                                    </form>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('update-category-dialog').style.display='none';"">Cancel</button>
                                    <br/>
                                </div>
                            </div>
                        </div>
                        <!-- CATEGORY DELETE DIALOG -->
                        <div id="category-delete-dialog" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <h2 class="w3-red">Delete Category</h2>
                                    <form method="post" action="admin.php">
                                        <label for="delete-category-name">Delete Category</label>
                                        <input type="text" class="w3-input w3-block" id="delete-category-name" value="" />
                                        <input type="hidden" name="action" value="delete_category" />
                                        <input type="hidden" name="id" id="delete-category-id" value="0" />
                                        <button type="submit" class="w3-button w3-block w3-green">Yes</button>
                                        <br/>
                                    </form>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('category-delete-dialog').style.display='none';">No</button>
                                    <br/>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <!-- Products: coupons -->
                <div id="products-coupons" class="products-categories" style="display:none;">
                    <fieldset>
                        <legend>Coupon(s)</legend>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Created</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Amount/Percent</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="couponsTable">
                            <?php if (count($coupons) > 0) : ?>
                                <?php //if ($coupons['count'] > 0) : ?>
                                    <?php foreach($coupons->autoPagingIterator() as $coupon) : ?>
                                        <?php $base64 = base64_encode(json_encode($coupon)); ?>
                                        <?php 
                                            if (isset($coupon->percent_off) && !is_null($coupon->percent_off)) {
                                                $amount = htmlentities($coupon->percent_off) . '%'; 
                                            } else {
                                                $amount = '$' . number_format($coupon->amount_off / 100, 2);
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($coupon->created); ?></td>
                                            <td><?php echo htmlentities($coupon->id); ?></td>
                                            <td><?php echo htmlentities($coupon->name); ?></td>
                                            <td><?php echo htmlentities($amount); ?></td>
                                            <td>
                                                <button class="w3-button w3-blue" onclick="return viewCoupon(this)" data-coupon="<?php echo $base64; ?>">View</button>
                                                &nbsp;
                                                <button class="w3-button w3-green" onclick="return editCoupon(this)" data-coupon="<?php echo $base64; ?>">Edit</button>
                                                &nbsp;
                                                <button class="w3-button w3-red" onclick="return deleteCoupon(this)" data-coupon="<?php echo $base64; ?>">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php //endif; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="coupons-pagination-controls" class="w3-center">
                            <?php if ($coupons->has_more) : ?>
                                <button class="w3-button w3-blue" onclick="window.location.reload();">Reload</button>
                                &nbsp;
                                <button class="w3-button w3-blue" id="coupon-pagination-next" data-coupon-next="<?php echo $coupons->data[count($coupons->data) - 1]->id; ?>" onclick="updateCoupons(this);">Next</button>
                            <?php endif; ?>
                        </div>

                    </fieldset>
                    <!-- Edit Coupon Dialog -->
                    <div id="coupon-edit-dialog" class="w3-modal">
                        <div class="w3-modal-content">
                            <div class="w3-container w3-white">
                                <form method="post">
                                    <h2 class="w3-green">Edit Coupon</h2>
                                    <label for="coupon-edit-id">Code</label>
                                    <input type="text" class="w3-input w3-block w3-light-grey" name="coupon_edit_code" id="coupon-edit-id" readonly />
                                    <label for="coupon-edit-name">Name</label>
                                    <input type="text" class="w3-input w3-block" name="coupon_edit_name" id="coupon-edit-name" value="" />
                                    <fieldset>
                                        <legend>Metadata</legend>
                                        <span id="coupon-edit-metadata"></span>
                                    </fieldset>
                                    <input type="submit" class="w3-button w3-block w3-black" name="coupon_edit_submit" value="Submit" />
                                </form>
                                <br/>
                                <button class="w3-button w3-block w3-orange" onclick="document.getElementById('coupon-edit-dialog').style.display='none';">Cancel</button>
                                <br/>
                            </div>
                        </div>
                    </div>
                    <!-- Delete Coupon Dialog -->
                    <div id="coupon-delete-dialog" class="w3-modal">
                        <div class="w3-modal-content">
                            <div class="w3-container w3-white">
                                <h2 class="w3-red">Delete Coupon</h2>
                                <form method="post">
                                    <label for="coupon-delete-code">Coupon Code</label>
                                    <input type="text" name="coupon_code" id="coupon-delete-code" class="w3-input w3-block w3-light-grey" value="" readonly />
                                    <br/>
                                    <input type="submit" name="coupon_delete_submit" class="w3-button w3-block w3-red" value="Delete" />
                                </form>
                                <br/>
                                <button class="w3-button w3-block w3-orange" onclick="document.getElementById('coupon-delete-dialog').style.display='none';">Cancel</button>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Products: promotions -->
                <div id="products-promotions" class="products-categories" style="display:none;">
                    <fieldset>
                        <legend>Promotion(s)</legend>
                        <table class="w3-table w3-striped">
                            <thead>
                                <tr>
                                    <th>Created</th>
                                    <th>Coupon</th>
                                    <th>Code</th>
                                    <th>Amount/Percent</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="promotionsTable">
                                <?php if (count($promotion_codes) > 0) : ?>
                                    <?php foreach($promotion_codes->autoPagingIterator() as $promotion) : ?>
                                        <?php $base64 = base64_encode(json_encode($promotion)); ?>
                                        <?php 
                                            if (isset($promotion->coupon->percent_off) && !is_null($promotion->coupon->percent_off)) {
                                                $amount = htmlentities($promotion->coupon->percent_off) . '%'; 
                                            } else {
                                                $amount = '$' . number_format($promotion->coupon->amount_off / 100, 2);
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($promotion->created); ?></td>
                                            <td><?php echo htmlentities($promotion->coupon->id); ?></td>
                                            <td><?php echo htmlentities($promotion->code); ?></td>
                                            <td><?php echo htmlentities($amount); ?></td>
                                            <td>
                                                <button class="w3-button w3-blue" onclick="return viewPromoCode(this)" data-coupon="<?php echo $base64; ?>">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="promotions-pagination-controls" class="w3-center">
                            <?php if ($promotion_codes->has_more) : ?>
                                <button class="w3-button w3-blue" onclick="window.location.reload();">Reload</button>
                                &nbsp;
                                <button class="w3-button w3-blue" id="promotion-pagination-next" data-promotion-next="<?php echo $promotion_codes->data[count($promotion_codes->data) - 1]->id; ?>" onclick="updatePromotionCodes(this);">Next</button>
                            <?php endif; ?>
                        </div>

                    </fieldset>
                    <!-- View Promotion Dialog -->
                    <div id="promotion-view-dialog" class="w3-modal">
                        <div class="w3-modal-content">
                            <div class="w3-container w3-white">
                                <h2 class="w3-yellow">View Promotion</h2>
                                <form method="post">
                                    <span id="promotion-view-data"></span>
                                    <input type="submit" class="w3-button w3-block w3-black" name="promotion_view_sendemail" value="Resend Gift Card Coupon EMail" />
                                </form>
                                <br/>
                                <button class="w3-button w3-block w3-orange" onclick="document.getElementById('promotion-view-dialog').style.display='none';">Cancel</button>
                                <br/>
                            </div>
                        </div>
                    </div>
                    <!-- Update Promotion Dialog -->
                    <div id="promotion-update-dialog" class="w3-modal">
                        <div class="w3-modal-content">
                            <div class="w3-container w3-white">
                                <h2 class="w3-blue">Update Promotion</h2>
                                <form method="post">
                                    <input type="hidden" name="promotion_update_id" id="promotion-update-id" value="" />
                                    <label for="promotion-update-active">Active</label>
                                    <input type="checkbox" class="w3-input w3-block" name="promotion_update_active" id="promotion-update-active" value="true" />
                                    <label for="promotion-update-miinimum">Minimum Amount</label>
                                    <input type="number" class="w3-input w3-block" name="promotion_update_minimum" id="promotion-update-miinimum" stept="0.01" max="9999.99" value="" />
                                    <br/>
                                    <input type="submit" class="w3-button w3-block w3-black" name="promotion_update_submit" value="Submit" />
                                </form>
                                <br/>
                                <button class="w3-button w3-block w3-orange" onclick="document.getElementById('promotion-update-dialog').style.display='none';">Cancel</button>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>

        <div id="Newsletters" class="tabs" style="display:none">
            <fieldset>
                <legend>Newsletters &amp; Subscribers</legend>
                <div class="w3-bar w3-black">
                    <button class="w3-bar-item w3-button w3-pale-yellow newsletterstablinks" onclick="openTab(event,'newsletters-newsletters','newsletters-group','newsletterstablinks')">Newsletters&nbsp;<span id="total-newsletters" class="w3-badge w3-white">0</span></button>
                    <button class="w3-bar-item w3-button newsletterstablinks" onclick="openTab(event,'newsletters-subscribers','newsletters-group','newsletterstablinks')">Subscribers&nbsp;<span id="total-subscribers" class="w3-badge w3-white">0</span></button>
                </div>
                <!-- Newsletters: newsletters -->
                <div id="newsletters-newsletters" class="newsletters-group">
                    <fieldset>
                        <legend>Newsletters</legend>
                        <button class="w3-button w3-block w3-green" onclick="document.getElementById('newsletter-dialog').style.display='block';">Create New Newsletter</button>
                        <br/>
                        <table class="w3-table w3-stripe">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Release Date</th>
                                    <th>Distributed</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="newsletterTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="newsletter-pagination-controls" class="w3-center"></div>

                    </fieldset>
                </div>
                <!-- Newsletters: subscribers -->
                <div id="newsletters-subscribers" class="newsletters-group" style="display:none;">
                    <fieldset>
                        <legend>Subscribers</legend>
                        <table class="w3-table w3-stripe">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="subscribersTable">
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div id="subscribers-pagination-controls" class="w3-center"></div>

                    </fieldset>
                </div>
            </fieldset>
            <!-- NEWSLETTER RESEND DIALOG -->
            <div id="newsletter-resend-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <form method="post">
                            <h2 class="w3-yellow">Resend Newsletter</h2>
                            <input type="hidden" name="customer_id" id="customer-id" value="0" />
                            <label for="newsletter-id">Newsletter</label>
                            <select class="w3-input w3-block" id="newsletter-id" name="newsletter_id" required>
                            </select>
                            <br/>
                            <input type="submit" class="w3-button w3-block w3-black" name="resend_newsletter" value="Submit" />
                        </form>
                        <br/>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('newsletter-resend-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- NEWSLETTER DIALOG -->
            <div id="newsletter-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-green">Newsletter Creation</h2>
                        <form method="post">
                            <input type="hidden" name="newsletter_total_products" id="newsletter_total_products" value="1" />
                            <label for="newsletter_release_date">Release Date</label>
                            <input type="date" class="w3-input w3-block" id="newsletter_release_date" name="newsletter_release_date" value="" required />
                            <label for="newsletter_new_arrival_1">New Arrival Product</label>
                            <input type="text" class="w3-input w3-block" id="newsletter_new_arrival_1" name="newsletter_new_arrival_1" list="newsletter_products" required/>
                            <datalist id="newsletter_products">
                            </datalist>
                            <span id="newsletter_new_arrival_list"></span>
                            <br>
                            <input type="submit" class="w3-button w3-block w3-black" name="create_newsletter" value="Submit" />
                        </form>
                        <br/>
                        <button class="w3-button w3-block w3-blue" onclick="return addNewsletterProduct()">Add Product</button>
                        <br/>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('newsletter-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- NEWSLETTER PREVIEW -->
            <div id="newsletter-preview-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-yellow">Newsletter Preview</h2>
                        <label for="newsletter-preview-id">ID</label>
                        <input type="text" id="newsletter-preview-id" class="w3-input w3-block w3-light-grey" value="" readonly />
                        <label for="newsletter-preview-date">Release Date</label>
                        <input type="text" id="newsletter-preview-date" class="w3-input w3-block w3-light-grey" value="" readonly />
                        <label for="newsletter-preview-content">Preview Content</label>
                        <div id="newsletter-preview-content"></div>
                        <br/>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('newsletter-preview-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- NEWSLETTER DELETE DIALOG -->
            <div id="newsletter-delete-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <form method="post">
                            <h2 class="w3-red">Delete Newsletter</h2>
                            <label for="newsletter-delete-id">ID</label>
                            <input type="text" id="newsletter-delete-id" name="newsletter-delete-id" class="w3-input w3-block w3-light-grey" value="" readonly />
                            <label for="newsletter-delete-date">Release Date</label>
                            <input type="text" id="newsletter-delete-date" class="w3-input w3-block w3-light-grey" value="" readonly />
                            <br/>
                            <input type="submit" name="delete_newsletter" class="w3-button w3-block w3-black" value="Submit" />
                        </form>
                        <br/>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('newsletter-delete-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
        </div>

        <div id="Pages" class="tabs" style="display:none">
            <fieldset>
                <legend>Pages</legend>
                <input type="hidden" name="page_content_pageid" id="page_content_pageid" value="" />
                <label for="page">Page</label>
                <select class="w3-input w3-block" onchange="pageAction(this)" id="page" name="page">
                    <option value="">Select</option>
                    <?php foreach($settings as $key => $value) : ?>
                        <?php if (strpos($key, "content") !== false) : ?>
                            <?php $content = htmlspecialchars($value); ?>
                            <option value="<?php echo htmlentities($key); ?>" data-content="<?php echo base64_encode(json_encode($content)); ?>"><?php echo htmlentities($key); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <label for="page_content">Page Content</label>
                <textarea class="w3-input w3-block" rows="10" id="page_content" name="content"></textarea>
                <button class="w3-button w3-block w3-black" name="save_page_content" id="save_page_content">Save</button>
            </fieldset>
        </div>

        <div id="Users" class="tabs" style="display:none">
            <fieldset>
                <legend>Users&nbsp;<span id="total-users" class="w3-badge w3-black">0</span></legend>
                <p class="w3-panel">The users created are <b>NOT</b> customers but staff. Customer personal identity information (PII) is maintained on your STRIPE dashboard, making this eccommerce framework less of target for extracting customer information.</p>
                <button class="w3-button w3-block w3-green" onclick="document.getElementById('user-dialog').style.display='block';">Add New User</button>
                <table class="w3-table w3-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>EMail</th>
                            <th>Admin Status</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                    </tbody>
                </table>

                <!-- Pagination Links -->
                <div id="users-pagination-controls" class="w3-center"></div>
            </fieldset>
            <!-- USER DIALOG -->
            <div id="user-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-green">Add New User</h2>
                        <form method="post" enctype="multipart/form-data" action="admin.php">
                            <input type="hidden" name="action" value="add_user" />
                            <label for="user-username">User Name</label>
                            <input type="test" class="w3-input w3-block" name="username" id="user-username" value="" autocomplete="off" required />
                            <label for="user-email">EMail</label>
                            <input type="email" class="w3-input w3-block" name="email" id="user-email" value="" autocomplete="off" required />
                            <label for="user-password">Password</label>
                            <input type="password" class="w3-input w3-block" name="password" id="user-password" value="" autocomplete="off" required />
                            <label for="user-admin">Is Admin?</label>
                            <select id="user-admin" class="w3-input w3-block" name="is_admin">
                                <option value="0" selected>No</option>
                                <option value="1">Yes</option>
                            </select>
                            <label for="user-file">Control File</label>
                            <input type="file" class="w3-input w3-block" name="user-file" id="user-file" value="" required />
                            <br/>
                            <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                            <br/>
                        </form>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('user-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- EDIT USER DIALOG -->
            <div id="edit-user-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-green">Edit Existing User</h2>
                        <form method="post" enctype="multipart/form-data" action="admin.php">
                            <input type="hidden" name="action" value="update_user" />
                            <input type="hidden" name="id" id="edit-user-id" value="0" />
                            <label for="edit-user-username">User Name</label>
                            <input type="test" class="w3-input w3-block" name="username" id="edit-user-username" value="" autocomplete="off" required />
                            <label for="edit-user-email">EMail</label>
                            <input type="email" class="w3-input w3-block" name="email" id="edit-user-email" value="" autocomplete="off" required />
                            <label for="edit-user-password">Password</label>
                            <input type="password" class="w3-input w3-block w3-light-grey" name="password" id="edit-user-password" value="" autocomplete="off" readonly />
                            <label for="edit-user-admin">Is Admin?</label>
                            <select id="edit-user-admin" class="w3-input w3-block" name="is_admin" id="edit-user-admin">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <label for="edit-user-file">Control File</label>
                            <input type="file" class="w3-input w3-block" name="user-file" id="edit-user-file" value="" required />
                            <br/>
                            <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                            <br/>
                        </form>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('edit-user-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- VIEW USER DIALOG -->
            <div id="view-user-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-green">Add New User</h2>
                        <form>
                            <label for="view-user-username">User Name</label>
                            <input type="test" class="w3-input w3-block w3-light-grey" id="view-user-username" value="" readonly />
                            <label for="view-user-email">EMail</label>
                            <input type="email" class="w3-input w3-block w3-light-grey" id="view-user-email" value="" readonly />
                            <label for="view-user-password">Password</label>
                            <input type="password" class="w3-input w3-block w3-light-grey" id="view-user-password" value="" autocomplete readonly />
                            <label for="view-user-admin">Is Admin?</label>
                            <input type="test" class="w3-input w3-block w3-light-grey" id="view-user-admin" value="" readonly />
                            <label for="view-user-file">Control File</label>
                            <input type="text" class="w3-input w3-block w3-light-grey" id="view-user-file" value="" required />
                        </form>
                        <br/>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('view-user-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- USER PASSWORD DIALOG -->
            <div id="user-password-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <form method="post" action="admin.php">
                            <h2 class="w3-green">Change User Password</h2>
                            <input type="hidden" name="action" value="change_user_password" />
                            <input type="hidden" name="id" id="user-password-id" value="0" />
                            <label for="current-password">Current Password</label>
                            <input type="password" class="w3-input w3-block" name="current-password" id="current-password" autocomplete value="" required />
                            <label for="new-password">New Password</label>
                            <input type="password" class="w3-input w3-block" name="new-password" id="new-password" autocomplete value="" required />
                            <label for="repeat-password">Repeat New Password</label>
                            <input type="password" class="w3-input w3-block" name="repeat-password" id="repeat-password" autocomplete value="" required />
                            <br/>
                            <button type="submit" class="w3-button w3-block w3-black">Submit</button>
                            <br/>
                            <button class="w3-button w3-block w3-orange" onclick="document.getElementById('user-password-dialog').style.display='none';">Cancel</button>
                            <br/>
                        </form>
                    </div>
                </div>
            </div>
            <!-- USER DELETE DIALOG -->
            <div id="user-delete-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-red">Delete User</h2>
                        <form method="post" action="admin.php">
                            <label for="delete-user-name">Delete User</label>
                            <input type="text" class="w3-input w3-block" id="delete-user-name" value="" />
                            <input type="hidden" name="action" value="delete_user" />
                            <input type="hidden" name="id" id="delete-user-id" value="0" />
                            <input type="hidden" name="file" id="delete-user-file" value="" />
                            <button type="submit" class="w3-button w3-block w3-green">Yes</button>
                            <br/>
                        </form>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('user-delete-dialog').style.display='none';">No</button>
                        <br/>
                    </div>
                </div>
            </div>
            <!-- USER CODE EDITOR -->
            <div id="user-codeeditor-dialog" class="w3-modal">
                <div class="w3-modal-content">
                    <div class="w3-container w3-white">
                        <h2 class="w3-yellow">Code Editor</h2>
                        <form method="post" action="admin.php">
                            <input type="hidden" name="action" value="save_user_file" />
                            <input type="hidden" name="id" id="user-codeeditor-id" value="0" />
                            <label for="codeeditor-filename">File Name</label>
                            <input type="text" name="codeeditor-filename" id="codeeditor-filename" value="" class="w3-input w3-block w3-light-grey" readonly />
                            <label for="codeeditor-contents">Editor</label>
                            <textarea class="editor w3-block w3-input" rows="20" name="contents" id="codeeditor-contents" placeholder="File content will appear here..."></textarea>
                            <br/>
                            <button type="submit" class="w3-button w3-block w3-black">Save</button>
                            <br/>
                        </form>
                        <button class="w3-button w3-block w3-orange" onclick="document.getElementById('user-codeeditor-dialog').style.display='none';">Cancel</button>
                        <br/>
                    </div>
                </div>
            </div>
        </div>

        <div id="Settings" class="tabs" style="display:none">
            <fieldset>
                <legend>Settings</legend>
                <form method="post" action="admin.php">
                    <table class="w3-table w3-striped">
                        <tr>
                            <th>Name</th>
                            <th>Value</th>
                        </tr>
                        <tr>
                            <td>SQLite Database Path</td>
                            <td>
                                <input type="text" class="w3-input w3-block w3-light-grey" value="<?php echo $dbFile; ?>" readonly />
                                <a href="download.php" class="w3-button w3-block w3-green" download="shopecommerce.db">
                                    Download
                                </a>
                            </td>
                        </tr>
                    <?php foreach($settings as $key => $value) : ?>
                        <?php if (strpos($key, "content") === false) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($key); ?></td>
                            <td><input type="text" name="<?php echo htmlspecialchars($key); ?>" class="w3-input w3-block" value="<?php echo htmlspecialchars($value); ?>" /></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                        <tr>
                            <td colspan="2">
                                <input type="hidden" name="action" value="save_settings" />
                                <input type="submit" class="w3-button w3-block w3-black" value="Save" />
                            </td>
                        </tr>
                    </table>
                </form>
            </fieldset>
        </div>
    </div>
    <!-- ERROR DIALOG -->
    <div id="error-dialog" class="w3-modal">
        <div class="w3-modal-content">
            <div class="w3-container w3-white">
                <h2 class="w3-red" id="error-title">ERROR</h2>
                <p class="w3-panel" id="error-message"></p>
                <br/>
                <button class="w3-button w3-block w3-black" onclick="document.getElementById('error-dialog').style.display='none';">OK</button>
                <br/>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        const servicePackages = {
                "Priority Mail": [
                    "Flat Rate Envelope",
                    "Small Flat Rate Box",
                    "Medium Flat Rate Box",
                    "Small Flat Rate Envelope",
                    "Large Flat Rate Box",
                    "Padded Flat Rate Envelope",
                    "Window Flat Rate Envelope",
                    "Legal Flat Rate Envelope",
                    "Choose Your Own Box"
                ],
                "Priority Mail Express": [
                    "Flat Rate Envelope",
                    "Legal Flat Rate Envelope",
                    "Padded Flat Rate Envelope",
                    "Choose Your Own Box"
                ],
                "USPS Connect Local": [
                    "Choose Your Own Box",
                    "Large Flat Rate Bag",
                    "Flat Rate Box",
                    "Small Flat Rate Bag"
                ],
                "USPS Connect Local Mail": ["Choose Your Own Box"],
                "USPS Ground Advantage": ["Choose Your Own Box"],
                "USPS Ground Advantage Cubic": ["Choose Your Own Box"],
                "Priority Mail Cubic": ["Choose Your Own Box"]
        };

        window.onload = async function() {
            await loadTableData("tracker", btoa("WHERE status='Order Received'"), page = 1, limit = 5, await updateOrdersReceived);
            await loadTableData("tracker", btoa("WHERE status='Processing'"), page = 1, limit = 5, await updateOrdersProcessing);
            await loadTableData("tracker", btoa("WHERE status='Shipped'"), page = 1, limit = 5, await updateOrdersShipped);
            await loadTableData("tracker", btoa("WHERE status='Out for Delivery'"), page = 1, limit = 5, await updateOrdersOutForDelivery);
            await loadTableData("tracker", btoa("WHERE status='Delivered'"), page = 1, limit = 5, await updateOrdersDelivered);
            await loadTableData("products", btoa(""), page = 1, limit = 5, await updateProducts);
            await loadTableData("categories", btoa(""), page = 1, limit = 5, await updateCategories);
            await loadTableData("newsletters", btoa(""), page = 1, limit = 5, await updateNewsletters);
            await loadTableData("newsletter_subscriptions", btoa(""), page = 1, limit = 5, await updateSubscribers);
            await loadTableData("users", btoa(""), page = 1, limit = 5, await updateUsers);
        }
        // SEE OFFICIAL USER GUIDE REFERENCE: TABLINKS
        function openTab(evt, tabName, className="tabs", _tablinks="tablinks") {
            var i;
            var x = document.getElementsByClassName(className);
            for (i = 0; i < x.length; i++) {
                x[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName(_tablinks);
            
            for (i = 0; i < x.length; i++) {
                tablinks[i].className = tablinks[i].className.replace("w3-pale-yellow", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " w3-pale-yellow";
        }

        function showError(message,title="Error") {
            document.getElementById('error-message').innerHTML = message;
            document.getElementById('error-title').innerHTML = title;
            document.getElementById('error-dialog').style.display='block';
        }

        function orderAction(element) {
            const id = element.getAttribute("data-id");

            switch(element.value) {
                case 'status':
                    const status = document.getElementById(`status_${id}`).innerText;
                    const notes = document.getElementById(`notes_${id}`).innerText;
                    document.getElementById('order-id').value = id;
                    document.getElementById('order-status').value = status;
                    document.getElementById('order-notes').value = notes;
                    document.getElementById('status-dialog').style.display='block';
                    break;
                case 'view':
                    {
                        const order = JSON.parse(atob(element.getAttribute("data-order")));
                        
                        const myHeaders = new Headers();
                        myHeaders.append("Origin", location.origin);

                        const formdata = new FormData();
                        formdata.append("action", "session_retrieval");
                        formdata.append("session_id", `${order.session_id}`);

                        const requestOptions = {
                            method: "POST",
                            headers: myHeaders,
                            body: formdata,
                            redirect: "follow"
                        };

                        fetch("/ajax.php", requestOptions)
                        .then((response) => response.json())
                        .then(function(result){
                            <?php if ($settings['stripe_mode'] == "test") : ?>
                                const pi_url = `https://dashboard.stripe.com/test/payments/${result.session.payment_intent}`;
                            <?php else : ?>
                                const pi_url = `https://dashboard.stripe.com/payments/${result.session.payment_intent}`;
                            <?php endif; ?>
                            window.open(pi_url);
                        })
                        .catch((error) => console.error(error));
                    }
                    break;
                case 'usps':
                    {
                        const order = JSON.parse(atob(element.getAttribute("data-order")));

                        const myHeaders = new Headers();
                        myHeaders.append("Origin", location.origin);

                        const formdata = new FormData();
                        formdata.append("action", "session_retrieval");
                        formdata.append("session_id", `${order.session_id}`);

                        const requestOptions = {
                            method: "POST",
                            headers: myHeaders,
                            body: formdata,
                            redirect: "follow"
                        };

                        fetch("/ajax.php", requestOptions)
                        .then((response) => response.json())
                        .then(function(result){
                            console.log(result)
                            const rows = `<tr class="w3-card w3-padding w3-margin-bottom w3-white">
                                <td class="w3-padding-small"><strong>Session ID: </strong></td>
                                <td class="w3-padding-small"><input type="text" class="w3-input w3-block" name="session_id" value="${order.session_id}" readonly /></td>
                              </tr>
                              <tr class="w3-card w3-padding w3-margin-bottom w3-white">
                                <td class="w3-padding-small"><strong>Service Type: </strong></td>
                                <td>
                                    <select name="usps_svctype" id="usps_svctype" data-id="${order.id}" class="w3-select" onchange="updatePackageTypes(this)" required>
                                        <option value="">Select</option>
                                        <option value="Priority Mail">Priority Mail</option>
                                        <option value="Priority Mail Express">Priority Mail Express</option>
                                        <option value="USPS Connect Local">USPS Connect Local</option>
                                        <option value="USPS Connect Local Mail">USPS Connect Local Mail</option>
                                        <option value="USPS Ground Advantage">USPS Ground Advantage</option>
                                        <option value="USPS Ground Advantage Cubic">USPS Ground Advantage Cubic</option>
                                        <option value="Priority Mail Cubic">Priority Mail Cubic</option>
                                    </select>
                                </td>
                              </tr>
                              <tr class="w3-card w3-padding w3-margin-bottom w3-white">
                                <td class="w3-padding-small"><strong>Package Type: </strong></td>
                                <td>
                                    <select name="usps_pkgtype" id="usps_pkgtype" data-id="${order.id}" class="w3-select" onchange="updateUSPSRequiredFields(this)" required>
                                        <option value="">Select</option>
                                    </select>
                                </td>
                              </tr>
                              <tr class="w3-card w3-padding w3-margin-bottom w3-white">
                                <td class="w3-padding-small"><strong>Weight: </strong></td>
                                <td> <input type="text" name="usps_pkgwgtlb" value="" required /> lb <input type="text" name="usps_pkgwgtoz" value="" required /> oz</td>
                              </tr>
                              <tr class="w3-card w3-padding w3-margin-bottom w3-white">
                                <td class="w3-padding-small"><strong>Dimensions: </strong></td>
                                <td>
                                    <input type="text" name="usps_length" id="usps_length" value="" placeholder="length" />" x 
                                    <input type="text" name="usps_width" id="usps_width" value="" placeholder="width" />" x 
                                    <input type="text" name="usps_height" id="usps_height" value="" placeholder="height" />"
                                </td>
                              </tr>
                              <tr class="w3-card w3-padding w3-margin-bottom w3-white">
                                <td class="w3-padding-small"><strong>Girth: </strong></td>
                                <td> <input type="text" name="usps_girth" id="usps_girth" value="" />"</td>
                            </tr>`;

                            <?php if ($settings['stripe_mode'] == "test") : ?>
                                const pi_url = `https://dashboard.stripe.com/test/payments/${result.session.payment_intent}`;
                            <?php else : ?>
                                const pi_url = `https://dashboard.stripe.com/payments/${result.session.payment_intent}`;
                            <?php endif; ?>

                            document.getElementById('uspsOrdersTable').innerHTML = rows;
                            document.getElementById("usps_firstname").value = result.session.shipping_details.name;
                            document.getElementById("usps_addressline1").value = result.session.shipping_details.address.line1;
                            document.getElementById("usps_addressline2").value = result.session.shipping_details.address.line2;
                            document.getElementById("usps_addresstowncity").value = result.session.shipping_details.address.city;
                            document.getElementById("usps_state").value = result.session.shipping_details.address.state;
                            document.getElementById("usps_zipcode").value = result.session.shipping_details.address.postal_code;
                            document.getElementById("usps_viewcustomer").setAttribute("href", pi_url);
                            document.getElementById('export-usps-dialog').style.display='block';
                        })
                        .catch((error) => console.error(error));

                    }
                    break;
                case 'notify':
                    {
                        const order = JSON.parse(atob(element.getAttribute("data-order")));
                        document.getElementById("order-notification-sessionid").value = order.session_id;
                        document.getElementById('order-notification-dialog').style.display='block';
                    }
                    break;
                case 'delete':
                    const order = JSON.parse(atob(element.getAttribute("data-order")));

                    document.getElementById('order-delete-id').value = id;
                    document.getElementById('order-delete-session-id').value = order.session_id;
                    document.getElementById('order-delete-dialog').style.display='block';
                    break;
            }
            element.value = "";
            return false;
        }

        function userAction(element) {
            const id = element.getAttribute("data-id");

            switch(element.value) {
                case 'edit':
                    {
                        if (id == 1) {
                            showError("Cannot edit the main administrator account","Error Chainging Admiistrator User");
                        } else {
                            const user = JSON.parse(atob(element.getAttribute("data-user")))
                            document.getElementById("edit-user-id").value = user.id;
                            document.getElementById("edit-user-username").value = user.name;
                            document.getElementById("edit-user-email").value = user.email;
                            document.getElementById("edit-user-password").value = user.password;
                            document.getElementById("edit-user-admin").value = user.is_approved;
                            document.getElementById('edit-user-dialog').style.display='block';
                        }
                    }
                    break;
                case 'view':
                    {
                        const user = JSON.parse(atob(element.getAttribute("data-user")))
                        document.getElementById("view-user-username").value = user.name;
                        document.getElementById("view-user-email").value = user.email;
                        document.getElementById("view-user-password").value = user.password;
                        document.getElementById("view-user-admin").value = user.is_approved;
                        document.getElementById("view-user-file").value = user.file;
                        document.getElementById('view-user-dialog').style.display='block';
                    }
                    break;
                case 'delete':
                    if (id == 1) {
                        showError("Cannot delete the main administrator account","Error Deleting a User");
                    } else {
                        const user = JSON.parse(atob(element.getAttribute("data-user")));
                        document.getElementById('delete-user-id').value = id;
                        document.getElementById('delete-user-name').value = user.name;
                        document.getElementById('delete-user-file').value = user.file;
                        document.getElementById('user-delete-dialog').style.display='block';
                    }
                    break;
                case 'password':
                    document.getElementById("user-password-id").value = id;
                    document.getElementById('user-password-dialog').style.display='block';
                    break;
                case 'file':
                    if (id == 1) {
                        showError("Cannot edit the main administrator control file","Error Editing a User's Control File");
                    } else {
                        const user = JSON.parse(atob(element.getAttribute("data-user")));
                        const file = user.file;
                        document.getElementById('user-codeeditor-id').value = id;
                        document.getElementById('codeeditor-filename').value = user.file;

                        // Make an AJAX POST request to read the file
                        const myHeaders = new Headers();
                        myHeaders.append("Origin", location.origin); // Use location.origin dynamically                        

                        const formdata = new FormData();
                        formdata.append("action", "read_file");
                        formdata.append("file", file);

                        const requestOptions = {
                            method: "POST",
                            headers: myHeaders,
                            body: formdata,
                            redirect: "follow"
                        };

                        fetch("ajax.php", requestOptions)
                        .then((response) => response.json())
                        .then(function(data){
                            if (data.status === 'success') {
                                document.getElementById('codeeditor-contents').value = data.content;
                                document.getElementById('user-codeeditor-dialog').style.display='block';
                            } else {
                                alert(`Error: ${data.message}`);
                            }
                        })
                        .catch((error) => console.error(error));
                    }
                    break;
            }

            element.value = "";
        }

        function htmlentities(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        function decodeHtml(html) {
            var txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        }

        function pageAction(element) {
            const selectedOption = element.options[element.selectedIndex];
            const content = decodeHtml(JSON.parse(atob(selectedOption.getAttribute("data-content"))));
            document.getElementById("page_content").value = content;
            document.getElementById("page_content_pageid").value = element.value;
        }

        document.getElementById("save_page_content").addEventListener("click",function(e){
            e.preventDefault();
            const page_id = document.getElementById("page_content_pageid").value;
            const page_content = document.getElementById("page_content").value;

            const myHeaders = new Headers();
            myHeaders.append("Origin", location.origin); // Use location.origin dynamically                        

            const formdata = new FormData();
            formdata.append("action", "update_page_content");
            formdata.append("page_id", page_id);
            formdata.append("page_content", page_content);

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: formdata,
                redirect: "follow"
            };


            fetch("ajax.php", requestOptions)
            .then((response) => response.json())
            .then(function(data){
                window.location.reload();
            })
            .catch((error) => console.error(error));

        })

        function categoryAction(element) {
            const id = element.getAttribute("data-id");

            switch(element.value) {
                case 'edit':
                    {
                        const category = JSON.parse(atob(element.getAttribute("data-category")));
                        document.getElementById('category-id').value = id;
                        document.getElementById('category-name').value = category.name;
                        document.getElementById('category-provider').value = category.provider;
                        document.getElementById('update-category-dialog').style.display='block';
                    }
                    break;
                case 'delete':
                    {
                        const category = JSON.parse(atob(element.getAttribute("data-category")));
                        document.getElementById('delete-category-id').value = id;
                        document.getElementById('delete-category-name').value = category.name;
                        document.getElementById('category-delete-dialog').style.display='block';
                    }
                    break;
            }
            element.value="";
            return false;
        }
        function viewCoupon(element) {
            const base64 = element.getAttribute("data-coupon");
            const json = atob(base64);
            const coupon = JSON.parse(json);

            <?php if ($settings['stripe_mode'] == "test") : ?>
            const url = `https://dashboard.stripe.com/test/coupons/${coupon.id}`;
            <?php else : ?>
            const url = `https://dashboard.stripe.com/coupons/${coupon.id}`;
            <?php endif; ?>
            window.open(url);
            return false;
        }
        function editCoupon(element) {
            const base64 = element.getAttribute("data-coupon");
            const json = atob(base64);
            const coupon = JSON.parse(json);

            document.getElementById("coupon-edit-id").value = coupon.id;
            document.getElementById("coupon-edit-name").value = coupon.name;
            let tableHTML = "<table class='w3-table'><tr><th>Name</th><th>Value</th></tr>";
            Object.entries(coupon.metadata).forEach(([key, value]) => {
                tableHTML += `<tr>
                                <td>${key}</td>
                                <td><input type="text" class="w3-input" name="${key}" value="${value}" /></td>
                              </tr>`;
            });
            tableHTML += "</table>";
            document.getElementById("coupon-edit-metadata").innerHTML = tableHTML;
            document.getElementById('coupon-edit-dialog').style.display='block';
            return false;
        }
        function deleteCoupon(element) {
            const base64 = element.getAttribute("data-coupon");
            const json = atob(base64);
            const coupon = JSON.parse(json);

            document.getElementById("coupon-delete-code").value = coupon.id;
            document.getElementById('coupon-delete-dialog').style.display='block';
            return false;
        }
        function addNewsletterProduct() {
            let total = Number(document.getElementById("newsletter_total_products").value) + 1;
            document.getElementById("newsletter_new_arrival_list").innerHTML += `
            <label for="newsletter_new_arrival_${total}">New Arrival Product</label>
            <input type="text" class="w3-input w3-block" id="newsletter_new_arrival_${total}" name="newsletter_new_arrival_${total}" list="newsletter_products" required />
            `;
            document.getElementById("newsletter_total_products").value = total;
            return false;
        }
        function subscriberAction(element) {
            const cusid = element.getAttribute("data-cusid");

            switch(element.value) {
                case "view":
                    {
                        <?php if ($settings['stripe_mode'] == "test") : ?>
                        const url = `https://dashboard.stripe.com/test/customers/${cusid}`;
                        <?php else : ?>
                        const url = `https://dashboard.stripe.com/customers/${cusid}`;
                        <?php endif; ?>
                        window.open(url);
                    }
                    break;
                case "resend":
                    {
                        document.getElementById("customer-id").value = cusid;
                        document.getElementById('newsletter-resend-dialog').style.display='block';
                    }
                    break;
            }
            element.value = "";
        }
        function viewPromoCode(element) {
            const base64 = element.getAttribute("data-coupon");
            const json = atob(base64);
            const promotion = JSON.parse(json);
            const promocode_id = promotion.id;

            <?php if ($settings['stripe_mode'] == "test") : ?>
            const url = `https://dashboard.stripe.com/test/promotion_codes/${promocode_id}`;
            <?php else : ?>
            const url = `https://dashboard.stripe.com/promotion_codes/${promocode_id}`;
            <?php endif; ?>
            window.open(url);
        }

        function newsletterAction(element) {
            switch(element.value) {
                case 'view':
                    {
                        const id = element.getAttribute("data-id");
                        const release_date = element.getAttribute("data-date");
                        const base64 = element.getAttribute("data-content");
                        const json = atob(base64);
                        const content = JSON.parse(json);

                        document.getElementById("newsletter-preview-id").value = id;
                        document.getElementById("newsletter-preview-date").value = release_date;
                        document.getElementById("newsletter-preview-content").innerHTML = content;
                        document.getElementById('newsletter-preview-dialog').style.display='block';
                    }
                    break;
                case 'delete':
                    {
                        const distributed = element.getAttribute("data-distributed");
                        if (distributed == 1) {
                            document.getElementById("error-title").innerHTML = "Delete a Newsletter";
                            document.getElementById("error-message").innerHTML = "Cannot delete a newsletter already distributed!";
                            document.getElementById("error-dialog").style.display = "block";
                        } else {
                            // ok to delete
                            const id = element.getAttribute("data-id");
                            const release_date = element.getAttribute("data-date");
                            document.getElementById("newsletter-delete-id").value = id;
                            document.getElementById("newsletter-delete-date").value = release_date;
                            document.getElementById('newsletter-delete-dialog').style.display='block';
                        }
                    }
                    break;
            }

            element.value = "";
        }

        async function loadTableData(table, base64, page = 1, limit = 5, callback) {
            const filter = atob(base64);
            const myHeaders = new Headers();
            myHeaders.append("Origin", location.origin);

            const formdata = new FormData();
            formdata.append("action", "pagination");
            formdata.append("table", table);
            formdata.append("filter", filter);
            formdata.append("page", page);
            formdata.append("limit", limit);

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: formdata,
                redirect: "follow"
            };

            fetch("/ajax.php", requestOptions)
            .then((response) => response.json())
            .then(function(result){
                callback(result);
            })
            .catch(error => console.error(error));
        }

        async function updatePagination(element, table, filter, limit, totalPages, currentPage, callback) {
            let paginationHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                //paginationHTML += `<button onclick="loadTableData('${table}','${filter}',${i},${limit},${callback})">${i}</button> `;
                paginationHTML += `<button class="w3-button w3-blue" onclick="loadTableData('${table}','${filter}',${i},${limit},${callback})">${i}</button>&nbsp;`;
            }
            document.getElementById(element).innerHTML = paginationHTML;
        }

        async function updateOrdersReceived(response) {
            let rows = '';
            response.data.forEach(order => {
                const base64 = btoa(JSON.stringify(order));
                const notes = (order.notes) ? order.notes : "";
                rows += `<tr>
                            <td id="session_${order.id}">${order.session_id}</td>
                            <td id="status_${order.id}">${order.status}</td>
                            <td id="notes_${order.id}">${notes}</td>
                            <td>
                                <select 
                                    onchange='orderAction(this)' 
                                    class='w3-input' 
                                    data-id='${order.id}' 
                                    data-order='${base64}'>
                                    <option value=''>Select</option>
                                    <option value='status'>Status</option>
                                    <option value='view'>View</option>
                                    <option value='delete'>Delete</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('ordersReceivedTable').innerHTML = rows;
            document.getElementById("total-received").innerText = Number(response.total);

            const where = btoa("WHERE status='Order Received'");
            await updatePagination("orders-received-pagination-controls",
                             "tracker",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateOrdersReceived");
        }

        async function updateOrdersProcessing(response) {
            let rows = '';
            response.data.forEach(order => {
                const base64 = btoa(JSON.stringify(order));
                const notes = (order.notes) ? order.notes : "";
                rows += `<tr>
                            <td id="session_${order.id}">${order.session_id}</td>
                            <td id="status_${order.id}">${order.status}</td>
                            <td id="notes_${order.id}">${notes}</td>
                            <td>
                                <select 
                                    onchange='orderAction(this)' 
                                    class='w3-input' 
                                    data-id='${order.id}' 
                                    data-order='${base64}'>
                                    <option value=''>Select</option>
                                    <option value='status'>Status</option>
                                    <option value='view'>View</option>
                                    <option value='delete'>Delete</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('ordersProcessingTable').innerHTML = rows;
            document.getElementById("total-processing").innerText = Number(response.total);
            const where = btoa("WHERE status='Processing'");
            await updatePagination("orders-processing-pagination-controls",
                             "tracker",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateOrdersProcessing");
        }

        async function updateOrdersShipped(response) {
            let rows = '';
            let usps_rows = '';
            response.data.forEach(order => {
                const base64 = btoa(JSON.stringify(order));
                const notes = (order.notes) ? order.notes : "";
                rows += `<tr>
                            <td id="session_${order.id}">${order.session_id}</td>
                            <td id="status_${order.id}">${order.status}</td>
                            <td id="notes_${order.id}">${notes}</td>
                            <td>
                                <select 
                                    onchange='orderAction(this)' 
                                    class='w3-input' 
                                    data-id='${order.id}' 
                                    data-order='${base64}'>
                                    <option value=''>Select</option>
                                    <option value='status'>Status</option>
                                    <option value='view'>View</option>
                                    <option value='usps'>USPS Click-n-Ship Export</option>
                                    <option value='delete'>Delete</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('ordersShippedTable').innerHTML = rows;
            document.getElementById("total-shipped").innerText = Number(response.total);
            const where = btoa("WHERE status='Shipped'");
            await updatePagination("orders-shipped-pagination-controls",
                             "tracker",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateOrdersShipped");
        }

        async function updateOrdersOutForDelivery(response) {
            let rows = '';
            response.data.forEach(order => {
                const base64 = btoa(JSON.stringify(order));
                const notes = (order.notes) ? order.notes : "";
                rows += `<tr>
                            <td id="session_${order.id}">${order.session_id}</td>
                            <td id="status_${order.id}">${order.status}</td>
                            <td id="notes_${order.id}">${notes}</td>
                            <td>
                                <select 
                                    onchange='orderAction(this)' 
                                    class='w3-input' 
                                    data-id='${order.id}' 
                                    data-order='${base64}'>
                                    <option value=''>Select</option>
                                    <option value='status'>Status</option>
                                    <option value='view'>View</option>
                                    <option value='notify'>Notify</option>
                                    <option value='delete'>Delete</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('ordersOutForDeliveryTable').innerHTML = rows;
            document.getElementById("total-outfordelivery").innerText = Number(response.total);
            const where = btoa("WHERE status='Out for Delivery'");
            await updatePagination("orders-outfordelivery-pagination-controls",
                             "tracker",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateOrdersOutForDelivery");
        }

        async function updateOrdersDelivered(response) {
            let rows = '';
            response.data.forEach(order => {
                const base64 = btoa(JSON.stringify(order));
                const notes = (order.notes) ? order.notes : "";
                rows += `<tr>
                            <td id="session_${order.id}">${order.session_id}</td>
                            <td id="status_${order.id}">${order.status}</td>
                            <td id="notes_${order.id}">${notes}</td>
                            <td>
                                <select 
                                    onchange='orderAction(this)' 
                                    class='w3-input' 
                                    data-id='${order.id}' 
                                    data-order='${base64}'>
                                    <option value=''>Select</option>
                                    <option value='status'>Status</option>
                                    <option value='view'>View</option>
                                    <option value='delete'>Delete</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('ordersDeliveredTable').innerHTML = rows;
            document.getElementById("total-delivered").innerText = Number(response.total);
            const where = btoa("WHERE status='Delivered'");
            await updatePagination("orders-delivered-pagination-controls",
                             "tracker",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateOrdersDelivered");
        }

        async function updateProducts(response) {
            let rows = '';
            let list = '';
            let image = 'No image';

            response.data.forEach(product => {
                if (product.image) {
                    image = `<img src="${product.image}" alt="${product.name}" style="width: 50px; height: 50px;">`;
                }
                rows += `<tr>
                            <td>${product.id}</td>
                            <td>${product.name}</td>
                            <td>${product.price.toFixed(2)}</td>
                            <td>${image}</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="${product.id}">
                                    <button type="submit" class="w3-button w3-red">Delete</button>
                                </form>
                                &nbsp;
                                <button class="w3-button w3-blue" onclick="document.getElementById('product-${product.id}').style.display='block';">Update</button>
                            </td>
                        </tr>
                        <!--  Product ${product.name} Dialog -->
                        <div id="product-${product.id}" class="w3-modal">
                            <div class="w3-modal-content">
                                <div class="w3-container w3-white">
                                    <h2 class="w3-blue">Product Update</h2>
                                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                                        <input type="hidden" name="action" value="update">
                                        <label for="id-${product.id}">Product ID</label>
                                        <input class="w3-input w3-light-grey" type="text" name="id" id="id-${product.id}" value="${product.id}" readonly>
                                        <label for="name-${product.id}">Product Name</label>
                                        <input class="w3-input" type="text" id="name-${product.id}" name="name" value="${product.name}" required>
                                        <label for="name">Product Price</label>
                                        <input class="w3-input" type="number" step="0.01" name="price" value="${product.price}" required>
                                        <label for="name">Product Tag</label>
                                        <input class="w3-input" type="text" name="tag" value="${product.tag}" />
                                        <label for="name">Product On Homepage</label>
                                        <input class="w3-input" type="number" name="homepage" value="${product.homepage}"/>
                                        <label for="name">Product Image</label>
                                        <br/>
                                        ${image}&nbsp;
                                        <input class="w3-input" type="file" name="image" accept="image/*">
                                        <label for="available_date">Availability Date</label>
                                        <input class="w3-input" type="datetime" name="available_date" id="available_date" value="${product.available_date}" required>
                                        <label for="price-${product.id}">Stripe Price ID</label>
                                        <input class="w3-input w3-light-grey" type="text" name="stripe_id" id="price-${product.id}" value="${product.stripe_id}" readonly>
                                        <button type="submit" class="w3-button w3-block w3-blue">Update</button>
                                    </form>
                                    <br/>
                                    <button class="w3-button w3-block w3-orange" onclick="document.getElementById('product-${product.id}').style.display='none';">Cancel</button>
                                    <br/>
                                </div>
                            </div>
                        </div>`;

                list += `<option>${product.name}</option>`;
            });
            document.getElementById("productTable").innerHTML = rows;
            document.getElementById("newsletter_products").innerHTML = list;
            document.getElementById("total-products").innerText = Number(response.total);
            const where = btoa("");
            await updatePagination("product-pagination-controls",
                             "categories",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateProducts");
        }

        async function updateCategories(response) {
            let rows = '';
            let list = '<option value="">Select</option>';
            let image = 'No image';

            response.data.forEach(category => {
                if (category.image) {
                    image = `<img src="${category.image}" alt="${category.name}" style="height: 50px;" />`;
                }
                const base64 = btoa(JSON.stringify(category));
                rows += `<tr>
                            <td>${image}</td>
                            <td>${category.name}</td>
                            <td>${category.provider}</td>
                            <td>
                                <select class="w3-input" onclick="categoryAction(this)" 
                                    data-id="${category.id}" 
                                    data-category="${base64}">
                                    <option value="">Select</option>
                                    <option value="edit">Edit</option>
                                    <option value="delete">Delete</option>
                                </select>
                            </td>
                         </tr>`;

                list += `<option value='${category.id}'>${category.name}</option>`;
            });
            document.getElementById("categoryTable").innerHTML = rows;
            document.getElementById("category").innerHTML = list;
            document.getElementById("import-product-category").innerHTML = list;
            document.getElementById("total-categories").innerText = Number(response.total);
            const where = btoa("");
            await updatePagination("category-pagination-controls",
                             "categories",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateCategories");
        }

        async function updateNewsletters(response) {
            let rows = '';
            let list = '<option value="">Select</option>';
            let distributed = 'No';
            
            response.data.forEach(newsletter => {
                if (newsletter.distributed == 1) {
                    distributed = 'Yes';
                }
                rows += `<tr>
                            <td>${newsletter.id}</td>
                            <td>${newsletter.release_date}</td>
                            <td>${distributed}</td>
                            <td>
                                <select class="w3-input" onclick="newsletterAction(this)" data-id="${newsletter.id}" data-distributed="${newsletter.distributed}" data-date="${newsletter.release_date}" data-content="${newsletter.content}">
                                    <option value="">Select</option>
                                    <option value="view">View</option>
                                    <option value="delete">Delete</option>
                                </select>
                            </td>
                        </tr>`;

                if (newsletter.distributed == 1) {
                    list += `<option value="${newsletter.id}">${newsletter.release_date}</option>`;
                }
            });
            document.getElementById("newsletterTable").innerHTML = rows;
            document.getElementById("newsletter-id").innerHTML = list;
            document.getElementById("total-newsletters").innerText = Number(response.total);
            const where = btoa("");
            await updatePagination("newsletter-pagination-controls",
                             "newsletters",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateNewsletters");
        }

        async function updateSubscribers(response) {
            let rows = '';
            response.data.forEach(subscriber => {
                rows += `<tr>
                            <td>${subscriber.stripe_customer_id}</td>
                            <td>
                                <select class="w3-input w3-block" onclick="subscriberAction(this)" data-date="${subscriber.release_date}" data-cusid="${subscriber.stripe_customer_id}">
                                    <option value="">Select</option>
                                    <option value="view">View</option>
                                    <option value="resend">Resend</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('subscribersTable').innerHTML = rows;
            document.getElementById("total-subscribers").innerText = Number(response.total);
            const where = btoa("");
            await updatePagination("subscribers-pagination-controls",
                             "newsletter_subscriptions",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateSubscribers");
        }

        async function updateUsers(response) {
            let rows = '';
            response.data.forEach(subscriber => {
                const base64 = btoa(JSON.stringify(subscriber));
                let is_approved = "N";
                if (subscriber.is_approved == 1) {
                    is_approved = "Y";
                }
                rows += `<tr>
                            <td id="name_${subscriber.id}">${subscriber.name}</td>
                            <td id="email_${subscriber.id}">${subscriber.email}</td>
                            <td id="admin_${subscriber.id}">${is_approved}</td>
                            <td id="file_${subscriber.id}">${subscriber.file}</td>
                            <td>
                                <select onchange="userAction(this)" 
                                    class="w3-input" 
                                    data-id="${subscriber.id}"  
                                    data-user="${base64}">
                                    <option value="">Select</option>
                                    <option value="edit">Edit</option>
                                    <option value="view">View</option>
                                    <option value="password">Change Password</option>
                                    <option value="file">Edit File</option>
                                    <option value="delete">Delete</option>
                                </select>
                            </td>
                         </tr>`;
            });
            document.getElementById('usersTable').innerHTML = rows;
            document.getElementById("total-users").innerText = Number(response.total);
            const where = btoa("");
            await updatePagination("users-pagination-controls",
                             "users",
                             where,
                             response.limit,
                             response.totalPages, 
                             response.currentPage,
                             "updateUsers");
        }

        async function updateDisputes(element) {
            const starting_after = element.getAttribute("data-dispute-next");

            const myHeaders = new Headers();
            myHeaders.append("Origin", location.origin);

            const formdata = new FormData();
            formdata.append("action", "stripe_dispute_pagination");            
            formdata.append("starting_after", `${starting_after}`);

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: formdata,
                redirect: "follow"
            };

            fetch("/ajax.php", requestOptions)
            .then((response) => response.json())
            .then(function(result){
                if (result.data.length > 0) {
                    let row = '';

                    result.data.forEach(data => {
                        const base64 = btoa(JSON.stringify(data));
                        row += `<tr>
                                    <td>${data.id}</td>
                                    <td>${Number(data.amount / 100).toFixed(2)}</td>
                                    <td>${data.evidence_details.due_by}</td>
                                    <td>
                                        <select class="w3-input" onclick="return disputeAction(this)" data-id="${data.id}" data-dispute="${base64}">
                                            <option value="">Select</option>
                                            <option value="view">View</option>
                                        </select>
                                    </td>
                                </tr>`;
                    })
                    document.getElementById("disputesTable").innerHTML = row;
                    element.setAttribute("data-dispute-next", result.data[result.data.length - 1].id);
                } else {
                    alert(`No more coupons exist!`);
                }
            })
            .catch(function(error){
                console.log(error);
            });
        }

        /**
         * The ending_before parameter returns objects listed before the named object. 
         * The starting_after parameter returns objects listed after the named object.
         */
        async function updateCoupons(element) {
            const starting_after = element.getAttribute("data-coupon-next");

            const myHeaders = new Headers();
            myHeaders.append("Origin", location.origin);

            const formdata = new FormData();
            formdata.append("action", "stripe_coupon_pagination");            
            formdata.append("starting_after", `${starting_after}`);

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: formdata,
                redirect: "follow"
            };

            fetch("/ajax.php", requestOptions)
            .then((response) => response.json())
            .then(function(result){
                if (result.data.length > 0) {
                    let row = '';

                    result.data.forEach(data => {
                        const base64 = btoa(JSON.stringify(data));
                        let amount = '';
                        if (data.percent_off) {
                            amount = `${data.percent_off}%`;
                        } else {
                            amount = `$${Number(data.amount_off / 100).toFixed(2)}`;
                        }
                        row += `<tr>
                                    <td>${data.created}</td>
                                    <td>${data.id}</td>
                                    <td>${data.name}</td>
                                    <td>${amount}</td>
                                    <td>
                                        <button class="w3-button w3-blue" onclick="return viewCoupon(this)" data-coupon="${base64}">View</button>
                                        &nbsp;
                                        <button class="w3-button w3-green" onclick="return editCoupon(this)" data-coupon="${base64}">Edit</button>
                                        &nbsp;
                                        <button class="w3-button w3-red" onclick="return deleteCoupon(this)" data-coupon="${base64}">Delete</button>
                                    </td>
                                </tr>`;
                    })
                    document.getElementById("couponsTable").innerHTML = row;
                    element.setAttribute("data-coupon-next", result.data[result.data.length - 1].id);
                } else {
                    alert(`No more coupons exist!`);
                }
            })
            .catch(function(error){
                console.log(error);
            });
        }

        async function updatePromotionCodes(element) {
            const starting_after = element.getAttribute("data-promotion-next");

            const myHeaders = new Headers();
            myHeaders.append("Origin", location.origin);

            const formdata = new FormData();
            formdata.append("action", "stripe_promotion_pagination");            
            formdata.append("starting_after", `${starting_after}`);

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: formdata,
                redirect: "follow"
            };

            fetch("/ajax.php", requestOptions)
            .then((response) => response.json())
            .then(function(result){
                if (result.data.length > 0) {
                    let row = '';

                    result.data.forEach(data => {
                        const base64 = btoa(JSON.stringify(data));
                        let amount = '';
                        if (data.coupon.percent_off) {
                            amount = `${data.coupon.percent_off}%`;
                        } else {
                            amount = `$${Number(data.coupon.amount_off / 100).toFixed(2)}`;
                        }
                        row += `<tr>
                                    <td>${data.created}</td>
                                    <td>${data.coupon.id}</td>
                                    <td>${data.code}</td>
                                    <td>${amount}</td>
                                    <td>
                                        <button class="w3-button w3-blue" onclick="return viewPromoCode(this)" data-coupon="${base64}">View</button>
                                    </td>
                                </tr>`;
                    })
                    document.getElementById("promotionsTable").innerHTML = row;
                    element.setAttribute("data-promotion-next", result.data[result.data.length - 1].id);
                } else {
                    alert(`No more coupons exist!`);
                }
            })
            .catch(function(error){
                console.log(error);
            });
        }

        function disputeAction(element) {
            const mode = "<?php echo $settings['stripe_mode']; ?>";
            const dispute = JSON.parse(atob(element.getAttribute("data-dispute")));

            switch(element.value) {
                case 'view':
                    {
                        let url = `https://dashboard.stripe.com/disputes/${dispute.id}`;
                        if (mode == "test") {
                            url = `https://dashboard.stripe.com/test/disputes/${dispute.id}`;
                        }
                        window.open(url);
                    }
                    break;
            }
            element.value = "";
            return false;
        }

        function updatePackageTypes(element) {
            const serviceType = document.getElementById(`usps_svctype`).value;
            const packageTypeSelect = document.getElementById(`usps_pkgtype`);

            // Clear previous options
            packageTypeSelect.innerHTML = '<option value="">Select</option>';

            if (serviceType && servicePackages[serviceType]) {
                servicePackages[serviceType].forEach(package => {
                    let option = document.createElement("option");
                    option.value = package;
                    option.textContent = package;
                    packageTypeSelect.appendChild(option);
                });
            }
        }

        function updateUSPSRequiredFields(element) {
            if (element.value == "Choose Your Own Box") {
                document.getElementById(`usps_length`).setAttribute("required","required");
                document.getElementById(`usps_width`).setAttribute("required","required");
                document.getElementById(`usps_height`).setAttribute("required","required");
                document.getElementById(`usps_girth`).setAttribute("required","required");
            } else {
                document.getElementById(`usps_length`).removeAttribute("required");
                document.getElementById(`usps_width`).removeAttribute("required");
                document.getElementById(`usps_height`).removeAttribute("required");
                document.getElementById(`usps_girth`).removeAttribute("required");
            }
        }

        function importProductChange(element) {
            const selected = element.selectedOptions[0];
            const product = JSON.parse(atob(selected.getAttribute("data-product")));

            if (product.images[0]) {
                // SEE OFFICIAL USER GUIDE: CHANGING INPUT ELEMENT DYNAMICALLY
                document.getElementById("import-product-image").setAttribute("type","text");
                document.getElementById("import-product-image").value = product.images[0];
                document.getElementById("import-product-image").setAttribute("readonly","readonly");
                // SEE OFFICIAL USER GUIDE: CHANGING CLASS NAME DYNAMICALLY
                document.getElementById("import-product-image").classList.add("w3-light-grey");
            } else {
                // SEE OFFICIAL USER GUIDE: CHANGING INPUT ELEMENT DYNAMICALLY
                document.getElementById("import-product-image").setAttribute("type","file");
                document.getElementById("import-product-image").removeAttribute("readonly");
                // SEE OFFICIAL USER GUIDE: CHANGING CLASS NAME DYNAMICALLY
                document.getElementById("import-product-image").classList.remove("w3-light-grey");
            }

        }
    </script>
</body>
</html>
