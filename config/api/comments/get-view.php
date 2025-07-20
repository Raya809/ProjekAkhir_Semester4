<?php
// Frontend PHP untuk menampilkan komentar berdasarkan anime
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$anime_mal_id = isset($_GET['anime_mal_id']) ? htmlspecialchars(strip_tags($_GET['anime_mal_id'])) : null;
$comments = [];
$message = "";

if (!empty($anime_mal_id)) {
    $query = "SELECT c.comment_text, c.created_at, u.username
              FROM comments c
              JOIN users u ON c.user_id = u.id
              WHERE c.anime_mal_id = ?
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $anime_mal_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $message = "Belum ada komentar untuk anime ini.";
    }
} else {
    $message = "Anime MAL ID tidak ditemukan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Komentar Anime</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 30px;
    }

    .container {
      max-width: 700px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h2 {
      margin-bottom: 20px;
      text-align: center;
    }

    .comment {
      border-bottom: 1px solid #ddd;
      padding: 10px 0;
    }

    .comment:last-child {
      border-bottom: none;
    }

    .username {
      font-weight: bold;
      color: #007bff;
    }

    .created-at {
      font-size: 12px;
      color: #777;
    }

    .comment-text {
      margin-top: 5px;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Komentar untuk Anime MAL ID: <?= htmlspecialchars($anime_mal_id) ?></h2>

  <?php if (!empty($comments)): ?>
    <?php foreach ($comments as $comment): ?>
      <div class="comment">
        <div class="username">@<?= htmlspecialchars($comment['username']) ?></div>
        <div class="created-at"><?= htmlspecialchars($comment['created_at']) ?></div>
        <div class="comment-text"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p><?= $message ?></p>
  <?php endif; ?>

  <br><a href="../anime-frontend.php">← Kembali ke Daftar Anime</a>
</div>

</body>
</html>
