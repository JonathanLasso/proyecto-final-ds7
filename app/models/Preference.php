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
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveUserPreferences($userId, $genresArray) {
        try {
            // Iniciamos transacción para asegurar que se borre e inserte todo correctamente
            $this->conn->beginTransaction();

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
                    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                    $stmt->bindValue(':genero_id', $genreId, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            $this->conn->commit();
            return true;

        }catch (Exception $e) {
            // Si algo falla, revertimos para no dejar al usuario sin preferencias antiguas ni nuevas
            $this->conn->rollBack();
            return false;
        }
    }
}
