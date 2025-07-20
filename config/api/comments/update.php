<?php
// Required headers
header("Access-Control-Allow-Origin: http://localhost:3000"); // Sesuaikan dengan domain frontend Anda
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS"); // Pastikan PUT diizinkan
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Files needed to connect to database
include_once '../../config/database.php';
include_once '../../objects/Comment.php';
include_once '../../config/core.php';
include_once '../../libs/php-jwt-main/src/BeforeValidException.php';
include_once '../../libs/php-jwt-main/src/ExpiredException.php';
include_once '../../libs/php-jwt-main/src/SignatureInvalidException.php';
include_once '../../libs/php-jwt-main/src/JWT.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate comment object
$comment = new Comment($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Get JWT from header
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$jwt) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak. Token tidak ditemukan."));
    exit();
}

try {
    // Decode JWT and get user ID
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user_id_from_token = $decoded->data->id;

    // Make sure data is not empty
    if (!empty($data->comment_id) && !empty($data->comment_text)) {
        // Set comment properties
        $comment->id = $data->comment_id;
        $comment->comment_text = $data->comment_text;

        // Fetch comment details to verify ownership
        $comment->readOne();

        // Check if comment exists and if the token's user_id matches the comment's user_id
        if ($comment->user_id == $user_id_from_token) {
            // Update the comment
            if ($comment->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Komentar berhasil diperbarui."));
            } else {
                http_response_code(503); // Service unavailable
                echo json_encode(array("message" => "Gagal memperbarui komentar."));
            }
        } else {
            http_response_code(403); // Forbidden
            echo json_encode(array("message" => "Anda tidak memiliki izin untuk memperbarui komentar ini."));
        }
    } else {
        http_response_code(400); // Bad request
        echo json_encode(array("message" => "Tidak dapat memperbarui komentar. Data tidak lengkap."));
    }

} catch (Exception $e) {
    http_response_code(401); // Unauthorized
    echo json_encode(array(
        "message" => "Akses ditolak.",
        "error" => $e->getMessage()
    ));
}
?>