<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("database.php");

// Input validation
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidImage($filename) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExt);
}

$customerName = trim($_POST['customerName'] ?? '');
$conservationAreaName = trim($_POST['conservationAreaName'] ?? '');
$reservationDate = trim($_POST['reservationDate'] ?? '');
$reservationTime = trim($_POST['reservationTime'] ?? '');
$partySize = intval($_POST['partySize'] ?? 0);
$totalSpots = intval($_POST['total_spots'] ?? 30);

// Server-side validations
if (
    empty($customerName) || !preg_match('/^[A-Za-z\s]+$/', $customerName) ||
    empty($conservationAreaName) ||
    empty($reservationDate) ||
    empty($reservationTime) ||
    $partySize <= 0 || $partySize > $totalSpots
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid form input']);
    exit();
}

// Validate uploaded image
$imageFileName = 'placeholder.png';
if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
    if (!isValidImage($_FILES['customerImage']['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported image format. Use JPG, JPEG, PNG, or WEBP.']);
        exit();
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['customerImage']['name']));
    $targetPath = $uploadDir . $safeName;

    if (!move_uploaded_file($_FILES['customerImage']['tmp_name'], $targetPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload image.']);
        exit();
    }

    $imageFileName = $safeName;
}

// Insert into database
try {
    $stmt = $db->prepare("INSERT INTO reservations 
        (customerName, conservationAreaName, reservationDate, reservationTime, partySize, spots_booked, total_spots, imageFileName)
        VALUES (:name, :area, :date, :time, :size, :booked, :total, :image)");

    $stmt->bindValue(':name', $customerName);
    $stmt->bindValue(':area', $conservationAreaName);
    $stmt->bindValue(':date', $reservationDate);
    $stmt->bindValue(':time', $reservationTime);
    $stmt->bindValue(':size', $partySize);
    $stmt->bindValue(':booked', $partySize);
    $stmt->bindValue(':total', $totalSpots);
    $stmt->bindValue(':image', $imageFileName);

    $stmt->execute();
    echo json_encode(['message' => '✅ Reservation created successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
