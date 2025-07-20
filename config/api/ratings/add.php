<?php
// api/ratings/add_update.php

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

// Include database connection dan core config
include_once '../../config/database.php';
include_once '../../config/core.php'; // Pastikan ini ada

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Dapatkan token dari header Authorization
$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

// Gunakan secret key dari Core.php, bukan string literal
// $key = "YOUR_SUPER_SECRET_KEY"; // HAPUS BARIS INI
$key = Core::$SECRET_KEY; // Gunakan secret key yang benar

if (!empty($jwt)) {
    try {
        // Decode JWT menggunakan secret key dan algoritma dari Core.php
        $decoded = JWT::decode($jwt, new Key($key, Core::$ALGORITHM[0])); // Menggunakan Core::$ALGORITHM
        $user_id = $decoded->data->id;

        // Pastikan data yang dibutuhkan ada
        if (
            !empty($data->anime_mal_id) &&
            isset($data->score) && is_numeric($data->score)
        ) {
            $anime_mal_id = htmlspecialchars(strip_tags($data->anime_mal_id));
            $score = (int) $data->score;

            // Cek apakah rating sudah ada untuk user dan anime ini
            $query_check = "SELECT id FROM ratings WHERE user_id = ? AND anime_mal_id = ? LIMIT 0,1";
            $stmt_check = $db->prepare($query_check);
            $stmt_check->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt_check->bindParam(2, $anime_mal_id, PDO::PARAM_INT);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                // Jika sudah ada, update rating
                $query = "UPDATE ratings SET score = ? WHERE user_id = ? AND anime_mal_id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $score, PDO::PARAM_INT);
                $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
                $stmt->bindParam(3, $anime_mal_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    http_response_code(200); // OK
                    echo json_encode(array("message" => "Rating was updated."));
                } else {
                    http_response_code(503); // Service unavailable
                    echo json_encode(array("message" => "Unable to update rating."));
                }
            } else {
                // Jika belum ada, tambahkan rating baru
                $query = "INSERT INTO ratings (user_id, anime_mal_id, score) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $anime_mal_id, PDO::PARAM_INT);
                $stmt->bindParam(3, $score, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    http_response_code(201); // Created
                    echo json_encode(array("message" => "Rating was added."));
                } else {
                    http_response_code(503); // Service unavailable
                    echo json_encode(array("message" => "Unable to add rating."));
                }
            }
        } else {
            http_response_code(400); // Bad request
            echo json_encode(array("message" => "Unable to add/update rating. Data is incomplete."));
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