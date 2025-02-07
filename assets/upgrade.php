<?php
// Get the HOME directory
$homePath = getenv('HOME');   // Or $_SERVER['HOME']
// SQLite database file in HOME directory
$dbFile = $homePath . '/simplecommerce.db';
$db = new SQLite3($dbFile, SQLITE3_OPEN_READWRITE);
$new_password = "MyNewSecretPassword";
$stmt = $db->prepare('UPDATE users SET password=:new_password WHERE id=1');
$stmt→bindValue(':id',$id);
$stmt->bindValue(':new_password',password_hash($new_password, PASSWORD_BCRYPT));
$stmt->execute();
unlink("upgrade.php");
header('Location: admin.php');
?>