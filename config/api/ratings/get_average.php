<?php
// api/ratings/get_average.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$anime_mal_id = isset($_GET['anime_mal_id']) ? $_GET['anime_mal_id'] : die(json_encode(array("message" => "Missing anime_mal_id parameter.")));

$query = "SELECT AVG(score) as average_score FROM ratings WHERE anime_mal_id = ?";
$stmt = $db->prepare( $query );
$stmt->bindParam(1, $anime_mal_id);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['average_score'] !== null) {
    http_response_code(200);
    echo json_encode(array("average_score" => round((float)$row['average_score'], 2)));
} else {
    http_response_code(200); // OK, but no average score (e.g., no ratings yet)
    echo json_encode(array("average_score" => 0)); // Mengembalikan 0 jika belum ada rating
}
?>