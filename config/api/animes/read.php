<?php
// api/animes/read.php

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

$query = "SELECT id, anime_mal_id, title, genre, description, release_year, cover_image_url FROM animes ORDER BY title ASC";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

$animes_arr = array();
$animes_arr["records"] = array();

if($num > 0){
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
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
        array_push($animes_arr["records"], $anime_item);
    }
    http_response_code(200);
    echo json_encode($animes_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No animes found."));
}
?>