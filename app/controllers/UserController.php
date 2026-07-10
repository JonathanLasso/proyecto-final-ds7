<?php
require_once BASE_PATH . "/app/models/Preference.php";

class UserController {
    private $db;
    private $preference;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->preference = new Preference($this->db);
    }

    public function preferencias() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /streammatch/public/login");
            exit();
        }

        $userId = $_SESSION['usuario_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedGenres = $_POST['generos'] ?? [];
            if ($this->preference->saveUserPreferences($userId, $selectedGenres)) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'Preferencias guardadas exitosamente.'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Error al guardar las preferencias.'
                ];
            }
        }

        $allGenres = $this->preference->getAllGenres();
        $userGenres = $this->preference->getUserPreferences($userId);

        require_once BASE_PATH . "/app/views/user/preferencias.php";
    }
}
