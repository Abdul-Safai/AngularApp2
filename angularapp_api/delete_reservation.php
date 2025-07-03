<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? intval($data->id) : 0;

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid reservation ID']);
  exit();
}

$query = "DELETE FROM reservations WHERE ID = :id";
$statement = $db->prepare($query);
$statement->bindValue(':id', $id);
$statement->execute();
$statement->closeCursor();
echo json_encode(['message' => '✅ Reservation cancelled']);
?>
