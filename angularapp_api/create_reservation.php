<?php
// ✅ CORS headers for Angular (localhost:4200)
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// ✅ Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ✅ Show PHP errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("database.php");

// ✅ Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method Not Allowed']);
  exit();
}

// ✅ Get and validate inputs
$customerName = trim($_POST['customerName'] ?? '');
$area = trim($_POST['conservationAreaName'] ?? '');
$date = trim($_POST['reservationDate'] ?? '');
$time = trim($_POST['reservationTime'] ?? '');
$partySize = intval($_POST['partySize'] ?? 0);

if ($customerName === '' || $area === '' || $date === '' || $time === '' || $partySize <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid fields']);
  exit();
}

// ✅ Handle image upload
$imageFileName = null;

if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
  $uploadDir = __DIR__ . '/uploads/';
  
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
      http_response_code(500);
      echo json_encode(['error' => 'Failed to create uploads directory']);
      exit();
    }
  }

  $originalName = basename($_FILES['customerImage']['name']);
  $targetPath = $uploadDir . $originalName;

  if (move_uploaded_file($_FILES['customerImage']['tmp_name'], $targetPath)) {
    $imageFileName = $originalName;
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to upload image']);
    exit();
  }
}

try {
  // ✅ Check for duplicate reservation
  $checkQuery = "SELECT * FROM reservations 
                 WHERE customerName = :customerName 
                   AND conservationAreaName = :conservationAreaName 
                   AND reservationDate = :reservationDate 
                   AND reservationTime = :reservationTime";
  $checkStmt = $db->prepare($checkQuery);
  $checkStmt->bindValue(':customerName', $customerName);
  $checkStmt->bindValue(':conservationAreaName', $area);
  $checkStmt->bindValue(':reservationDate', $date);
  $checkStmt->bindValue(':reservationTime', $time);
  $checkStmt->execute();

  if ($checkStmt->fetch()) {
    http_response_code(409); // Conflict
    echo json_encode(['error' => '❌ Duplicate reservation exists for this time']);
    exit();
  }
  $checkStmt->closeCursor();

  // ✅ Insert reservation
  $query = "INSERT INTO reservations 
              (customerName, conservationAreaName, reservationDate, reservationTime, partySize, spots_booked, total_spots, imageFileName) 
            VALUES 
              (:customerName, :conservationAreaName, :reservationDate, :reservationTime, :partySize, :spots_booked, :total_spots, :imageFileName)";
  $statement = $db->prepare($query);
  $statement->bindValue(':customerName', $customerName);
  $statement->bindValue(':conservationAreaName', $area);
  $statement->bindValue(':reservationDate', $date);
  $statement->bindValue(':reservationTime', $time);
  $statement->bindValue(':partySize', $partySize);
  $statement->bindValue(':spots_booked', $partySize);
  $statement->bindValue(':total_spots', 20); // Set default max
  $statement->bindValue(':imageFileName', $imageFileName);
  $statement->execute();
  $statement->closeCursor();

  echo json_encode(['message' => '✅ Reservation created successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
