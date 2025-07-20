<?php
// File: objects/Comment.php

class Comment {
    private $conn;
    private $table_name = "comments";

    public $id;
    public $user_id;
    public $anime_mal_id;
    public $comment_text;
    public $created_at;
    public $username; // untuk join dengan tabel users

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tambah komentar
    public function add() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET user_id = :user_id, anime_mal_id = :anime_mal_id, comment_text = :comment_text, created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->anime_mal_id = htmlspecialchars(strip_tags($this->anime_mal_id));
        $this->comment_text = htmlspecialchars(strip_tags($this->comment_text));
        $this->created_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(":anime_mal_id", $this->anime_mal_id, PDO::PARAM_INT);
        $stmt->bindParam(":comment_text", $this->comment_text, PDO::PARAM_STR);
        $stmt->bindParam(":created_at", $this->created_at);

        return $stmt->execute();
    }

    // Ambil komentar berdasarkan anime_mal_id
    public function getByAnimeMalId() {
        $query = "SELECT c.id, c.comment_text, c.created_at, c.user_id, u.username
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.anime_mal_id = ?
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);

        $this->anime_mal_id = htmlspecialchars(strip_tags($this->anime_mal_id));
        $stmt->bindParam(1, $this->anime_mal_id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    // Ambil komentar berdasarkan ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->user_id = $row['user_id'];
            $this->anime_mal_id = $row['anime_mal_id'];
            $this->comment_text = $row['comment_text'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Update komentar
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET comment_text = :comment_text
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $this->comment_text = htmlspecialchars(strip_tags($this->comment_text));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(':comment_text', $this->comment_text, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    // Hapus komentar
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
?>
