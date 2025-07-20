<?php
// api/comments/add.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Menerima POST dan OPTIONS
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Required files
include_once '../../config/database.php';
require_once '../../includes/validate_token.php'; // Middleware untuk validasi token
include_once '../../object/comment.php'; // Kelas Comment

$database = new Database();
$db = $database->getConnection();

// Validasi token dan dapatkan user data
$user_data = validate_token(); // Ini akan exit() jika token tidak valid
$user_id = $user_data['id']; // ID pengguna dari token

// Buat objek Comment
$comment = new Comment($db);

// Ambil data yang dikirim melalui POST request (JSON)
$data = json_decode(file_get_contents("php://input"));

// Pastikan data yang diperlukan tersedia
if (
    !empty($data->anime_mal_id) &&
    !empty($data->comment_text)
) {
    // Set properti objek Comment
    $comment->user_id = $user_id;
    $comment->anime_mal_id = $data->anime_mal_id;
    $comment->comment_text = $data->comment_text;

    // Coba tambahkan komentar
    if ($comment->add()) {
        http_response_code(201); // Created
        echo json_encode(array("message" => "Komentar berhasil ditambahkan."));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("message" => "Gagal menambahkan komentar."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Tidak dapat menambahkan komentar. Data tidak lengkap."));
}
?>