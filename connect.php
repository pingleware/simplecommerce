<?php
// Get the HOME directory
$homePath = getenv('HOME'); // Or $_SERVER['HOME']

// SEE OFFICIAL USER GUIDE REFERENCE "unable to open database file"
if ($homePath === false) {
    if (isset($_SERVER['HOME']) && $_SERVER['HOME'] !== "") {
        $homePath = $_SERVER['HOME'];
    } else {
        if (!file_exists("data")) mkdir("data",0777);
        $homePath = "./data";
    }
}

// SQLite database file in HOME directory
$dbFile = $homePath . '/simplecommerce.db';

// Check if the database file exists
if (!file_exists($dbFile)) {
    // Redirect to setup script
    header('Location: setup.php');
    exit();
}

if (file_exists('upgrade.php')) {
    // Upgrade action detected. Redirect to upgrade script
    // database connection has not been established.
    header('Location: upgrade.php');
    exit();
}

$db = new SQLite3($dbFile, SQLITE3_OPEN_READWRITE);
?>
