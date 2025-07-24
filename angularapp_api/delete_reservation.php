<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

$data = json_decode(file_get_contents("php://input"));
$id = $data->id ?? 0;

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid reservation ID']);
  exit();
}

try {
  $query = "DELETE FROM reservations WHERE ID = :id";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  http_response_code(200);
  echo json_encode(['message' => 'Reservation cancelled successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
