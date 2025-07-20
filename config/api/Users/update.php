<?php
// Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include files
include_once '../../config/database.php';
include_once '../../objects/User.php';
include_once '../../config/core.php';
include_once '../../libs/php-jwt-main/src/BeforeValidException.php';
include_once '../../libs/php-jwt-main/src/ExpiredException.php';
include_once '../../libs/php-jwt-main/src/SignatureInvalidException.php';
include_once '../../libs/php-jwt-main/src/JWT.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// DB & user setup
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get input data
$data = json_decode(file_get_contents("php://input"));
if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "Data JSON tidak valid."]);
    exit();
}

// Get JWT from Authorization header
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$jwt) {
    http_response_code(401);
    echo json_encode(["message" => "Akses ditolak. Token tidak ditemukan."]);
    exit();
}

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user->id = $decoded->data->id;

    // Validasi input
    if (empty($data->username) || empty($data->email)) {
        http_response_code(400);
        echo json_encode(["message" => "Username dan email harus diisi."]);
        exit();
    }

    $user->username = $data->username;
    $user->email = $data->email;

    // Hash password jika diisi
    if (!empty($data->password)) {
        $user->password = password_hash($data->password, PASSWORD_DEFAULT);
    } else {
        $user->password = null;
    }

    // Proses update
    if ($user->update()) {
        http_response_code(200);

        // Buat JWT baru
        $token = [
            "iss" => $iss,
            "aud" => $aud,
            "iat" => $iat,
            "nbf" => $nbf,
            "data" => [
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email
            ]
        ];
        $new_jwt = JWT::encode($token, $key, 'HS256');

        echo json_encode([
            "message" => "Pengguna berhasil diperbarui.",
            "jwt" => $new_jwt
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Gagal memperbarui pengguna. Mungkin email sudah terdaftar."]);
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        "message" => "Akses ditolak.",
        "error" => $e->getMessage()
    ]);
}
?>
