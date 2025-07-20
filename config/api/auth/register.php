<?php
// api/auth/register.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid JSON input.']);
    exit();
}

$username = $input['username'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['message' => 'Unable to register. All fields are required.']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$query = "SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 0,1";
$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['username'] === $username) {
        http_response_code(409);
        echo json_encode(['message' => 'Username already taken.']);
        exit();
    }
    if ($row['email'] === $email) {
        http_response_code(409);
        echo json_encode(['message' => 'Email already registered.']);
        exit();
    }
}

// Pastikan nama kolom 'password' sesuai di DB Anda
$query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password_hash)";
$stmt = $db->prepare($query);

$stmt->bindParam(':username', $username);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password_hash', $password_hash);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['message' => 'Registration successful! Please login.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Unable to register user. Please try again.']);
    error_log("Database error (register): " . implode(":", $stmt->errorInfo()));
}
?>