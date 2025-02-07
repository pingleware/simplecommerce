<?php
if (!function_exists('addItemToStripe')) {
    function addItemToStripe($currency,$price,$name,$tax_behavior="unspecified") {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }

        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);

          $response = json_decode(
            $stripe->prices->create(
              [
                'currency' => $currency,
                'unit_amount' => $price,
                'product_data' => ['name' => $name],
                'tax_behavior' => $tax_behavior
              ]
            )
          ); 
          return $response->id;
        } else {
          return 0; 
        }
    }    
}

if (!function_exists('getPrices')) {
    function getPrices() {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }


        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);

          $response = $stripe->prices->all(['limit' => 100]);
          return json_decode($response, true);
        } else {
          return [];
        }
    }    
}

if (!function_exists('getProduct')) {
    function getProduct($product) {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }

        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);

          $response = $stripe->products->retrieve($product, []);
          return json_decode($response, true);
        } else {
          return [];
        }
    }    
}

if (!function_exists('getProducts')) {
    function getProducts() {
      return getStripeProducts();
    }    
}

if (!function_exists('getStripeProducts')) {
    function getStripeProducts($limit=100) {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }

        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);
          $response = $stripe->products->all(['limit' => $limit]);
          return json_decode($response,true); 
        } else {
          return [];
        }
    }    
}

if (!function_exists('getPrice')) { 
    function getPrice($price) {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }
    
        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);
          $response = $stripe->prices->retrieve($price, []);
        } else {
          return [];
        }
    }    
}

if (!function_exists('getDisputes')) {
    function getDisputes() {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }

        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);

          return $stripe->disputes->all(['limit' => $settings['items_per_page']]);  
        } else {
          return []; 
        }

    }    
}

if (!function_exists('getPriceForProduct')) {
    function getPriceForProduct($product="") {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }

        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);

          if ($product !== "") {
            $response = $stripe->prices->all(
              [
                'limit' => 100,
                'product' => $product
              ]
            );
          } else {
            $response = $stripe->prices->all(['limit' => 100]);
          }
          return $response;
        } else {
          return [];
        }
    }    
}

if (!function_exists('getCoupons')) {
    function getCoupons() {
        include "settings.php";
        require "vendor/autoload.php";
    
        $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
        $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
        $stripe_secret = $stripe_test_secret;
        $stripe_mode = $settings['stripe_mode'];
        if ($stripe_mode == "live") {
            $stripe_secret = $stripe_live_secret;
        }

        if ($stripe_secret !== "") {
          $stripe = new \Stripe\StripeClient($stripe_secret);

          $coupons = $stripe->coupons->all(['limit' => $settings['items_per_page']]);
          // SEE OFFICIAL USER GUIDE REFERENCE: STRIPE AUTO PAGINATION
          if (!isset($coupons->count)) {
            $_coupons = $stripe->coupons->all();
            $coupons->count = count($_coupons->data);
          }
          return $coupons;  
        } else {
          return [];
        }
    }    
}

if (!function_exists('getPromotions')) {
  function getPromotions($starting_after="") {
    include "settings.php";
    require "vendor/autoload.php";
    
    $stripe_test_secret = trim(decrypt(base64_decode($settings['stripe_test_secret'])));
    $stripe_live_secret = trim(decrypt(base64_decode($settings['stripe_live_secret'])));
    $stripe_secret = $stripe_test_secret;
    $stripe_mode = $settings['stripe_mode'];
    if ($stripe_mode == "live") {
        $stripe_secret = $stripe_live_secret;
    }

    if ($stripe_secret !== "") {
      $stripe = new \Stripe\StripeClient($stripe_secret);

      $promotions = $stripe->promotionCodes->all(['limit' => $settings['items_per_page']]);
      // SEE OFFICIAL USER GUIDE REFERENCE: STRIPE AUTO PAGINATION
      if (!isset($promotions->count)) {
        $_promotions = $stripe->promotionCodes->all(); 
        $promotions->count = count($_promotions->data);
      }
      return $promotions;  
    } else {
      return [];
    }
  }
}

