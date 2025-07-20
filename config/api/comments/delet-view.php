<?php
session_start(); // Wajib jika menggunakan $_SESSION
include_once '../../config/database.php';
include_once '../../object/comment.php';

$database = new Database();
$db = $database->getConnection();
$comment = new Comment($db);

// Simulasi user login (hapus ini jika sudah punya sistem login)
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';

// Ambil data anime untuk dropdown
$animes = [];
$stmt = $db->query("SELECT anime_mal_id, title FROM animes ORDER BY title ASC");
$animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$animeOptions = [];
foreach ($animes as $anime) {
    $animeOptions[$anime['title']] = $anime['anime_mal_id'];
}

$message = "";

// === HANDLE TAMBAH KOMENTAR ===
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $anime_title = $_POST['anime_title'];
    $comment_text = $_POST['comment_text'];

    if (!empty($anime_title) && !empty($comment_text)) {
        $comment->user_id = $_SESSION['user_id'];
        $comment->anime_mal_id = $animeOptions[$anime_title];
        $comment->comment_text = $comment_text;

        if ($comment->add()) {
            $message = "Komentar berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan komentar.";
        }
    } else {
        $message = "Semua field wajib diisi.";
    }
}

// === HANDLE HAPUS KOMENTAR ===
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $user_id]);
}

// === HANDLE EDIT KOMENTAR ===
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $comment_id = $_POST['comment_id'];
    $new_text = $_POST['new_comment_text'];
    $stmt = $db->prepare("UPDATE comments SET comment_text = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$new_text, $comment_id, $_SESSION['user_id']]);
}

// === AMBIL DATA KOMENTAR ===
$query = "SELECT c.id, c.comment_text, c.created_at, a.title, u.username
          FROM comments c
          JOIN animes a ON c.anime_mal_id = a.anime_mal_id
          JOIN users u ON c.user_id = u.id
          ORDER BY c.created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Komentar Anime</title>
</head>
<body>
<h2>Form Komentar</h2>
<?php if ($message): ?>
  <p style="color:green;"><?= $message ?></p>
<?php endif; ?>
<form method="POST">
    <input type="hidden" name="action" value="add">
    <label>Judul Anime:</label>
    <select name="anime_title" required>
        <option value="">-- Pilih Anime --</option>
        <?php foreach ($animeOptions as $title => $id): ?>
            <option value="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></option>
        <?php endforeach; ?>
    </select><br><br>
    <label>Komentar:</label><br>
    <textarea name="comment_text" rows="3" cols="40" required></textarea><br>
    <button type="submit">Kirim Komentar</button>
</form>

<h2>Komentar Terbaru</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>Anime</th>
        <th>User</th>
        <th>Komentar</th>
        <th>Tanggal</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($comments as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td>@<?= htmlspecialchars($row['username']) ?></td>
            <td>
                <?php
                if (isset($_POST['action'], $_POST['comment_id']) &&
                    $_POST['action'] === 'edit' &&
                    $_POST['comment_id'] == $row['id']
                ): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="comment_id" value="<?= $row['id'] ?>">
                        <textarea name="new_comment_text" rows="3" cols="40"><?= htmlspecialchars($row['comment_text']) ?></textarea><br>
                        <button type="submit">Simpan</button>
                    </form>
                <?php else: ?>
                    <?= nl2br(htmlspecialchars($row['comment_text'])) ?>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td>
                <?php if ($_SESSION['username'] === $row['username']): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="comment_id" value="<?= $row['id'] ?>">
                        <button type="submit">Edit</button>
                    </form>

                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="comment_id" value="<?= $row['id'] ?>">
                        <button type="submit">Hapus</button>
                    </form>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
