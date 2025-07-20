<?php
session_start(); // Selalu panggil ini dulu

// Hapus semua variabel sesi
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: ../index.php");
exit;
