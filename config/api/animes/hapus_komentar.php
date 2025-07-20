<?php
session_start();

include_once '../../config/database.php';
include_once '../../object/comment.php';

$database = new Database();
$db = $database->getConnection();

$comment = new Comment($db);

if (!isset($_SESSION['id'])) {
    die("<p>Anda harus login untuk menghapus komentar.</p><a href='komentar.php'>Kembali</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $comment->id = intval($_POST['comment_id']);

    if ($comment->getById() && $comment->user_id == $_SESSION['id']) {
        // Set ulang user_id untuk validasi di fungsi delete
        $comment->user_id = $_SESSION['id'];

        if ($comment->delete()) {
            header("Location: komentar.php");
            exit;
        } else {
            echo "<p style='color:red;'>Gagal menghapus komentar.</p>";
        }
    } else {
        echo "<p style='color:red;'>Komentar tidak ditemukan atau Anda tidak berhak menghapusnya.</p>";
    }
} else {
    echo "<p style='color:red;'>Permintaan tidak valid.</p>";
}
?>
