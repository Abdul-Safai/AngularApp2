<?php
// ✅ CORS & Content-Type headers
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("database.php");

// ✅ Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// ✅ Validate method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// ✅ Extract ID
$id = $_POST['ID'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing reservation ID']);
    exit();
}

// ✅ Get current record
$query = "SELECT * FROM reservations WHERE ID = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$current = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$current) {
    http_response_code(404);
    echo json_encode(['error' => 'Reservation not found']);
    exit();
}

// ✅ Prepare update fields
$fields = [];
$params = [];

if (!empty($_POST['customerName']) && $_POST['customerName'] !== $current['customerName']) {
    $fields[] = "customerName = :customerName";
    $params[':customerName'] = $_POST['customerName'];
    $current['customerName'] = $_POST['customerName'];
}

if (!empty($_POST['emailAddress']) && $_POST['emailAddress'] !== $current['emailAddress']) {
    $fields[] = "emailAddress = :emailAddress";
    $params[':emailAddress'] = $_POST['emailAddress'];
    $current['emailAddress'] = $_POST['emailAddress'];
}

if (!empty($_POST['conservationAreaName']) && $_POST['conservationAreaName'] !== $current['conservationAreaName']) {
    $fields[] = "conservationAreaName = :conservationAreaName";
    $params[':conservationAreaName'] = $_POST['conservationAreaName'];
    $current['conservationAreaName'] = $_POST['conservationAreaName'];
}

if (!empty($_POST['reservationDate']) && $_POST['reservationDate'] !== $current['reservationDate']) {
    $fields[] = "reservationDate = :reservationDate";
    $params[':reservationDate'] = $_POST['reservationDate'];
    $current['reservationDate'] = $_POST['reservationDate'];
}

if (!empty($_POST['reservationTime']) && $_POST['reservationTime'] !== $current['reservationTime']) {
    $fields[] = "reservationTime = :reservationTime";
    $params[':reservationTime'] = $_POST['reservationTime'];
    $current['reservationTime'] = $_POST['reservationTime'];
}

if (!empty($_POST['partySize']) && $_POST['partySize'] != $current['partySize']) {
    $fields[] = "partySize = :partySize";
    $fields[] = "spots_booked = :spots_booked";
    $params[':partySize'] = $_POST['partySize'];
    $params[':spots_booked'] = $_POST['partySize'];
    $current['partySize'] = $_POST['partySize'];
}

// ✅ Handle image upload
if (isset($_FILES['customerImage']) && $_FILES['customerImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $originalName = basename($_FILES['customerImage']['name']);
    $targetPath = $uploadDir . $originalName;

    if (move_uploaded_file($_FILES['customerImage']['tmp_name'], $targetPath)) {
        if ($current['imageFileName'] && $current['imageFileName'] !== 'placeholder.png') {
            $oldPath = $uploadDir . $current['imageFileName'];
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $fields[] = "imageFileName = :imageFileName";
        $params[':imageFileName'] = $originalName;
        $current['imageFileName'] = $originalName;
    }
}

// ✅ Run update
if (!empty($fields)) {
    $sql = "UPDATE reservations SET " . implode(", ", $fields) . " WHERE ID = :id";
    $stmt = $db->prepare($sql);
    $params[':id'] = $id;
    $stmt->execute($params);
    $stmt->closeCursor();
}

// ✅ Send email confirmation
if (!empty($current['emailAddress'])) {
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
        $mail->addAddress($current['emailAddress']);
        $mail->isHTML(true);
        $mail->Subject = '✅ Your Reservation Has Been Updated';
        $mail->Body = "
            <h3>Updated Reservation Details</h3>
            <p><strong>Name:</strong> {$current['customerName']}</p>
            <p><strong>Conservation Area:</strong> {$current['conservationAreaName']}</p>
            <p><strong>Date:</strong> {$current['reservationDate']}</p>
            <p><strong>Time:</strong> {$current['reservationTime']}</p>
            <p><strong>Party Size:</strong> {$current['partySize']}</p>
            <p>Thank you for using SpeakMate!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("❌ Email failed: " . $mail->ErrorInfo);
    }
}

echo json_encode(['message' => '✅ Reservation updated and email sent']);
?>
