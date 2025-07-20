<?php
// api/ratings/get_user_rating.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include_once '../../config/database.php';
include_once '../../config/core.php';

$database = new Database();
$db = $database->getConnection();

$headers = getallheaders();
$jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

$anime_mal_id = isset($_GET['anime_mal_id']) ? $_GET['anime_mal_id'] : die(json_encode(array("message" => "Missing anime_mal_id parameter.")));

if (!empty($jwt)) {
    try {
        $decoded = JWT::decode($jwt, new Key(Core::$SECRET_KEY, Core::$ALGORITHM[0]));
        $user_id = $decoded->data->id;

        $query = "SELECT score FROM ratings WHERE user_id = ? AND anime_mal_id = ? LIMIT 0,1";
        $stmt = $db->prepare( $query );
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $anime_mal_id);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode(array("score" => (float)$row['score']));
        } else {
            http_response_code(200); // OK, but user has not rated this anime
            echo json_encode(array("score" => null)); // Mengembalikan null jika user belum rating
        }

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
    }
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. Token not provided."));
}
?>