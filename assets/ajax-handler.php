<?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      echo "Hello, " . htmlspecialchars($_POST['name']);
  }
?>