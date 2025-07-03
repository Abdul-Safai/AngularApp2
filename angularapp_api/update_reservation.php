<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"));

$id = isset($data->ID) ? intval($data->ID) : 0;
$customerName = trim($data->customerName ?? '');
$area = trim($data->conservationAreaName ?? '');
$date = trim($data->reservationDate ?? '');
$time = trim($data->reservationTime ?? '');
$partySize = isset($data->partySize) ? intval($data->partySize) : 0;

// ✅ Basic validation
if ($id <= 0 || $customerName === '' || $area === '' || $date === '' || $time === '' || $partySize <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid fields']);
  exit();
}

try {
  $query = "UPDATE reservations 
            SET customerName = :customerName,
                conservationAreaName = :conservationAreaName,
                reservationDate = :reservationDate,
                reservationTime = :reservationTime,
                partySize = :partySize,
                spots_booked = :partySize
            WHERE ID = :id";

  $statement = $db->prepare($query);
  $statement->bindValue(':customerName', $customerName);
  $statement->bindValue(':conservationAreaName', $area);
  $statement->bindValue(':reservationDate', $date);
  $statement->bindValue(':reservationTime', $time);
  $statement->bindValue(':partySize', $partySize);
  $statement->bindValue(':id', $id);
  $statement->execute();
  $statement->closeCursor();

  echo json_encode(['message' => '✅ Reservation updated successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>
