<?php

if (!function_exists('getSSHKeysPath')) {
    function getSSHKeysPath() {
  
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
  
if (!function_exists('objectToXml')) {
    function objectToXml($data, &$xml_data) {
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $subnode = $xml_data->addChild(is_numeric($key) ? "item_$key" : $key);
                objectToXml($value, $subnode);
            } else {
                $xml_data->addChild(is_numeric($key) ? "item_$key" : $key, htmlspecialchars($value));
            }
        }
    }    
}

if (!function_exists("addCdata")) {
  function addCdata($name, $value, &$parent) {
    $child = $parent->addChild($name);
  
    if ($child !== NULL) {
      $child_node = dom_import_simplexml($child);
      $child_owner = $child_node->ownerDocument;
      $child_node->appendChild($child_owner->createCDATASection($value));
    }
  
    return $child;
  }  
}

?>