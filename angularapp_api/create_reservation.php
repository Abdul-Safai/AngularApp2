<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

// ✅ Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

// ✅ Only handle POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method Not Allowed']);
  exit();
}

// ✅ Get inputs
$customerName = trim($_POST['customerName'] ?? '');
$area = trim($_POST['conservationAreaName'] ?? '');
$date = trim($_POST['reservationDate'] ?? '');
$time = trim($_POST['reservationTime'] ?? '');
$partySize = intval($_POST['partySize'] ?? 0);

// ✅ Basic validation
if ($customerName === '' || $area === '' || $date === '' || $time === '' || $partySize <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid fields']);
  exit();
}

// ✅ Handle image upload
$imageFileName = null;

if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
  $uploadDir = __DIR__ . '/uploads/';
  
  // ✅ Ensure folder exists
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

// ✅ Insert into database
try {
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
  $statement->bindValue(':spots_booked', $partySize); // Initially same
  $statement->bindValue(':total_spots', 20); // ✅ Default
  $statement->bindValue(':imageFileName', $imageFileName);
  $statement->execute();
  $statement->closeCursor();

  echo json_encode(['message' => '✅ Reservation created successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
