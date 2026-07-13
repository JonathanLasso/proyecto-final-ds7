<?php
require_once BASE_PATH . "/app/models/Preference.php";
require_once BASE_PATH . "/app/models/User.php";

class UserController {
    private $db;
    private $preference;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->preference = new Preference($this->db);
        $this->user = new User($this->db);
    }

    public function preferences() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /streammatch/public/login");
            exit();
        }

        $userId = $_SESSION['usuario_id'];
        $prefModel = new Preference($this->db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recogemos el array de checkboxes (si no marcó ninguno, pasamos un array vacío)
            $selectedGenres = $_POST['generos'] ?? [];
            $selectedGenres = array_map('intval', $selectedGenres);

            $success = $prefModel->saveUserPreferences($userId, $selectedGenres);

            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Preferencias actualizadas correctamente.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'No se pudieron guardar las preferencias.'];
            }

            header("Location: /streammatch/public/preferences");
            exit();
        }

        $allGenres = $prefModel->getAllGenres();
        $userGenres = $prefModel->getUserPreferences($userId);

        require_once BASE_PATH . "/app/views/user/preferences.php";
    }

    public function updateTheme()
    {
        // Detectar el tema actual enviado por la URL y alternarlo
        $currentTheme = $_GET['current'] ?? 'light';
        $newTheme = ($currentTheme === 'dark') ? 'light' : 'dark';

        // Guardar el nuevo tema en la cookie para persistencia local
        setcookie("theme", $newTheme, time() + (86400 * 30), "/");

        if (isset($_SESSION['usuario_id'])) {
            $this->user->id = $_SESSION['usuario_id'];
            $this->user->tema = $newTheme;

            $this->user->updateTheme();
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? '/streammatch/public/';
        header("Location: " . $referer);
        exit();
    }
}
