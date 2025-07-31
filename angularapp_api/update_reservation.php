<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("database.php");
require 'vendor/autoload.php'; // PHPMailer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Helper to validate image
function isValidImage($filename) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExt);
}

// Get ID
$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reservation ID']);
    exit();
}

// Get current reservation
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

// Set updated values
$fields = [];
$params = [];

if (!empty($_POST['customerName']) && $_POST['customerName'] !== $existing['customerName']) {
    $fields[] = "customerName = :customerName";
    $params[':customerName'] = $_POST['customerName'];
}

if (!empty($_POST['emailAddress']) && $_POST['emailAddress'] !== $existing['emailAddress']) {
    $fields[] = "emailAddress = :emailAddress";
    $params[':emailAddress'] = $_POST['emailAddress'];
}

if (!empty($_POST['conservationAreaName']) && $_POST['conservationAreaName'] !== $existing['conservationAreaName']) {
    $fields[] = "conservationAreaName = :area";
    $params[':area'] = $_POST['conservationAreaName'];
}

if (!empty($_POST['reservationDate']) && $_POST['reservationDate'] !== $existing['reservationDate']) {
    $fields[] = "reservationDate = :date";
    $params[':date'] = $_POST['reservationDate'];
}

if (!empty($_POST['reservationTime']) && $_POST['reservationTime'] !== $existing['reservationTime']) {
    $fields[] = "reservationTime = :time";
    $params[':time'] = $_POST['reservationTime'];
}

if (isset($_POST['partySize']) && intval($_POST['partySize']) !== intval($existing['partySize'])) {
    $fields[] = "partySize = :partySize";
    $fields[] = "spots_booked = :partySize"; // keep synced
    $params[':partySize'] = intval($_POST['partySize']);
}

// Handle image upload
$imageFileName = $existing['imageFileName'];
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

    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['customerImage']['name']));
    $targetPath = $uploadDir . $safeName;

    if (move_uploaded_file($_FILES['customerImage']['tmp_name'], $targetPath)) {
        // Delete old image if not placeholder
        if (!empty($imageFileName) && $imageFileName !== 'placeholder.png') {
            $oldPath = $uploadDir . basename($imageFileName);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        $imageFileName = $safeName;
        $fields[] = "imageFileName = :imageFileName";
        $params[':imageFileName'] = $safeName;
    }
}

// If no changes
if (empty($fields)) {
    echo json_encode(['message' => 'No changes were made.']);
    exit();
}

// Build query
$params[':id'] = $id;
$query = "UPDATE reservations SET " . implode(', ', $fields) . " WHERE ID = :id";

try {
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    // ✅ Send email if available
    $recipientEmail = !empty($_POST['emailAddress']) ? $_POST['emailAddress'] : ($existing['emailAddress'] ?? null);
    if ($recipientEmail) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'myclass.practice@gmail.com';
            $mail->Password = 'wkddgtuxfmivwheh';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('myclass.practice@gmail.com', 'Reservation System');
            $mail->addAddress($recipientEmail);
            $mail->Subject = 'Reservation Updated Successfully';
            $mail->Body = "Dear {$existing['customerName']},\n\nYour reservation has been successfully updated.\n\nThank you for using our system.\n\n- SpeakMate Reservation System";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email send error: " . $mail->ErrorInfo);
        }
    }

    echo json_encode(['message' => '✅ Reservation updated']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'PDO Error: ' . $e->getMessage()]);
}
