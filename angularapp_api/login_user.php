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

$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
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
  http_response_code(401);
  echo json_encode(["success" => false, "error" => "Invalid email or password"]);
}
