<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("database.php");

function isValidImage($filename) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExt);
}

// Validate ID
$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reservation ID']);
    exit();
}

// Fetch existing reservation
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

// Collect and sanitize fields
$customerName = trim($_POST['customerName'] ?? $existing['customerName']);
$conservationAreaName = trim($_POST['conservationAreaName'] ?? $existing['conservationAreaName']);
$reservationDate = trim($_POST['reservationDate'] ?? $existing['reservationDate']);
$reservationTime = trim($_POST['reservationTime'] ?? $existing['reservationTime']);
$partySize = isset($_POST['partySize']) ? intval($_POST['partySize']) : intval($existing['partySize']);

if (
    empty($customerName) || !preg_match('/^[A-Za-z\s]+$/', $customerName) ||
    empty($conservationAreaName) || $partySize <= 0 || $partySize > 30
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input. Check name or party size.']);
    exit();
}

// Handle image upload
$imageFileName = $existing['imageFileName'] ?? 'placeholder.png';

if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
    if (!isValidImage($_FILES['customerImage']['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported image format.']);
        exit();
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['customerImage']['name']));
    $targetPath = $uploadDir . $safeName;

    if (move_uploaded_file($_FILES['customerImage']['tmp_name'], $targetPath)) {
        if (!empty($existing['imageFileName']) && $existing['imageFileName'] !== 'placeholder.png') {
            $oldPath = $uploadDir . basename($existing['imageFileName']);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        $imageFileName = $safeName;
    }
}

// Update DB
try {
    $stmt = $db->prepare("UPDATE reservations SET 
        customerName = :customerName,
        conservationAreaName = :area,
        reservationDate = :date,
        reservationTime = :time,
        partySize = :partySize,
        spots_booked = :partySize,
        imageFileName = :imageFileName
        WHERE ID = :id");

    $stmt->bindValue(':customerName', $customerName);
    $stmt->bindValue(':area', $conservationAreaName);
    $stmt->bindValue(':date', $reservationDate);
    $stmt->bindValue(':time', $reservationTime);
    $stmt->bindValue(':partySize', $partySize);
    $stmt->bindValue(':imageFileName', $imageFileName);
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    echo json_encode(['message' => '✅ Reservation updated']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