if (!function_exists('createSSHKeys')) {
  function createSSHKeys() {
    // Determine the home directory
    if (isset($_SERVER['HOME'])) {
      $sshDir = $_SERVER['HOME'] . '/.ssh'; // Use $_SERVER['HOME'] if it's set
    } else {
      // Fallback for when $_SERVER['HOME'] is not defined
      $homeDir = posix_getpwuid(posix_geteuid())['dir'];
      $sshDir = $homeDir . '/.ssh'; // Use posix_getpwuid() if $_SERVER['HOME'] is not set
    }

    // Define paths for the SSH keys
    $privateKeyPath = $sshDir . '/shopecommerce';
    $publicKeyPath = $sshDir . '/shopecommerce.pub';

    // Check if the SSH keys already exist
    if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
      // Ensure the .ssh directory exists
      if (!is_dir($privateKeyPath)) {
          mkdir($privateKeyPath, 0700, true);
      }

      // Generate SSH keys if they do not exist
      $command = "ssh-keygen -t rsa -b 4096 -f $privateKeyPath -N ''";
      exec($command, $output, $returnVar);
      
      if ($returnVar !== 0) {
          die("Error generating SSH keys. Must create manually using mkdir $sshDir && ssh-keygen -t rsa -b 4096 -f $privateKeyPath -N ''");
      }
      //echo "SSH keys generated at $privateKeyPath and $publicKeyPath\n";
    }
  }
}

if (!function_exists('getSSHKeysPath')) {
  function getSSHKeysPath() {
    createSSHKeys();

    // Determine the home directory
    if (isset($_SERVER['HOME'])) {
      $sshDir = $_SERVER['HOME'] . '/.ssh'; // Use $_SERVER['HOME'] if it's set
    } else {
      // Fallback for when $_SERVER['HOME'] is not defined
      $homeDir = posix_getpwuid(posix_geteuid())['dir'];
      $sshDir = $homeDir . '/.ssh'; // Use posix_getpwuid() if $_SERVER['HOME'] is not set
    }

    // Define paths for the SSH keys
    $privateKeyPath = $sshDir . '/shopecommerce';
    $publicKeyPath = $sshDir . '/shopecommerce.pub';
    
    return [$privateKeyPath,$publicKeyPath];
  }
}

if (!function_exists('getSSHKey')) {
  function getSSHKey($keyPath) {
    if (file_exists($keyPath)) {
      return file_get_contents($keyPath);
    }

    return "";
  }
}

// --- Encrypt --- //
if (!function_exists('encrypt')) {
  function encrypt($plaintext, $cipher = "AES-128-CBC") {
    $keyPath = getSSHKeysPath();
    $secret_key = getSSHKey($keyPath[0]);
  
    $key = openssl_digest($secret_key, 'SHA256', TRUE);

    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    // binary cipher
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    // or replace OPENSSL_RAW_DATA & $iv with 0 & bin2hex($iv) for hex cipher (eg. for transmission over internet)

    // or increase security with hashed cipher; (hex or base64 printable eg. for transmission over internet)
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    return base64_encode($iv . $hmac . $ciphertext_raw);
  }    
}


// --- Decrypt --- //
if (!function_exists('decrypt')) {
  function decrypt($ciphertext, $cipher = "AES-128-CBC") {
    $keyPath = getSSHKeysPath();
    $secret_key = getSSHKey($keyPath[0]);

    $c = base64_decode($ciphertext);

    $key = openssl_digest($secret_key, 'SHA256', TRUE);

    $ivlen = openssl_cipher_iv_length($cipher);

    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    if (hash_equals($hmac, $calcmac))
        return $original_plaintext . "\n";
  }    
}

if (!function_exists('isBase64')) {
  function isBase64($string) {
    // Check if the string is a valid Base64 encoded string
    $decoded = base64_decode($string, true);
    if ($decoded === false) {
        return false;
    }
    // Check if the encoded version of the decoded string matches the original string
    return base64_encode($decoded) === $string;
  }  
}
?>