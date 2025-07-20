<?php
// api/auth/login.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include_once '../../config/database.php';
include_once '../../config/core.php'; // Sertakan file core.php

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// error_log("Login Request - Raw JSON: " . $inputJSON); // Aktifkan ini untuk debugging
// error_log("Login Request - Decoded Data: " . print_r($input, true)); // Aktifkan ini untuk debugging

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid JSON input.']);
    exit();
}

$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['message' => 'Unable to login. Data is incomplete.']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, username, email, password FROM users WHERE username = :username LIMIT 0,1"; // Asumsikan kolom password menyimpan hash
$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->execute();

$foundUser = null;
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Verifikasi password yang dimasukkan dengan hash password di database
    if (password_verify($password, $row['password'])) {
        $foundUser = $row;
        unset($foundUser['password']); // Hapus password dari objek sebelum dikirim
    }
}

if ($foundUser) {
    http_response_code(200);

    // Buat JWT Payload
    $token_payload = array(
        "iss" => Core::$ISSUER,
        "aud" => Core::$AUDIENCE,
        "iat" => time(),
        "exp" => time() + Core::$JWT_EXPIRE_SECONDS,
        "data" => array(
            "id" => $foundUser['id'],
            "username" => $foundUser['username'],
            "email" => $foundUser['email']
        )
    );

    $jwt = JWT::encode($token_payload, Core::$SECRET_KEY, Core::$ALGORITHM[0]);

    echo json_encode([
        'message' => 'Login successful!',
        'user' => [
            'id' => $foundUser['id'],
            'username' => $foundUser['username'],
            'email' => $foundUser['email']
        ],
        'token' => $jwt
    ]);
} else {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid username or password.']);
}
?>