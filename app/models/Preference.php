<?php
class Preference {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllGenres() {
        $query = "SELECT * FROM generos ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserPreferences($userId) {
        $query = "SELECT genero_id FROM preferencias_usuario WHERE usuario_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveUserPreferences($userId, $genresArray) {
        // Eliminar las antiguas
        $query = "DELETE FROM preferencias_usuario WHERE usuario_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        // Insertar nuevas
        if (!empty($genresArray)) {
            $query = "INSERT INTO preferencias_usuario (usuario_id, genero_id) VALUES (:user_id, :genero_id)";
            $stmt = $this->conn->prepare($query);
            foreach ($genresArray as $genreId) {
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':genero_id', $genreId);
                $stmt->execute();
            }
        }
        return true;
    }
}
