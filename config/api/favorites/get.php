<?php
// animelist-api/api/favorites/get.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS"); // Menerima GET dan OPTIONS
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

// Periksa apakah ada anime_mal_id di query string (untuk mengecek status favorit satu anime)
if (isset($_GET['anime_mal_id'])) {
    $anime_mal_id = $_GET['anime_mal_id'];
    $query = "SELECT id FROM favorites WHERE user_id = ? AND anime_mal_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
    $stmt->bindParam(2, $anime_mal_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
    $stmt->execute();
    
    // Mengganti $stmt->store_result() dan $stmt->num_rows
    $favorited = $stmt->rowCount() > 0; // Ini yang benar untuk PDO
    
    http_response_code(200);
    echo json_encode(array("favorited" => $favorited));
    // Tidak perlu $stmt->close() di PDO
} else {
    // Jika tidak ada anime_mal_id, kembalikan semua favorit pengguna
    $query = "SELECT anime_mal_id FROM favorites WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT); // Menggunakan bindParam PDO
    $stmt->execute();
    
    // Mengganti $stmt->get_result() dan fetch_assoc()
    $favorites = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Ini yang benar untuk PDO
        $favorites[] = $row['anime_mal_id'];
    }

    http_response_code(200);
    echo json_encode(array("favorites" => $favorites));
    // Tidak perlu $stmt->close() di PDO
}
// Tidak perlu $db->close() di PDO, koneksi akan ditutup otomatis di akhir skrip
?>