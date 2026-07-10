<?php
class Content {
    private $conn;
    private $table_name = "contenido";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecommended($userId) {
        $query = "SELECT DISTINCT c.* FROM contenido c
                  JOIN contenido_generos cg ON c.id = cg.contenido_id
                  JOIN preferencias_usuario pu ON cg.genero_id = pu.genero_id
                  WHERE pu.usuario_id = :user_id
                  ORDER BY RAND() LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function saveFromApi($titulo, $tipo, $descripcion, $poster_url, $api_id) {
        $check = "SELECT id FROM " . $this->table_name . " WHERE api_id = ?";
        $stmtCheck = $this->conn->prepare($check);
        $stmtCheck->execute([$api_id]);
        if ($stmtCheck->rowCount() > 0) return false;

        $query = "INSERT INTO " . $this->table_name . " (titulo, tipo, descripcion, poster_url, api_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$titulo, $tipo, $descripcion, $poster_url, $api_id]);
    }
}
