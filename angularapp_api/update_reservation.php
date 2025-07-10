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

// ✅ Use POST + multipart/form-data
$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
$customerName = trim($_POST['customerName'] ?? '');
$area = trim($_POST['conservationAreaName'] ?? '');
$date = trim($_POST['reservationDate'] ?? '');
$time = trim($_POST['reservationTime'] ?? '');
$partySize = isset($_POST['partySize']) ? intval($_POST['partySize']) : 0;

// ✅ Handle uploaded file if provided
$imageFileName = null;

if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
  $uploadDir = __DIR__ . '/uploads/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }

  $tmpName = $_FILES['customerImage']['tmp_name'];
  $baseName = basename($_FILES['customerImage']['name']);
  $targetPath = $uploadDir . $baseName;

  if (move_uploaded_file($tmpName, $targetPath)) {
    $imageFileName = $baseName;
  }
}

// ✅ Basic validation
if ($id <= 0 || $customerName === '' || $area === '' || $date === '' || $time === '' || $partySize <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid fields']);
  exit();
}

try {
  // ✅ Add imageFileName if uploaded
  $query = "UPDATE reservations 
            SET customerName = :customerName,
                conservationAreaName = :conservationAreaName,
                reservationDate = :reservationDate,
                reservationTime = :reservationTime,
                partySize = :partySize,
                spots_booked = :partySize";

  if ($imageFileName) {
    $query .= ", imageFileName = :imageFileName";
  }

  $query .= " WHERE ID = :id";

  $statement = $db->prepare($query);
  $statement->bindValue(':customerName', $customerName);
  $statement->bindValue(':conservationAreaName', $area);
  $statement->bindValue(':reservationDate', $date);
  $statement->bindValue(':reservationTime', $time);
  $statement->bindValue(':partySize', $partySize);
  $statement->bindValue(':id', $id);

  if ($imageFileName) {
    $statement->bindValue(':imageFileName', $imageFileName);
  }

  $statement->execute();
  $statement->closeCursor();

  echo json_encode(['message' => '✅ Reservation updated successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>
