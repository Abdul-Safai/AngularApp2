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

// ✅ Step 1: Get image file name from DB
$stmt = $db->prepare("SELECT imageFileName FROM reservations WHERE ID = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$imageFile = $stmt->fetchColumn();
$stmt->closeCursor();

// ✅ Step 2: Delete the reservation
$query = "DELETE FROM reservations WHERE ID = :id";
$statement = $db->prepare($query);
$statement->bindValue(':id', $id, PDO::PARAM_INT);
$success = $statement->execute();
$statement->closeCursor();

// ✅ Step 3: Delete image file if not blank
if ($success) {
  if (!empty($imageFile)) {
    $imagePath = __DIR__ . '/uploads/' . basename($imageFile);
    if (is_file($imagePath)) {
      unlink($imagePath);
    }
  }

  echo json_encode(['message' => '✅ Reservation and image deleted']);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to delete reservation']);
}
?>
