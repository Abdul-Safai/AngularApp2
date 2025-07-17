<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("database.php");

$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reservation ID']);
    exit();
}

// ✅ Step 1: Fetch existing reservation
$stmt = $db->prepare("SELECT * FROM reservations WHERE ID = :id");
$stmt->bindValue(':id', $id);
$stmt->execute();
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$existing) {
    http_response_code(404);
    echo json_encode(['error' => 'Reservation not found']);
    exit();
}

// ✅ Step 2: Use existing values if not provided
$customerName = isset($_POST['customerName']) ? trim($_POST['customerName']) : $existing['customerName'];
$area = isset($_POST['conservationAreaName']) ? trim($_POST['conservationAreaName']) : $existing['conservationAreaName'];
$date = isset($_POST['reservationDate']) ? trim($_POST['reservationDate']) : $existing['reservationDate'];
$time = isset($_POST['reservationTime']) ? trim($_POST['reservationTime']) : $existing['reservationTime'];
$partySize = isset($_POST['partySize']) ? intval($_POST['partySize']) : intval($existing['partySize']);

// ✅ Step 3: Handle image upload
$imageFileName = $existing['imageFileName'] ?: 'placeholder.png';

if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['customerImage']['tmp_name'];
    $baseName = basename($_FILES['customerImage']['name']);
    $baseName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $baseName);
    $targetPath = $uploadDir . $baseName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        // ✅ Delete old image unless it's blank or placeholder
        if (!empty($existing['imageFileName']) && $existing['imageFileName'] !== 'placeholder.png') {
            $oldPath = $uploadDir . basename($existing['imageFileName']);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        $imageFileName = $baseName;
    }
}

// ✅ Step 4: Update database
try {
    $query = "UPDATE reservations 
              SET customerName = :customerName,
                  conservationAreaName = :conservationAreaName,
                  reservationDate = :reservationDate,
                  reservationTime = :reservationTime,
                  partySize = :partySize,
                  spots_booked = :partySize,
                  imageFileName = :imageFileName
              WHERE ID = :id";

    $statement = $db->prepare($query);
    $statement->bindValue(':customerName', $customerName);
    $statement->bindValue(':conservationAreaName', $area);
    $statement->bindValue(':reservationDate', $date);
    $statement->bindValue(':reservationTime', $time);
    $statement->bindValue(':partySize', $partySize);
    $statement->bindValue(':imageFileName', $imageFileName);
    $statement->bindValue(':id', $id);

    $statement->execute();
    $statement->closeCursor();

    echo json_encode(['message' => '✅ Reservation updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
