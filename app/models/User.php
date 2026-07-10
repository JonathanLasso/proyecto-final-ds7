<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $rol;
    public $tema;
    public $intentos_fallidos;
    public $bloqueado_hasta;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function emailExists() {
        $query = "SELECT id, nombre, password, rol, tema, intentos_fallidos, bloqueado_hasta FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->password = $row['password'];
            $this->rol = $row['rol'];
            $this->tema = $row['tema'];
            $this->intentos_fallidos = $row['intentos_fallidos'];
            $this->bloqueado_hasta = $row['bloqueado_hasta'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre, email=:email, password=:password, rol=:rol";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->rol = 'usuario';

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":rol", $this->rol);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateTheme() {
        $query = "UPDATE " . $this->table_name . " SET tema = :tema WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tema', $this->tema);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    
    public function updateFailedAttempts($reset = false) {
        if ($reset) {
            $query = "UPDATE " . $this->table_name . " SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE email = :email";
        } else {
            $this->intentos_fallidos++;
            $bloqueado = NULL;
            if ($this->intentos_fallidos >= 5) {
                $bloqueado = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            $query = "UPDATE " . $this->table_name . " SET intentos_fallidos = :intentos, bloqueado_hasta = :bloqueo WHERE email = :email";
        }
        
        $stmt = $this->conn->prepare($query);
        if (!$reset) {
            $stmt->bindParam(':intentos', $this->intentos_fallidos);
            $stmt->bindParam(':bloqueo', $bloqueado);
        }
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
    }
}
