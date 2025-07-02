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

// Get JSON input
$data = json_decode(file_get_contents("php://input"));

// Debug log
file_put_contents(
  '/Applications/XAMPP/xamppfiles/logs/debug_add_reservation.log',
  date('Y-m-d H:i:s') . "\n" . print_r($data, true) . "\n\n",
  FILE_APPEND
);

// Safe trim + parse
$customerName = isset($data->customerName) ? trim($data->customerName) : '';
$area = isset($data->conservationAreaName) ? trim($data->conservationAreaName) : '';
$date = isset($data->reservationDate) ? trim($data->reservationDate) : '';
$time = isset($data->reservationTime) ? trim($data->reservationTime) : '';
$partySize = isset($data->partySize) ? intval($data->partySize) : 0;

// Fix TIME
if (preg_match('/am|pm/i', $time)) {
  $time = date("H:i:s", strtotime($time));
} elseif (strlen($time) === 5) {
  $time .= ":00";
}

// Validate
if (
  $customerName === '' ||
  $area === '' ||
  $date === '' ||
  $time === '' ||
  $partySize <= 0
) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Missing or invalid fields',
    'debug' => [
      'customerName' => $customerName,
      'conservationAreaName' => $area,
      'reservationDate' => $date,
      'reservationTime' => $time,
      'partySize' => $partySize
    ]
  ]);
  exit;
}

// Insert
$query = "INSERT INTO reservations 
(customerName, conservationAreaName, reservationDate, reservationTime, partySize, spots_booked, total_spots)
VALUES (:customerName, :conservationAreaName, :reservationDate, :reservationTime, :partySize, :spots_booked, :total_spots)";

$statement = $db->prepare($query);
$statement->bindValue(':customerName', $customerName);
$statement->bindValue(':conservationAreaName', $area);
$statement->bindValue(':reservationDate', $date);
$statement->bindValue(':reservationTime', $time);
$statement->bindValue(':partySize', $partySize);
$statement->bindValue(':spots_booked', $partySize);
$statement->bindValue(':total_spots', 30);

try {
  $statement->execute();
  $statement->closeCursor();
  echo json_encode(['message' => '✅ Reservation added successfully!']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'DB insert failed',
    'details' => $e->getMessage(),
    'params' => [
      'customerName' => $customerName,
      'conservationAreaName' => $area,
      'reservationDate' => $date,
      'reservationTime' => $time,
      'partySize' => $partySize
    ]
  ]);
  exit;
}
?>
