<?php
if (file_exists('maintenance.php')) {
    require('maintenance.php');
    exit;
}

// SEE OFFICIAL USER GUIDE REFERENCE "session_start"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connect.php';

$settings = [];

try {
    // Fetch settings from the database
    $results = $db->query('SELECT * FROM settings');
    if (!$results) {
        throw new Exception("Error fetching settings: " . $db->lastErrorMsg());
    }

    while ($setting = $results->fetchArray(SQLITE3_ASSOC)) {
        $settings[$setting['name']] = $setting['value'];
    }
} catch (Exception $e) {
    error_log("An error occurred: " . $e->getMessage());
}

?>