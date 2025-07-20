<?php
session_start(); // WAJIB diletakkan paling atas, sebelum output HTML apapun

include_once '../../config/database.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, username, password FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                // Simpan ke session
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect ke halaman komentar
                header("Location: ../../index.php");//C:\laragon\www\animelist-api\api\auth\login-views.php
                exit();
            } else {
                $message = "<span style='color:red;'>Password salah.</span>";
            }
        } else {
            $message = "<span style='color:red;'>User tidak ditemukan.</span>";
        }
    } catch (PDOException $e) {
        $message = "<span style='color:red;'>Error: " . $e->getMessage() . "</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - AnimeList</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 400px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .message {
      margin-top: 15px;
      text-align: center;
      color: red;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h2>Login AnimeList</h2>
  <form method="POST" action="">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>

  <?php if (!empty($message)): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>
</div>

</body>
</html>
