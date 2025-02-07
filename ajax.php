<?php
require 'vendor/autoload.php';
require 'settings.php';
require 'connect.php';
require 'functions_admin.php';

// Always set the Content-Type header to JSON
header('Content-Type: application/json');

// Get the current host dynamically
$currentHost = $_SERVER['HTTP_HOST']; // Example: "example.com"


// Check the Origin header
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];

    // Validate the origin against the current host
        // Allow requests from the current host
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Allow-Credentials: true");

        // Handle the request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Preflight request; respond with 200 OK
            http_response_code(200);
            exit;
        }

        // Your AJAX processing logic here
        // Main actions
        if (isset($_POST['action']) && $_POST['action'] === 'read_file') {
            $file = $_POST['file'] ?? '';

            // Validate file: allow only specific PHP files in a defined directory
            $allowedDir = __DIR__; // Directory containing editable PHP files
            $realFilePath = realpath($allowedDir . basename($file)); // Resolve real path
            if (file_exists($file)) {
                // Send file content to the editor
                $content = file_get_contents(basename($file));
                echo json_encode([
                    'status' => 'success',
                    'file' => $file,
                    'content' => $content
                ]);
            } else {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid or unauthorized file',
                    'realpath' => $realFilePath
                ]);
            }
        } else if (isset($_POST['action']) && $_POST['action'] === 'update_page_content') {
            $page = $_POST['page_id'];
            $content = $_POST['page_content'];

            $settings[$page] = $content;

            $stmt = $db->prepare('UPDATE settings SET value=:value WHERE name=:name');
            $stmt->bindValue(':name',$page);
            $stmt->bindValue(':value',$content);
            $stmt->execute();
    
            echo json_encode([
                "success" => true,
                "page" => $page,
                "content" => $content
            ]);
        } else if (isset($_POST['action']) && $_POST['action'] === 'session_retrieval') {
            require 'settings.php';

            $session_id = $_POST['session_id'];

            $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
            $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
            $stripe_secret = $stripe_test_secret;
            $stripe_mode = $settings['stripe_mode'];
            if ($stripe_mode == "live") {
                $stripe_secret = $stripe_live_secret;
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.stripe.com/v1/checkout/sessions/$session_id",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $stripe_secret"
              ),
            ));
            
            $session = json_decode(curl_exec($curl),true);
            
            curl_close($curl);

            // RETRIEVE AN INVOICE PDF LINK
            $invoice_id = $session['invoice'];
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.stripe.com/v1/invoices/$invoice_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $stripe_secret"
            ),
            ));

            $invoice = json_decode(curl_exec($curl), true);

            curl_close($curl);

            $invoice_pdf_url = $invoice['invoice_pdf'];
            $line_items = $invoice['lines']['data'];

            $session['invoice_pdf'] = $invoice_pdf_url;
            $session['line_items'] = $line_items;
            $session['order_asembly_guide'] = "uploads/oag.pdf";

            echo json_encode([
                "success" => true,
                "session" => $session
            ]);
        } else if (isset($_POST['action']) && $_POST['action'] === 'remove_cart_item') {
            $cart_index = htmlentities($_POST['cart_index']);
            if (isset($_SESSION['cart'][$cart_index])) {
                unset($_SESSION['cart'][$cart_index]);
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Item not found."]);
            }
        } else if (isset($_POST['action']) && $_POST['action'] === 'pagination') {
            require 'settings.php';

            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $offset = ($page - 1) * $limit;

            $table = $_POST['table'];
            $where = isset($_POST['filter']) ? $_POST['filter'] : '';
            //$where = "WHERE status='$filter'";
            
            $sql = "SELECT COUNT(*) as count FROM $table $where";
            $totalQuery = $db->querySingle($sql);
            $totalPages = ceil($totalQuery / $limit);
            
            $query = $db->prepare("SELECT * FROM $table $where LIMIT :limit OFFSET :offset");
            $query->bindValue(':limit', $limit, SQLITE3_INTEGER);
            $query->bindValue(':offset', $offset, SQLITE3_INTEGER);
            $result = $query->execute();
            
            $data = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $data[] = $row;
            }
            
            echo json_encode([
                'data' => $data,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'limit' => $limit,
                'total' => $totalQuery
            ]);
        } else if (isset($_POST['action']) && $_POST['action'] === 'stripe_dispute_pagination') {
            require 'settings.php';
            require 'vendor/autoload.php';

            $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
            $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
            $stripe_secret = $stripe_test_secret;
            $stripe_mode = $settings['stripe_mode'];
            if ($stripe_mode == "live") {
                $stripe_secret = $stripe_live_secret;
            }
    
            $stripe = new \Stripe\StripeClient($stripe_secret);

            return $stripe->disputes->all(
                [
                    'limit' => $settings['items_per_page'],
                    'starting_after' => $_POST['starting_after']
                ]
            );
        } else if (isset($_POST['action']) && $_POST['action'] === 'stripe_coupon_pagination') {
            require 'settings.php';
            require 'vendor/autoload.php';
            
            $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
            $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
            $stripe_secret = $stripe_test_secret;
            $stripe_mode = $settings['stripe_mode'];
            if ($stripe_mode == "live") {
                $stripe_secret = $stripe_live_secret;
            }
    
            $stripe = new \Stripe\StripeClient($stripe_secret);

            $request = [];

            if (isset($_POST['starting_after'])) {
                $request = [
                    'limit' => $settings['items_per_page'],
                    'starting_after' => $_POST['starting_after']
                ];
            }

            if (isset($_POST['ending_before'])) {
                $request = [
                    'limit' => $settings['items_per_page'],
                    'ending_before' => $_POST['ending_before']
                ];
            }
    
            echo json_encode($stripe->coupons->all($request));
        } else if (isset($_POST['action']) && $_POST['action'] === 'stripe_promotion_pagination') {
            require 'settings.php';
            require 'vendor/autoload.php';
            
            $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
            $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
            $stripe_secret = $stripe_test_secret;
            $stripe_mode = $settings['stripe_mode'];
            if ($stripe_mode == "live") {
                $stripe_secret = $stripe_live_secret;
            }
    
            $stripe = new \Stripe\StripeClient($stripe_secret);

            $request = [];

            if (isset($_POST['starting_after'])) {
                $request = [
                    'limit' => $settings['items_per_page'],
                    'starting_after' => $_POST['starting_after']
                ];
            }

            if (isset($_POST['ending_before'])) {
                $request = [
                    'limit' => $settings['items_per_page'],
                    'ending_before' => $_POST['ending_before']
                ];
            }

            echo json_encode($stripe->promotionCodes->all($request));
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action',
                "post" => $_REQUEST
            ]);
        } 
    //} else {
    //    // Block the request
    //    http_response_code(403);
    //    echo json_encode([
    //        "status" => "error", 
    //        "message" => "CORS blocked for origin: $origin",
    //        "origin" => $origin,
    //        "current_host" => $currentHost
    //    ]);
    //}
} else {
    // No Origin header; likely not a cross-origin request
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "No Origin header present"]);
}
?>