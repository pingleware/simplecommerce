<?php
require "settings.php";
require "functions.php"; 

// Set the response header as JSON
header('Content-Type: application/json');

// Handle CORS (Cross-Origin Resource Sharing)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');

// Handle the request method and route
$method = $_SERVER['REQUEST_METHOD'];
$path = explode("?",$_SERVER['REQUEST_URI'])[1]; // ?? '/';
$path = explode("&",$path)[0];

function authenticate() {
    require "connect.php";
    require "vendor/autoload.php";
    $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
    $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
    $stripe_secret = $stripe_test_secret;
    $stripe_mode = $settings['stripe_mode'];
    if ($stripe_mode == "live") {
        $stripe_secret = $stripe_live_secret;
    }
    $stripe = new \Stripe\StripeClient($stripe_secret);

    
    if (isset($_SERVER['HTTP_EMAIL'])) {
        $result = $db->query("SELECT * FROM newsletter_subscriptions");
        while ($subscriber = $result->fetchArray(SQLITE3_ASSOC)) {
            $customer = $stripe->customers->retrieve($subscriber['stripe_customer_id'], []);
            if ($customer->email == htmlentities($_SERVER['HTTP_EMAIL'])) {
                return true;
            }
        }
    }
    return false;
}

function authenticate_shipstation($username,$password) {
    require "connect.php";

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();

    // Fetch the user from the database
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if (password_verify($password, $user['password'])) {
        return true;
    }
    return false;
}

