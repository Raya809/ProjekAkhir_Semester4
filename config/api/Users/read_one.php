<?php
// Required headers
header("Access-Control-Allow-Origin: http://localhost:3000"); // Sesuaikan dengan domain frontend Anda
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Files needed for database connection and JWT handling
include_once '../../config/database.php';
include_once '../../objects/User.php';
include_once '../../config/core.php'; // For JWT key and algorithms
include_once '../../libs/php-jwt-main/src/BeforeValidException.php';
include_once '../../libs/php-jwt-main/src/ExpiredException.php';
include_once '../../libs/php-jwt-main/src/SignatureInvalidException.php';
include_once '../../libs/php-jwt-main/src/JWT.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate user object
$user = new User($db);

// Get JWT from header
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$jwt) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak. Token tidak ditemukan."));
    exit();
}

try {
    // Decode JWT to get user ID
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user->id = $decoded->data->id;

    // Read user details
    if ($user->readOneById()) {
        http_response_code(200);
        echo json_encode(array(
            "id" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "created_at" => $user->created_at,
            "message" => "Detail pengguna berhasil diambil."
        ));
    } else {
        http_response_code(404); // Not found
        echo json_encode(array("message" => "Pengguna tidak ditemukan."));
    }

} catch (Exception $e) {
    http_response_code(401); // Unauthorized
    echo json_encode(array(
        "message" => "Akses ditolak.",
        "error" => $e->getMessage()
    ));
}
?>