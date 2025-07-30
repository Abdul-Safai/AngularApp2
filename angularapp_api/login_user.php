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

$stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit();
}

$failedAttempts = (int)$user['failed_attempts'];
$lastFailed = $user['last_failed_login'];
$now = new DateTime();

// 🔒 Check lockout status
if ($failedAttempts >= 3 && $lastFailed) {
    $lastFailedTime = new DateTime($lastFailed);
    $interval = $lastFailedTime->diff($now);
    $elapsedSeconds = ($interval->i * 60) + $interval->s;

    if ($elapsedSeconds < 300) { // 5 minutes = 300 seconds
        $remaining = 300 - $elapsedSeconds;
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "Account locked after multiple failed attempts.",
            "remainingSeconds" => $remaining
        ]);
        exit();
    } else {
        // ✅ Lock expired: reset attempts
        $failedAttempts = 0;
    }
}

// ✅ Password check
if (password_verify($password, $user['password'])) {
    // Reset login attempts
    $stmt = $db->prepare("UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "id" => $user['id'],
            "username" => $user['username'],
            "email" => $user['email']
        ]
    ]);
    exit();
} else {
    // ❌ Incorrect password: increase attempts
    $failedAttempts++;
    $stmt = $db->prepare("UPDATE users SET failed_attempts = :fa, last_failed_login = :ts WHERE email = :email");
    $stmt->bindValue(':fa', $failedAttempts);
    $stmt->bindValue(':ts', $now->format('Y-m-d H:i:s'));
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    // Send email alert on 3rd failed attempt
    if ($failedAttempts === 3) {
        @mail($email, "Security Alert - Reservation System Login",
            "There have been 3 failed login attempts.\n\nIf this wasn't you, please reset your password.");
    }

    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid email or password"]);
    exit();
}
