<?php
$dsn = 'mysql:host=localhost;dbname=reservation_system';  // ✅ Use your real DB name!
$username = 'root';  // ✅ Default for XAMPP
$password = '';      // ✅ Default for XAMPP

try {
  $db = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
  exit();
}
?>
