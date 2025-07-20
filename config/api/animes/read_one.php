<?php
// api/animes/read_one.php

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

$query = "SELECT id, anime_mal_id, title, genre, description, release_year, cover_image_url
          FROM animes
          WHERE anime_mal_id = ?
          LIMIT 0,1";

$stmt = $db->prepare( $query );
$stmt->bindParam(1, $anime_mal_id);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($row);

    $anime_item = array(
        "id" => $id,
        "anime_mal_id" => $anime_mal_id,
        "title" => $title,
        "genre" => $genre,
        "description" => html_entity_decode($description),
        "release_year" => $release_year,
        "cover_image_url" => $cover_image_url
    );

    http_response_code(200);
    echo json_encode($anime_item);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Anime not found."));
}
?>