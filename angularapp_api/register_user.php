<?php
// 👇 ALLOW CROSS-ORIGIN REQUESTS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// 👇 Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once("database.php");

// 👇 Read JSON input from Angular
$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->username) || !isset($data->email) || !isset($data->password)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid input."]);
  exit();
}

// 👇 Sanitize and prepare values
$username = $data->username;
$email = $data->email;
$password = password_hash($data->password, PASSWORD_DEFAULT);

// 👇 Check for existing email
$checkQuery = "SELECT * FROM users WHERE email = :email";
$checkStmt = $db->prepare($checkQuery);
$checkStmt->bindValue(":email", $email);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
  http_response_code(409); // Conflict
  echo json_encode(["error" => "Email already registered."]);
  exit();
}

// 👇 Insert new user
$query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
$stmt = $db->prepare($query);
$stmt->bindValue(":username", $username);
$stmt->bindValue(":email", $email);
$stmt->bindValue(":password", $password);

if ($stmt->execute()) {
  http_response_code(200);
  echo json_encode(["message" => "User registered successfully."]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Something went wrong."]);
}
