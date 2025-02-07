<?php
$error = "";
// Get the HOME directory
$homePath = getenv('HOME');

if ($homePath === false) {
    if (isset($_SERVER['HOME']) && $_SERVER['HOME'] !== "") {
        $homePath = $_SERVER['HOME'];
    } else {
        if (!file_exists("data")) mkdir("data",0777);
        $homePath = "./data";
    }
}

// SQLite database file path
$dbFile = $homePath . '/simplecommerce.db';

if (file_exists($dbFile)) {
    header('Location: index.php');
    exit();
}

try {
    // Create the SQLite database file
    $db = new SQLite3($dbFile);

    $password = password_hash("adminpass", PASSWORD_BCRYPT);

    // Create necessary tables
    $db->exec("
        BEGIN TRANSACTION;
        CREATE TABLE IF NOT EXISTS categories (
            id	INTEGER,
            name	TEXT NOT NULL,
            image	TEXT,
            provider	TEXT,
            PRIMARY KEY(id AUTOINCREMENT)
        );
        CREATE TABLE IF NOT EXISTS orders (
            id	INTEGER,
            customer_id	INTEGER,
            product_id	INTEGER,
            quantity	INTEGER,
            total	REAL,
            payment_method	TEXT,
            status	TEXT,
            PRIMARY KEY(id AUTOINCREMENT),
            FOREIGN KEY(customer_id) REFERENCES users(id),
            FOREIGN KEY(product_id) REFERENCES products(id)
        );
        CREATE TABLE IF NOT EXISTS products (
            id	INTEGER,
            name	TEXT,
            description	TEXT,
            image	BLOB,
            cost	REAL,
            price	REAL,
            category_id	INTEGER,
            homepage	INTEGER DEFAULT 0,
            tag	TEXT,
            available_date	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            stripe_id	TEXT,
            tax_behavior	TEXT DEFAULT 'unspecified',
            PRIMARY KEY(id AUTOINCREMENT)
        );
        CREATE TABLE IF NOT EXISTS settings (
            id	INTEGER,
            name	TEXT,
            value	TEXT,
            PRIMARY KEY(id AUTOINCREMENT)
        );
        CREATE TABLE IF NOT EXISTS tracker (
            id	INTEGER,
            session_id	TEXT,
            status	TEXT DEFAULT 'Order Received',
            notes TEXT,
            oag TEXT DEFAULT 'uploads/oag.pdf',
            PRIMARY KEY(id AUTOINCREMENT)
        );
        CREATE TABLE users (
            id	INTEGER,
            name	TEXT,
            email	TEXT UNIQUE,
            password	TEXT,
            is_approved	INTEGER DEFAULT 0,
            address	TEXT,
            city	TEXT,
            zip	TEXT,
            file	TEXT DEFAULT 'index.php',
            reset_token TEXT DEFAULT '',
            token_expires TIMESTAMP,
            PRIMARY KEY(id AUTOINCREMENT)
        );
        INSERT INTO settings (name,value) VALUES ('DAYS_FOR_NEW','30');
        INSERT INTO settings (name,value) VALUES ('stripe_mode','test');
        INSERT INTO settings (name,value) VALUES ('stripe_live_secret','');
        INSERT INTO settings (name,value) VALUES ('stripe_test_secret','');
        INSERT INTO settings (name,value) VALUES ('success_url','');
        INSERT INTO settings (name,value) VALUES ('cancel_url','');
        INSERT INTO settings (name,value) VALUES ('fa-map-marker','');
        INSERT INTO settings (name,value) VALUES ('fa-phone','');
        INSERT INTO settings (name,value) VALUES ('fa-envelope','');
        INSERT INTO settings (name,value) VALUES ('fa-facebook-official','');
        INSERT INTO settings (name,value) VALUES ('fa-instagram','');
        INSERT INTO settings (name,value) VALUES ('fa-snapchat','');
        INSERT INTO settings (name,value) VALUES ('fa-pinterest-p','');
        INSERT INTO settings (name,value) VALUES ('fa-twitter','');
        INSERT INTO settings (name,value) VALUES ('fa-linkedin','');
        INSERT INTO settings (name,value) VALUES ('contact-email','');
        INSERT INTO settings (name,value) VALUES ('jobsearch_url','');
        INSERT INTO settings (name,value) VALUES ('support_url','');
        INSERT INTO settings (name,value) VALUES ('sitename','SimpleCommerce');
        INSERT INTO settings (name,value) VALUES ('siteslogan','A simplified eCommerce approach!');
        INSERT INTO settings (name,value) VALUES ('cancel_page_content','');
        INSERT INTO settings (name,value) VALUES ('faqs_page_content','');
        INSERT INTO settings (name,value) VALUES ('giftcard_page_content','');
        INSERT INTO settings (name,value) VALUES ('payment_page_content','');
        INSERT INTO settings (name,value) VALUES ('return_page_content','');
        INSERT INTO settings (name,value) VALUES ('shipment_page_content','');
        INSERT INTO settings (name,value) VALUES ('stores_page_content','');
        INSERT INTO settings (name,value) VALUES ('about_page_content','');
        INSERT INTO settings (name,value) VALUES ('newsletter_page_content','');
        INSERT INTO settings (name,value) VALUES ('giftcard_coupon_test','');
        INSERT INTO settings (name,value) VALUES ('giftcard_coupon_live','');
        INSERT INTO settings (name,value) VALUES ('giftcard_page_content','');
        INSERT INTO settings (name,value) VALUES ('giftcard_email_subject','');
        INSERT INTO settings (name,value) VALUES ('noreply_email','');
        INSERT INTO settings (name,value) VALUES ('siteurl','');
        INSERT INTO settings (name,value) VALUES ('unsubscribe_url','');
        INSERT INTO settings (name,value) VALUES ('items_per_page','8');

        INSERT INTO users (name,email,password,is_approved,address,city,zip,file) VALUES ('Administrator','admin','$password',1,NULL,NULL,NULL,'admin.php');
        COMMIT;
    ");
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Setup error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Setup System Settings</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    </head>
    <body>
        <div class="w3-container">
            <p class="w3-large"><?php isset($error) ? print($error) : print("Database successfully created!");?></p>
            <a href="login.php" class="w3-button w3-block w3-purple">Login to Update Settings</a>
            <br/>
            <a href="index.php" class="w3-button w3-block w3-green">Home</a>
        </div>
    </body>
</html>