<?php
session_start();

if (!isset($_SESSION['id'])) {
    // Redirect ke halaman login jika belum login
    header("Location: ../auth/login-views.php");
    exit;
}

include_once '../../config/database.php';
include_once '../../object/comment.php';

$database = new Database();
$db = $database->getConnection();
$comment = new Comment($db);

// Ambil semua anime
$query = "SELECT id, anime_mal_id, title, genre, description, release_year, cover_image_url FROM animes ORDER BY title ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$animes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat opsi select
$animeOptions = [];
foreach ($animes as $anime) {
    $animeOptions[$anime['title']] = $anime['anime_mal_id'];
}

// Proses tambah komentar
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['anime_title']) && isset($_POST['comment_text'])) {
    $title = $_POST['anime_title'];
    $text = $_POST['comment_text'];

    if (!isset($animeOptions[$title])) {
        $message = "<span style='color:red;'>Anime tidak ditemukan.</span>";
    } elseif (!empty($text)) {
        $comment->user_id = $_SESSION['id'];
        $comment->anime_mal_id = $animeOptions[$title];
        $comment->comment_text = $text;
        if ($comment->add()) {
            $message = "<span style='color:green;'>Komentar berhasil ditambahkan.</span>";
        } else {
            $message = "<span style='color:red;'>Gagal menambahkan komentar.</span>";
        }
    } else {
        $message = "<span style='color:red;'>Komentar tidak boleh kosong.</span>";
    }
}

// Ambil komentar terbaru (dengan gambar anime)
$query = "SELECT c.id, c.user_id, a.title, a.cover_image_url, c.comment_text, c.created_at, u.username
          FROM comments c
          JOIN users u ON c.user_id = u.id
          JOIN animes a ON c.anime_mal_id = a.anime_mal_id
          ORDER BY c.created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Komentar Anime</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f8f8; padding: 20px; }
    h2 { text-align: center; }
    .anime-list {
      max-width: 1000px;
      margin: auto;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    .anime-card {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      text-align: center;
      padding-bottom: 10px;
    }
    .anime-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .anime-card .info {
      padding: 10px;
    }
    .form-container, .comments {
      max-width: 800px;
      margin: 30px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 15px;
    }
    button {
      padding: 10px 20px;
      background: #007BFF;
      border: none;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      vertical-align: top;
    }
    th {
      background-color: #f0f0f0;
    }
    .action-btn {
      display: inline-block;
      margin-right: 5px;
    }
    .btn-edit { background: #ffc107; color: black; }
    .btn-delete { background: #dc3545; color: white; }
    .message { margin-top: 10px; text-align: center; }
    .anime-cover {
      width: 60px;
      height: auto;
      margin-right: 8px;
      border-radius: 5px;
      vertical-align: middle;
    }
  </style>
</head>
<body>

<a href="../index.php">← Kembali</a>

<h2>Daftar Anime</h2>
<div class="anime-list">
  <?php foreach ($animes as $anime): ?>
    <div class="anime-card">
      <img src="<?= htmlspecialchars($anime['cover_image_url']) ?>" alt="<?= htmlspecialchars($anime['title']) ?>">
      <div class="info">
        <strong><?= htmlspecialchars($anime['title']) ?></strong><br>
        <small><?= htmlspecialchars($anime['genre']) ?> | <?= $anime['release_year'] ?></small>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="form-container">
  <h2>Tambah Komentar</h2>
  <form method="POST" action="">
    <label>Judul Anime:</label>
    <select name="anime_title" required>
      <option value="">-- Pilih Anime --</option>
      <?php foreach ($animeOptions as $title => $id): ?>
        <option value="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Komentar:</label>
    <textarea name="comment_text" rows="4" required></textarea>

    <button type="submit">Kirim Komentar</button>
    <div class="message"><?= $message ?></div>
  </form>
</div>

<div class="comments">
  <h2>Komentar Terbaru</h2>
  <?php if (count($recent_comments) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Judul</th>
          <th>Pengguna</th>
          <th>Komentar</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_comments as $row): ?>
          <tr>
            <td>
              <img class="anime-cover" src="<?= htmlspecialchars($row['cover_image_url']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
              <?= htmlspecialchars($row['title']) ?>
            </td>
            <td>@<?= htmlspecialchars($row['username']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['comment_text'])) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td>
              <?php if ($_SESSION['id'] == $row['user_id']): ?>
                <form class="action-btn" action="edit_komentar.php" method="POST">
                  <input type="hidden" name="comment_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn-edit">Edit</button>
                </form>

                <form class="action-btn" action="hapus_komentar.php" method="POST" onsubmit="return confirm('Hapus komentar ini?')">
                  <input type="hidden" name="comment_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn-delete">Hapus</button>
                </form>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Belum ada komentar.</p>
  <?php endif; ?>
</div>

</body>
</html>
