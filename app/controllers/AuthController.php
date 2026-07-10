<?php
require_once BASE_PATH . "/app/models/User.php";

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login() {
        if (isset($_SESSION['usuario_id'])) {
            header("Location: /streammatch/public/");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->user->emailExists()) {
                // Protección contra fuerza bruta
                if ($this->user->bloqueado_hasta && strtotime($this->user->bloqueado_hasta) > time()) {
                    $_SESSION['flash_message'] = [
                        'type' => 'danger',
                        'text' => 'Demasiados intentos fallidos. Intenta nuevamente más tarde.'
                    ];
                } else {
                    if (password_verify($password, $this->user->password)) {
                        $this->user->updateFailedAttempts(true); // Resetear intentos
                        $_SESSION['usuario_id'] = $this->user->id;
                        $_SESSION['nombre_usuario'] = $this->user->nombre;
                        $_SESSION['rol'] = $this->user->rol;
                        
                        if($this->user->tema) {
                            setcookie("theme", $this->user->tema, time() + (86400 * 30), "/");
                        }
                        
                        header("Location: /streammatch/public/");
                        exit();
                    } else {
                        $this->user->updateFailedAttempts(false);
                        $_SESSION['flash_message'] = [
                            'type' => 'danger',
                            'text' => 'Contraseña incorrecta.'
                        ];
                    }
                }
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Usuario no encontrado.'
                ];
            }
        }
        
        require_once BASE_PATH . "/app/views/auth/login.php";
    }

    public function register() {
        if (isset($_SESSION['usuario_id'])) {
            header("Location: /streammatch/public/");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->nombre = $_POST['nombre'] ?? '';
            $this->user->email = $_POST['email'] ?? '';
            $this->user->password = $_POST['password'] ?? '';

            if ($this->user->emailExists()) {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'text' => 'El correo ya está registrado.'
                ];
            } else {
                if ($this->user->create()) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'Registro exitoso. Ahora puedes iniciar sesión.'
                    ];
                    header("Location: /streammatch/public/login");
                    exit();
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'danger',
                        'text' => 'Error al crear el usuario.'
                    ];
                }
            }
        }

        require_once BASE_PATH . "/app/views/auth/register.php";
    }

    public function logout() {
        session_unset();
        session_destroy();
        // Borrar cookie de tema opcional, o dejarla. La dejamos.
        header("Location: /streammatch/public/");
        exit();
    }
}
