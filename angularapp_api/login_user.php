<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->email) || empty($data->password)) {
  http_response_code(400);
  echo json_encode(["success" => false, "error" => "Missing email or password"]);
  exit();
}

$email = $data->email;
$password = $data->password;

// Fetch user
$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  http_response_code(401);
  echo json_encode(["success" => false, "error" => "Invalid email or password"]);
  exit();
}

// Check lockout status
$failedAttempts = (int)$user['failed_attempts'];
$lastFailed = $user['last_failed_login'];
$now = new DateTime();

if ($failedAttempts >= 3 && $lastFailed) {
  $lastFailedTime = new DateTime($lastFailed);
  $interval = $lastFailedTime->diff($now);
  if ($interval->i < 5) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Too many failed attempts. Please wait 5 minute(s) before trying again."]);
    exit();
  }
}

// Check password
if (password_verify($password, $user['password'])) {
  // ✅ Reset failed attempts on success
  $resetQuery = "UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE id = :id";
  $resetStmt = $db->prepare($resetQuery);
  $resetStmt->bindValue(':id', $user['id']);
  $resetStmt->execute();

  echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user" => [
      "id" => $user['id'],
      "username" => $user['username'],
      "email" => $user['email']
    ]
  ]);
} else {
  // ❌ Increment failed attempts
  $updateQuery = "UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_login = NOW() WHERE id = :id";
  $updateStmt = $db->prepare($updateQuery);
  $updateStmt->bindValue(':id', $user['id']);
  $updateStmt->execute();

  http_response_code(401);
  echo json_encode(["success" => false, "error" => "Invalid email or password"]);
}
