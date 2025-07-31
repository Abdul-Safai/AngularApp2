<?php
// ✅ CORS headers
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("database.php");

// ✅ Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// ✅ Validate POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// ✅ Collect inputs
$customerName = trim($_POST['customerName'] ?? '');
$emailAddress = trim($_POST['customerEmail'] ?? '');
$area = trim($_POST['conservationAreaName'] ?? '');
$date = trim($_POST['reservationDate'] ?? '');
$time = trim($_POST['reservationTime'] ?? '');
$partySize = intval($_POST['partySize'] ?? 0);

// ✅ Validate inputs
if ($customerName === '' || $area === '' || $date === '' || $time === '' || $partySize <= 0 || $emailAddress === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid fields']);
    exit();
}

// ✅ Handle image upload
$imageFileName = null;
if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
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
    // ✅ Check for duplicates
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
        http_response_code(409);
        echo json_encode(['error' => '❌ Duplicate reservation exists for this time']);
        exit();
    }
    $checkStmt->closeCursor();

    // ✅ Insert reservation
    $insert = "INSERT INTO reservations 
        (customerName, emailAddress, conservationAreaName, reservationDate, reservationTime, partySize, spots_booked, total_spots, imageFileName) 
        VALUES 
        (:customerName, :emailAddress, :conservationAreaName, :reservationDate, :reservationTime, :partySize, :spots_booked, :total_spots, :imageFileName)";

    $stmt = $db->prepare($insert);
    $stmt->bindValue(':customerName', $customerName);
    $stmt->bindValue(':emailAddress', $emailAddress);
    $stmt->bindValue(':conservationAreaName', $area);
    $stmt->bindValue(':reservationDate', $date);
    $stmt->bindValue(':reservationTime', $time);
    $stmt->bindValue(':partySize', $partySize);
    $stmt->bindValue(':spots_booked', $partySize);
    $stmt->bindValue(':total_spots', 20); // Default spots
    $stmt->bindValue(':imageFileName', $imageFileName);
    $stmt->execute();
    $stmt->closeCursor();

    // ✅ Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'myclass.practice@gmail.com';         // 🔁 Your Gmail
        $mail->Password = 'wkddgtuxfmivwheh';           // 🔁 Your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('myclass.practice@gmail.com', ' Reservation System');
        $mail->addAddress($emailAddress);

        $mail->isHTML(true);
        $mail->Subject = 'Your Reservation is Confirmed!';
        $mail->Body = "
            <h3>Reservation Details</h3>
            <p><strong>Name:</strong> $customerName</p>
            <p><strong>Conservation Area:</strong> $area</p>
            <p><strong>Date:</strong> $date</p>
            <p><strong>Time:</strong> $time</p>
            <p><strong>Party Size:</strong> $partySize</p>
            <p>Thank you for using SpeakMate!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("❌ Email failed: " . $mail->ErrorInfo);
    }

    echo json_encode(['message' => '✅ Reservation created and email sent']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
