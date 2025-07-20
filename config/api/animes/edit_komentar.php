<?php
// File: edit_komentar.php
session_start();

include_once '../../config/database.php';
include_once '../../object/comment.php';

$database = new Database();
$db = $database->getConnection();

$comment = new Comment($db);

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id'])) {
    die("<p>Silakan login terlebih dahulu untuk mengedit komentar.</p><a href='komentar.php'>Kembali</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $comment_id = intval($_POST['comment_id']);
    $comment->id = $comment_id;

    // Periksa apakah komentar milik user saat ini
    if ($comment->getById() && $comment->user_id == $_SESSION['id']) {

        // Jika form edit sudah disubmit
        if (isset($_POST['new_comment_text'])) {
            $comment->comment_text = $_POST['new_comment_text'];
            $comment->user_id = $_SESSION['id']; // Pastikan user_id diset ulang

            if ($comment->update()) {
                header("Location: komentar.php");
                exit;
            } else {
                echo "<p style='color:red;'>Terjadi kesalahan. Komentar gagal diperbarui.</p>";
            }

        } else {
            // Form edit pertama kali ditampilkan
            ?>
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Edit Komentar</title>
            </head>
            <body>
                <h3>Edit Komentar Anda</h3>
<form method="POST" action="edit_komentar.php">
    <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
    <textarea name="new_comment_text" rows="4" style="width:100%;" required><?= htmlspecialchars($comment->comment_text) ?></textarea><br><br>
    <button type="submit">Simpan Perubahan</button>
    <a href="komentar.php">Batal</a>
</form>
            </body>
            </html>
            <?php
            exit;
        }

    } else {
        echo "<p style='color:red;'>Komentar tidak ditemukan atau Anda tidak memiliki izin untuk mengedit komentar ini.</p>";
    }

} else {
    echo "<p style='color:red;'>Permintaan tidak valid. Pastikan Anda memilih komentar yang ingin diedit.</p>";
}
?>
