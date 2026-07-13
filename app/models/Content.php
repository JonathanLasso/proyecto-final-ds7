<?php
class Content {
    private $conn;
    private $table_name = "contenido";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        // Usamos GROUP_CONCAT para traer todos los géneros asociados en una sola cadena limpia
        $query = "SELECT c.*, GROUP_CONCAT(g.nombre SEPARATOR ', ') AS generos
              FROM " . $this->table_name . " c
              LEFT JOIN contenido_generos cg ON c.id = cg.contenido_id
              LEFT JOIN generos g ON cg.genero_id = g.id
              GROUP BY c.id
              ORDER BY c.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveFromApiWithGenres($titulo, $tipo, $descripcion, $poster_url, $api_id = null, $genero_id = []) {
        // Si viene un api_id, verificamos que no esté duplicado
        if (!empty($api_id)) {
            $check = "SELECT id FROM " . $this->table_name . " WHERE api_id = ?";
            $stmtCheck = $this->conn->prepare($check);
            $stmtCheck->execute([$api_id]);
            if ($stmtCheck->rowCount() > 0) return false;
        }
        else {
            // Si es creación manual y no viene api_id, lo guardamos como NULL
            $api_id = null;
        }
        try{

            // Iniciamos una transacción: o se guarda todo (película y géneros) o nada
            $this->conn->beginTransaction();

            // 2. Insertar el contenido principal
            $query = "INSERT INTO " . $this->table_name . " (titulo, tipo, descripcion, poster_url, api_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$titulo, $tipo, $descripcion, $poster_url, $api_id]);

            // 3. Recuperar el ID asignado automáticamente por MySQL
            $contenidoId = $this->conn->lastInsertId();

            // 4. Insertar la relación de géneros si existen
            if ($contenidoId && !empty($genero_id)) {
                foreach ($genero_id as $gId) {
                    $this->assignGender($contenidoId, $gId);
                }
            }

            $this->conn->commit();
            return true;

        }catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function assignGender($contenidoId, $generoId) {
        $query = "INSERT INTO contenido_generos (contenido_id, genero_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$contenidoId, $generoId]);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $titulo, $tipo, $descripcion, $poster_url, $api_id = null) {
        // Si el api_id viene vacío desde el formulario de edición, lo seteamos como null
        $api_id = !empty($api_id) ? $api_id : null;

        $query = "UPDATE " . $this->table_name . " 
                  SET titulo = :titulo, tipo = :tipo, descripcion = :descripcion, poster_url = :poster_url, api_id = :api_id 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':poster_url', $poster_url);
        $stmt->bindParam(':api_id', $api_id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteAll() {
        $query = "DELETE FROM " . $this->table_name . "";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}