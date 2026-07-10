<?php
require_once BASE_PATH . "/app/models/Content.php";
require_once BASE_PATH . "/app/models/User.php";

class AdminController {
    private $db;
    private $content;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->content = new Content($this->db);
        $this->user = new User($this->db);
    }

    public function login() {
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

                        if($this->user->tema) {
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

    public function register() {
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

    public function dashboard() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login"); // Asegúrate de que esta sea tu ruta de login
            exit();
        }
        $localContent = $this->content->getAll();
        require_once BASE_PATH . "/app/views/admin/dashboard.php";
    }

    // Cambiado de exportJson a export_json para hacer match con tu vista
    public function export_json() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }
        else {
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
    public function export_xml() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }
        else {
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
    public function import() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }
        else {
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
    public function create() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recoger y limpiar datos del formulario
            $titulo = $_POST['titulo'] ?? '';
            $tipo = $_POST['tipo'] ?? 'pelicula'; // O 'serie' según tu app
            $descripcion = $_POST['descripcion'] ?? '';
            $poster_url = $_POST['poster_url'] ?? '';
            $api_id = $_POST['api_id'] ?? null; // Puede ser nulo si es 100% manual

            // Reutilizamos tu método existente del modelo Content
            if ($this->content->saveFromApi($titulo, $tipo, $descripcion, $poster_url, $api_id)) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'Contenido agregado exitosamente de forma manual.'
                ];
                header("Location: /streammatch/public/admin");
                exit();
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Error al guardar el contenido.'
                ];
            }
        }

        // Carga la vista del formulario para agregar
        require_once BASE_PATH . "/app/views/admin/create.php";
    }

    // 2. ACTUALIZAR / EDITAR CONTENIDO
    public function update() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar la actualización
            $id = $_POST['id'] ?? null;
            $titulo = $_POST['titulo'] ?? '';
            $tipo = $_POST['tipo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $poster_url = $_POST['poster_url'] ?? '';
            $api_id = $_POST['api_id'] ?? null;

            if ($id) {
                // NOTA: Asegúrate de que tu modelo Content tenga un método update() similar a este
                if ($this->content->update($id, $titulo, $tipo, $descripcion, $poster_url, $api_id)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Contenido actualizado correctamente.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Error al actualizar el contenido.'];
                }
            }
            header("Location: /streammatch/public/admin");
            exit();
        } else {
            // Método GET: Cargar los datos actuales para mostrarlos en el formulario de edición
            $id = $_GET['id'] ?? null;
            if ($id) {
                // NOTA: Asegúrate de que tu modelo Content tenga un método para buscar por ID (ej: getById)
                $item = $this->content->getById($id);
                if ($item) {
                    require_once BASE_PATH . "/app/views/admin/edit.php";
                    exit();
                }
            }

            $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Contenido no encontrado.'];
            header("Location: /streammatch/public/admin");
            exit();
        }
    }

    // 3. ELIMINAR CONTENIDO
    public function delete() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        // Es más seguro recibir eliminaciones por POST para evitar ejecuciones accidentales por URL
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            if ($id) {
                // NOTA: Asegúrate de que tu modelo Content tenga un método delete()
                if ($this->content->delete($id)) {
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Contenido eliminado correctamente.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Error al eliminar el contenido.'];
                }
            }
        }

        header("Location: /streammatch/public/admin");
        exit();
    }
}