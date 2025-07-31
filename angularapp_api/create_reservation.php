<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("database.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$customerName = trim($_POST['customerName'] ?? '');
$emailAddress = trim($_POST['emailAddress'] ?? '');
$area = trim($_POST['conservationAreaName'] ?? '');
$date = trim($_POST['reservationDate'] ?? '');
$time = trim($_POST['reservationTime'] ?? '');
$partySize = intval($_POST['partySize'] ?? 0);

if ($customerName === '' || $area === '' || $date === '' || $time === '' || $partySize <= 0 || $emailAddress === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid fields']);
    exit();
}

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
    $checkQuery = "SELECT * FROM reservations 
        WHERE customerName = :customerName 
        AND emailAddress = :emailAddress
        AND conservationAreaName = :conservationAreaName 
        AND reservationDate = :reservationDate 
        AND reservationTime = :reservationTime";

    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindValue(':customerName', $customerName);
    $checkStmt->bindValue(':emailAddress', $emailAddress);
    $checkStmt->bindValue(':conservationAreaName', $area);
    $checkStmt->bindValue(':reservationDate', $date);
    $checkStmt->bindValue(':reservationTime', $time);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => '❌ Duplicate reservation exists for this customer at the same time']);
        exit();
    }
    $checkStmt->closeCursor();

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
    $stmt->bindValue(':total_spots', 20);
    $stmt->bindValue(':imageFileName', $imageFileName);
    $stmt->execute();
    $stmt->closeCursor();

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