switch ($path) {
    case 'products':
        if ($method === 'GET') {
            if (authenticate()) {
                $result = $db->query('SELECT * FROM products');
                $products = [];
                while ($product = $result->fetchArray(SQLITE3_ASSOC)) {
                    $products[] = $product;
                }
                echo json_encode(['products' => $products]);    
            } else {
                echo json_encode(['products' => []]);    
            }
        } elseif ($method === 'POST') {

        } elseif ($method === 'PUT') {

        } elseif ($method === 'DELETE') {

        } elseif ($method === 'OPTIONS') {
            
        }
        break;
    case 'categories':
        if ($method === 'GET') {
            if (authenticate()) {
                $result = $db->query('SELECT * FROM categories');
                $categories = [];
                while($category = $result->fetchArray(SQLITE3_ASSOC)) {
                    $categories[] = $category;
                }
                echo json_encode(['categories' => $categories]);
            } else {
                echo json_encode(['categories' => []]);
            }
        } elseif ($method === 'POST') {

        } elseif ($method === 'PUT') {

        } elseif ($method === 'DELETE') {

        } elseif ($method === 'OPTIONS') {
            
        }
        break;
    case 'invoice':
        if ($method === 'GET') {
            if (authenticate()) {
                parse_str($_SERVER['REQUEST_URI'], $params);
                echo json_encode(['invoice' => $params]);
            } else {
                echo json_encode(['invoice' => []]);
            }
        } elseif ($method === 'POST') {

        } elseif ($method === 'PUT') {

        } elseif ($method === 'DELETE') {

        } elseif ($method === 'OPTIONS') {
            
        }
        break;
    case 'shipstation':
        if ($method === 'GET') {
            $arguments = explode("&",$_SERVER['REQUEST_URI']);
            // Remove "shipstation" since it's not a key-value pair
            $query = preg_replace('/^shipstation&/', '', $_SERVER['REQUEST_URI']);

            // Parse query string into variables
            parse_str($query, $params);

            // Assign to individual variables
            $SS_UserName = $params['SS-UserName'] ?? null;
            $SS_Password = $params['SS-Password'] ?? null;
            $action = $params['action'] ?? null;
            $start_date = $params['start_date'] ?? null;
            $end_date = $params['end_date'] ?? null;  
            
            switch($action) {
                case 'export':
                    {
                        /**
                         * Provide ONLY orders in SHIPPED status
                         */
                        $stmt = $db->prepare("SELECT * FROM tracker WHERE status=:status");
                        $stmt->bindValue(":status","Shipped");
                        $result = $stmt->execute();
                        $shipped_orders = $result->fetchArray(SQLITE3_ASSOC);

                        $xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><Orders></Orders>");  // Initial Orders root element

                        if (!is_array($shipped_orders)) {
                            $xml->addAttribute("pages", 0); 
                            echo $xml->asXML();
                            return;
                        }

                        require "vendor/autoload.php";
                        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
                        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
                        $stripe_secret = $stripe_test_secret;
                        $stripe_mode = $settings['stripe_mode'];
                        if ($stripe_mode == "live") {
                            $stripe_secret = $stripe_live_secret;
                        }
                        $stripe = new \Stripe\StripeClient($stripe_secret);
                
                        $start_date = strtotime($start_date);
                        $end_date = strtotime($end_date);

                        $payments = $stripe->paymentIntents->search(['query' => "created>$start_date AND created<$end_date"]);

                        $invoices = [];
                        foreach($payments as $payment) {
                            if (isset($payment->invoice)) {
                                $invoice = $stripe->invoices->retrieve($payment->invoice, []);
                                $invoices[] = $invoice;
                            }
                        }


                        $pages = 0;
                        foreach ($invoices as $index => $invoice) {
                            // Add Orders element with pages attribute
                            $order = $xml->addChild("Order");
                            $pages = $index + 1;
                            //$order->addAttribute("pages", $index + 1); // Pages start from 1

                            // Add Order details
                            addCdata("OrderID",$invoice["id"], $order);
                            addCdata("OrderNumber", $invoice["number"], $order);
                            $order->addChild("OrderDate", date("m/d/Y h:i A", $invoice["created"]));
                            $order->addChild("LastModified", date("m/d/Y h:i A"));
                            addCdata("OrderStatus", "paid", $order);
                            $order->addChild("CurrencyCode", strtoupper($invoice["currency"]));
                            $order->addChild("OrderTotal", number_format($invoice["amount_paid"] / 100, 2));

                            // Customer details
                            $customer = $order->addChild("Customer");
                            addCdata("CustomerCode", $invoice["customer"], $customer);

                            $billTo = $customer->addChild("BillTo");
                            addCdata("Name", $invoice["customer_name"], $billTo);
                            addCdata("Phone", "", $billTo);
                            addCdata("Email", $invoice["customer_email"], $billTo);

                            $shipTo = $customer->addChild("ShipTo");
                            addCdata("Name", $invoice["customer_name"], $shipTo);
                            addCdata("Address1", $invoice["customer_address"]["line1"], $shipTo);
                            addCdata("Address2", $invoice["customer_address"]["line2"], $shipTo);
                            addCdata("City", $invoice["customer_address"]["city"], $shipTo);
                            addCdata("State", $invoice["customer_address"]["state"], $shipTo);
                            addCdata("PostalCode", $invoice["customer_address"]["postal_code"], $shipTo);
                            addCdata("Country", $invoice["customer_address"]["country"], $shipTo);

                            // Line items
                            $items = $order->addChild("Items");
                            foreach ($invoice["lines"]["data"] as $lineItem) {
                                $quantity = $lineItem["quantity"];
                                $unitprice = ($lineItem["amount"] / 100) / $quantity;
                                $item = $items->addChild("Item");
                                $item->addChild("Name", htmlspecialchars($lineItem["description"]));
                                $item->addChild("Quantity", $quantity);
                                $item->addChild("UnitPrice", number_format($unitprice, 2));
                                $item->addChild("SKU", $lineItem["id"]);
                            }
                        }

                        // Add the 'pages' attribute to the root 'Orders' element
                        $xml->addAttribute("pages", $pages); // Add any value as needed

                        // Output the final XML
                        echo $xml->asXML();

                        //echo json_encode($invoice);
                    }
                    break;
                default:
            }
            // action=export&start_date=01%2f23%2f2012+17%3a28&end_date=01%2f23%2f2012+17%3a33&page=1 
        } elseif ($method === 'POST') {
            $arguments = explode("&",$_SERVER['REQUEST_URI']);
            $action = explode("=",$arguments[1])[1];
            switch($action) {
                case 'shipnotify':
                    {
                        require "vendor/autoload.php";
                        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
                        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
                        $stripe_secret = $stripe_test_secret;
                        $stripe_mode = $settings['stripe_mode'];
                        if ($stripe_mode == "live") {
                            $stripe_secret = $stripe_live_secret;
                        }
                        $stripe = new \Stripe\StripeClient($stripe_secret);

                        $order_number = explode("=",$arguments[2])[1];
                        $carrier = explode("=",$arguments[3])[1];
                        $service = explode("=",$arguments[4])[1];
                        $tracking_number = explode("=",$arguments[5])[1];

                        $result = $stripe->paymentIntents->update(
                            $order_number,
                            [
                                'metadata' => [
                                    'carrier' => $carrier, 
                                    'service' => $service,
                                    'tracking_number' => $tracking_number
                                ]
                            ]
                        );

                        if ($result->invoice) {
                            $stripe->invoices->update(
                                $result->invoice,
                                [
                                    'metadata' => [
                                        'carrier' => $carrier, 
                                        'service' => $service,
                                        'tracking_number' => $tracking_number
                                    ]
                                ]
                            );    
                        }

                        // Send email to customer with courier, service and tracking number.

                        // Change fulfillment status to OUT_FOR_DELIVERY

                        echo json_encode($result);
                    }
                    break;
                default:
            }
        } elseif ($method === 'PUT') {

        } elseif ($method === 'DELETE') {

        } elseif ($method === 'OPTIONS') {
            
        }
        break;      
    default:
        // Invalid endpoint
        $paths = explode("/",$path);
        echo json_encode(['error' => 'Invalid endpoint', 'endpoint' => $path, 'server' => $_SERVER]);
        break;
}
?>