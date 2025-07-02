<?php
// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Connect to DB
require_once("database.php");

// Run the query
$query = "SELECT * FROM reservations";
$statement = $db->prepare($query);
$statement->execute();
$reservations = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Output JSON
header('Content-Type: application/json');
echo json_encode($reservations);
?>
