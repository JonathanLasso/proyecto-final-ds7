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
            $this->user->email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

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
            $this->user->nombre = trim($_POST['nombre'] ?? '');
            $this->user->email = filter_input(INPUT_POST, 'email',FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : '';
            $this->user->password = trim($_POST['password'] ?? '');

            if ($this->user->emailExists()) {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'text' => 'El correo ya está registrado.'
                ];
                header("Location: /streammatch/public/register");
                exit();
            }
            /*
               Explicación de la expresión regular:
               - (?=.*[A-Za-z]): Al menos una letra (mayúscula o minúscula).
               - (?=.*\d): Al menos un número.
               - (?=.*[\W_]): Al menos un carácter especial (no alfanumérico, incluye guiones bajos).
               - .{15,}: Mínimo 15 caracteres de longitud.
            */
            $patternPassword = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).{15,}$/';
            if (!preg_match($patternPassword, $this->user->password)) {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'La contraseña debe tener al menos 15 caracteres e incluir letras, números y caracteres especiales (ej. @, #, $, _).'
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
