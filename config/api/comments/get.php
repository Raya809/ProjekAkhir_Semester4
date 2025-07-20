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

// Include database connection
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get anime_mal_id from query parameters
$anime_mal_id = isset($_GET['anime_mal_id']) ? htmlspecialchars(strip_tags($_GET['anime_mal_id'])) : null;

if (!empty($anime_mal_id)) {
    // Query to get comments for a specific anime_mal_id, ordered by creation date
    $query = "SELECT c.comment_text, c.created_at, u.username
              FROM comments c
              JOIN users u ON c.user_id = u.id
              WHERE c.anime_mal_id = ?
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $anime_mal_id);
    $stmt->execute();

    $comments_arr = array();
    $comments_arr["comments"] = array();

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row); // Extracts variables like $comment_text, $created_at, $username
            $comment_item = array(
                "username" => $username,
                "comment_text" => $comment_text,
                "created_at" => $created_at
            );
            array_push($comments_arr["comments"], $comment_item);
        }
        http_response_code(200);
        echo json_encode($comments_arr);
    } else {
        http_response_code(200); // OK, but no comments found
        echo json_encode(array("message" => "No comments found for this anime."));
    }
} else {
    http_response_code(400); // Bad request
    echo json_encode(array("message" => "Anime MAL ID is required."));
}
?>