<?php
// api/comments/delete.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS"); // Menerima DELETE dan OPTIONS
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
include_once '../../objects/Comment.php'; // Kelas Comment

$database = new Database();
$db = $database->getConnection();

// Validasi token dan dapatkan user data
$user_data = validate_token(); // Ini akan exit() jika token tidak valid
$user_id = $user_data['id']; // ID pengguna dari token

// Buat objek Comment
$comment = new Comment($db);

// Ambil data yang dikirim melalui DELETE request (JSON)
$data = json_decode(file_get_contents("php://input"));

// Pastikan comment_id tersedia
if (!empty($data->comment_id)) {
    // Set ID komentar yang akan dihapus
    $comment->id = $data->comment_id;
    // Set ID pengguna dari token untuk otorisasi (hanya pemilik yang bisa menghapus)
    $comment->user_id = $user_id;

    // Coba hapus komentar
    if ($comment->delete()) {
        http_response_code(200); // OK
        echo json_encode(array("message" => "Komentar berhasil dihapus."));
    } else {
        // Jika delete gagal, mungkin karena comment_id tidak ditemukan atau user_id tidak cocok
        http_response_code(403); // Forbidden, karena user bukan pemilik atau komentar tidak ada
        echo json_encode(array("message" => "Gagal menghapus komentar. Pastikan Anda adalah pemilik komentar."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Tidak dapat menghapus komentar. ID komentar tidak ditemukan."));
}
?>