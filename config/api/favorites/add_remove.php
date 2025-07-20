<?php
// animelist-api/api/favorites/add_remove.php

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

require_once '../../config/database.php';
require_once '../../includes/validate_token.php'; // Pastikan ini sudah benar

// Validasi token
$user_data = validate_token();
$user_id = $user_data['id']; // ID pengguna dari token

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Pastikan data yang diperlukan tersedia
if (!isset($data->anime_mal_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Anime MAL ID tidak disediakan."));
    exit();
}

$anime_mal_id = $data->anime_mal_id;

// Periksa apakah anime sudah ada di favorit pengguna
$query = "SELECT id FROM favorites WHERE user_id = ? AND anime_mal_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
$stmt->bindParam(2, $anime_mal_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
$stmt->execute();

// Mengganti $stmt->store_result() dan $stmt->num_rows
if ($stmt->rowCount() > 0) { // Ini yang benar untuk PDO
    // Anime sudah ada di favorit, jadi hapus (toggle off)
    $query = "DELETE FROM favorites WHERE user_id = ? AND anime_mal_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
    $stmt->bindParam(2, $anime_mal_id, PDO::PARAM_INT); // Menggunakan bindParam PDO

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Anime berhasil dihapus dari favorit.", "favorited" => false));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Gagal menghapus anime dari favorit."));
    }
} else {
    // Anime belum ada di favorit, jadi tambahkan (toggle on)
    $query = "INSERT INTO favorites (user_id, anime_mal_id) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
    $stmt->bindParam(2, $anime_mal_id, PDO::PARAM_INT); // Menggunakan bindParam PDO

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Anime berhasil ditambahkan ke favorit.", "favorited" => true));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Gagal menambahkan anime ke favorit."));
    }
}
// Tidak perlu $stmt->close() atau $db->close() di PDO, koneksi akan ditutup otomatis di akhir skrip
?>