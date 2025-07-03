<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

$data = json_decode(file_get_contents("php://input"));

// ✅ Safe trim
$customerName = trim($data->customerName ?? '');
$area = trim($data->conservationAreaName ?? '');
$date = trim($data->reservationDate ?? '');
$time = trim($data->reservationTime ?? '');
$partySize = isset($data->partySize) ? intval($data->partySize) : 0;

if (
  $customerName === '' ||
  $area === '' ||
  $date === '' ||
  $time === '' ||
  $partySize <= 0
) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit();
}

$query = "INSERT INTO reservations 
(customerName, conservationAreaName, reservationDate, reservationTime, partySize, spots_booked, total_spots) 
VALUES (:customerName, :conservationAreaName, :reservationDate, :reservationTime, :partySize, :spots_booked, :total_spots)";

$statement = $db->prepare($query);
$statement->bindValue(':customerName', $customerName);
$statement->bindValue(':conservationAreaName', $area);
$statement->bindValue(':reservationDate', $date);
$statement->bindValue(':reservationTime', $time);
$statement->bindValue(':partySize', $partySize);
$statement->bindValue(':spots_booked', $partySize); // ✅ spots_booked = partySize per row!
$statement->bindValue(':total_spots', 30);

try {
  $statement->execute();
  $statement->closeCursor();
  echo json_encode(['message' => 'Reservation added successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>
