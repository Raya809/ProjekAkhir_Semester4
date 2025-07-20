<?php
// CORS Headers - Pastikan ini ada di paling atas setiap file API
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Required files for JWT (untuk verifikasi token)
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Include database connection
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Dapatkan token dari header Authorization
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

// Set your secret key (harus sama dengan di login.php)
$key = "YOUR_SUPER_SECRET_KEY";

if (!empty($jwt)) {
    try {
        // Decode JWT
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $user_id = $decoded->data->id;

        // Get anime_mal_id from query parameters
        $anime_mal_id = isset($_GET['anime_mal_id']) ? htmlspecialchars(strip_tags($_GET['anime_mal_id'])) : null;

        if (!empty($anime_mal_id)) {
            $query = "SELECT score FROM ratings WHERE user_id = ? AND anime_mal_id = ? LIMIT 0,1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $anime_mal_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode(array("score" => $row['score']));
            } else {
                http_response_code(200); // OK, but no rating found
                echo json_encode(array("score" => null, "message" => "No rating found for this anime by this user."));
            }
        } else {
            http_response_code(400); // Bad request
            echo json_encode(array("message" => "Anime MAL ID is required."));
        }

    } catch (Exception $e) {
        http_response_code(401); // Unauthorized
        echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(array("message" => "Access denied. Token not provided."));
}
?>