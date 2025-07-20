<?php
session_start();
?>

<h1>Web AnimeList</h1>

<?php if (isset($_SESSION['username'])): ?>
  <p>Selamat datang, <strong><?= $_SESSION['username'] ?></strong>!</p>
  <a href="/auth/logout.php">Logout</a> <br>
<?php else: ?>
  <a href="/auth/login-views.php">Login</a><br>
<?php endif; ?>

<a href="/animes/komentar.php">Tampilkan Data</a><br>

<!-- Kode tambahan -->
<?php
// users.php
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Users</title>
    <style>
        table {
            width: 70%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #888;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Daftar Users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
