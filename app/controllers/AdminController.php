<?php
require_once BASE_PATH . "/app/models/Content.php";
require_once BASE_PATH . "/app/models/User.php";

class AdminController
{
    private $db;
    private $content;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->content = new Content($this->db);
        $this->user = new User($this->db);
    }

    public function login()
    {
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') {
            header("Location: /streammatch/public/admin");
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

                        if ($this->user->tema) {
                            setcookie("theme", $this->user->tema, time() + (86400 * 30), "/");
                        }

                        header("Location: /streammatch/public/admin");
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

        require_once BASE_PATH . "/app/views/admin/login.php";
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->nombre = $_POST['nombre'] ?? '';
            $this->user->email = $_POST['email'] ?? '';
            $this->user->password = $_POST['password'] ?? '';
            $this->user->rol = "administrador";

            if ($this->user->emailExists()) {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'text' => 'El correo ya está registrado.'
                ];
                header("Location: /streammatch/public/admin/register");
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
                    header("Location: /streammatch/public/admin/login");
                    exit();
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'danger',
                        'text' => 'Error al crear el usuario.'
                    ];
                }
            }
        }

        require_once BASE_PATH . "/app/views/admin/register.php";
    }

    public function dashboard()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login"); // Asegúrate de que esta sea tu ruta de login
            exit();
        }
        $localContent = $this->content->getAll();
        require_once BASE_PATH . "/app/views/admin/dashboard.php";
    }

    // Cambiado de exportJson a export_json para hacer match con tu vista
    public function export_json()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        } else {
            $data = $this->content->getAll();
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Descarga directa sin escribir archivos físicos en el servidor
            header('Content-disposition: attachment; filename=export.json');
            header('Content-type: application/json; charset=utf-8');
            echo $json;
            exit();
        }
    }

    // Cambiado de exportXml a export_xml para hacer match con tu vista
    public function export_xml()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        } else {
            $data = $this->content->getAll();

            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><catalogo></catalogo>');

            foreach ($data as $item) {
                $node = $xml->addChild('contenido');
                $node->addChild('id', $item['id']);
                $node->addChild('titulo', htmlspecialchars($item['titulo']));
                $node->addChild('tipo', $item['tipo']);
                // Agregamos descripción que falto en tu XML original pero la usas en la importación
                $node->addChild('descripcion', htmlspecialchars($item['descripcion'] ?? ''));
                $node->addChild('poster_url', htmlspecialchars($item['poster_url']));
                $node->addChild('api_id', htmlspecialchars($item['api_id']));
            }

            // Descarga directa del XML por flujo de salida
            header('Content-disposition: attachment; filename=feed.xml');
            header('Content-type: text/xml; charset=utf-8');
            echo $xml->asXML();
            exit();
        }
    }

    // Cambiado de importData a import para que coincida con action="/admin/import"
    public function import()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
                $fileTmpPath = $_FILES['import_file']['tmp_name'];
                $fileName = $_FILES['import_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($fileExtension === 'json') {
                    $jsonString = file_get_contents($fileTmpPath);
                    $data = json_decode($jsonString, true);
                    $count = 0;
                    if (is_array($data)) {
                        foreach ($data as $item) {
                            if ($this->content->saveFromApi($item['titulo'], $item['tipo'], $item['descripcion'] ?? '', $item['poster_url'] ?? '', $item['api_id'] ?? '')) {
                                $count++;
                            }
                        }
                    }
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Importados $count registros desde JSON."];
                } elseif ($fileExtension === 'xml') {
                    $xmlString = file_get_contents($fileTmpPath);
                    $xml = simplexml_load_string($xmlString);
                    $count = 0;
                    if ($xml !== false) {
                        foreach ($xml->contenido as $item) {
                            // Usamos la validación ternaria por si el nodo descripción viene vacío en el XML externo
                            $descripcion = isset($item->descripcion) ? (string)$item->descripcion : '';
                            if ($this->content->saveFromApi((string)$item->titulo, (string)$item->tipo, $descripcion, (string)$item->poster_url, (string)$item->api_id)) {
                                $count++;
                            }
                        }
                    }
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Importados $count registros desde XML."];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => "Formato no soportado. Sólo JSON o XML."];
                }
            }
            header("Location: /streammatch/public/admin");
            exit();
        }
    }

    // 1. AGREGAR CONTENIDO MANUALMENTE
    public function create()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Limpieza básica de datos recibidos del modal
            $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
            $tipo = ($_POST['tipo'] === 'serie') ? 'serie' : 'pelicula';
            $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
            $poster_url = filter_input(INPUT_POST, 'poster_url', FILTER_VALIDATE_URL) ? $_POST['poster_url'] : '';
            $api_id = !empty($_POST['api_id']) ? trim($_POST['api_id']) : null;

            if (!empty($titulo)) {
                if ($this->content->saveFromApi($titulo, $tipo, $descripcion, $poster_url, $api_id)) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'Contenido agregado exitosamente desde el panel.'
                    ];
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'danger',
                        'text' => 'Error al guardar el contenido o el ID externo ya existe.'
                    ];
                }
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'text' => 'El título es un campo obligatorio.'
                ];
            }
        }

        // Siempre redirige de vuelta al dashboard, jamás carga una vista individual
        header("Location: /streammatch/public/admin");
        exit();
    }

    // 2. ACTUALIZAR / EDITAR CONTENIDO
    public function update()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
            $tipo = ($_POST['tipo'] === 'serie') ? 'serie' : 'pelicula';
            $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
            $poster_url = $_POST['poster_url'] ?? '';
            $api_id = !empty($_POST['api_id']) ? trim($_POST['api_id']) : null;

            if ($id && !empty($titulo)) {
                if ($this->content->update($id, $titulo, $tipo, $descripcion, $poster_url, $api_id)) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'Contenido actualizado correctamente.'
                    ];
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'danger',
                        'text' => 'Error al actualizar el contenido en la base de datos.'
                    ];
                }
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Datos inválidos. No se pudo procesar la actualización.'
                ];
            }
        }

        // Redirección inmediata al entorno unificado
        header("Location: /streammatch/public/admin");
        exit();
    }

    // 3. ELIMINAR CONTENIDO
    public function delete()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if ($id) {
                if ($this->content->delete($id)) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'Contenido eliminado permanentemente.'
                    ];
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'danger',
                        'text' => 'Error del sistema al intentar eliminar el registro.'
                    ];
                }
            }
        }

        header("Location: /streammatch/public/admin");
        exit();
    }
}