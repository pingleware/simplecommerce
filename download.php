<?php
require 'settings.php';

if (!isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

if (file_exists($dbFile)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($dbFile) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($dbFile));
    readfile($dbFile);
    exit;
} else {
    echo "File not found!";
}
?>
