<?php
require_once BASE_PATH . "/app/models/Content.php";
require_once BASE_PATH . "/app/models/User.php";
require_once BASE_PATH . "/app/models/Preference.php";

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
            $this->user->email = trim($_POST['email'] ?? '');
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
            // CORRECCIÓN XSS: Datos puros
            $this->user->nombre = trim($_POST['nombre'] ?? '');

            $emailInput = trim($_POST['email'] ?? '');
            if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Formato de correo inválido.'];
                require_once BASE_PATH . "/app/views/admin/register.php";
                return;
            }

            $this->user->email = $emailInput;
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

            $patternPassword = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).{15,}$/';
            if (!preg_match($patternPassword, $this->user->password)) {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'La contraseña debe tener al menos 15 caracteres e incluir letras, números y caracteres especiales.'
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
            header("Location: /streammatch/public/admin/login");
            exit();
        }
        $localContent = $this->content->getAll();
        $preferenceModel = new Preference($this->db);
        $allGenres = $preferenceModel->getAllGenres();

        require_once BASE_PATH . "/app/views/admin/dashboard.php";
    }

    public function export_json()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        } else {
            $data = $this->content->getAll();
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            header('Content-disposition: attachment; filename=export.json');
            header('Content-type: application/json; charset=utf-8');
            echo $json;
            exit();
        }
    }

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
                // XML requiere escapar de manera nativa los caracteres especiales al estructurar el árbol
                $node->addChild('titulo', htmlspecialchars($item['titulo'], ENT_XML1, 'UTF-8'));
                $node->addChild('tipo', $item['tipo']);
                $node->addChild('descripcion', htmlspecialchars($item['descripcion'] ?? '', ENT_XML1, 'UTF-8'));
                $node->addChild('poster_url', htmlspecialchars($item['poster_url'], ENT_XML1, 'UTF-8'));
                $node->addChild('api_id', htmlspecialchars($item['api_id'], ENT_XML1, 'UTF-8'));
            }

            header('Content-disposition: attachment; filename=feed.xml');
            header('Content-type: text/xml; charset=utf-8');
            echo $xml->asXML();
            exit();
        }
    }

    public function import()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
                $allowedMimeTypes = ['application/json', 'text/xml', 'application/xml'];
                $fileMimeType = mime_content_type($_FILES['import_file']['tmp_name']);

                if (!in_array($fileMimeType, $allowedMimeTypes)) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Tipo de archivo no permitido.'];
                    header("Location: /streammatch/public/admin");
                    exit();
                }

                $fileTmpPath = $_FILES['import_file']['tmp_name'];
                $fileName = $_FILES['import_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                require_once BASE_PATH . "/app/models/Preference.php";
                $prefModel = new Preference($this->db);
                $allGenres = $prefModel->getAllGenres();

                $localGenreMap = [];
                foreach ($allGenres as $g) {
                    $localGenreMap[strtolower(trim($g['nombre']))] = $g['id'];
                }

                $count = 0;

                if ($fileExtension === 'json') {
                    $jsonString = file_get_contents($fileTmpPath);
                    $data = json_decode($jsonString, true);

                    if (is_array($data)) {
                        foreach ($data as $item) {
                            $titulo = trim($item['titulo'] ?? '');
                            $tipo = ($item['tipo'] === 'serie') ? 'serie' : 'pelicula';
                            $descripcion = trim($item['descripcion'] ?? '');
                            $poster_url = trim($item['poster_url'] ?? '');
                            $api_id = trim($item['api_id'] ?? '');

                            $genderIds = [];
                            if (!empty($item['generos']) && is_array($item['generos'])) {
                                foreach ($item['generos'] as $genreName) {
                                    $normalized = strtolower(trim($genreName));
                                    if (isset($localGenreMap[$normalized])) {
                                        $genderIds[] = $localGenreMap[$normalized];
                                    }
                                }
                            }

                            if (!empty($titulo) && $this->content->saveFromApiWithGenres($titulo, $tipo, $descripcion, $poster_url, $api_id, $genderIds)) {
                                $count++;
                            }
                        }
                    }
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Importados $count registros desde JSON con sus géneros."];

                } elseif ($fileExtension === 'xml') {
                    $xmlString = file_get_contents($fileTmpPath);

                    libxml_use_internal_errors(true);
                    $xml = simplexml_load_string($xmlString);

                    if ($xml !== false) {
                        foreach ($xml->contenido as $item) {
                            // CORRECCIÓN XSS: Guardamos los strings limpios y originales sin htmlspecialchars
                            $titulo = trim((string)$item->titulo);
                            $tipo = ((string)$item->tipo === 'serie') ? 'serie' : 'pelicula';
                            $descripcion = isset($item->descripcion) ? trim((string)$item->descripcion) : '';
                            $poster_url = trim((string)$item->poster_url);
                            $api_id = trim((string)$item->api_id);

                            $genderIds = [];
                            if (isset($item->generos->genero)) {
                                foreach ($item->generos->genero as $gName) {
                                    $normalized = strtolower(trim((string)$gName));
                                    if (isset($localGenreMap[$normalized])) {
                                        $genderIds[] = $localGenreMap[$normalized];
                                    }
                                }
                            }

                            if (!empty($titulo) && $this->content->saveFromApiWithGenres($titulo, $tipo, $descripcion, $poster_url, $api_id, $genderIds)) {
                                $count++;
                            }
                        }
                    }
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Importados $count registros desde XML con sus géneros."];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => "Formato no soportado. Sólo JSON o XML."];
                }
            }
            header("Location: /streammatch/public/admin");
            exit();
        }
    }

    public function create()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CORRECCIÓN XSS: Quitar htmlspecialchars del guardado manual
            $titulo = trim($_POST['titulo'] ?? '');
            $tipo = ($_POST['tipo'] === 'serie') ? 'serie' : 'pelicula';
            $descripcion = trim($_POST['descripcion'] ?? '');
            $poster_url = filter_input(INPUT_POST, 'poster_url', FILTER_VALIDATE_URL) ? $_POST['poster_url'] : '';
            $api_id = !empty($_POST['api_id']) ? trim($_POST['api_id']) : null;
            $genderIds = $_POST['generos'] ?? [];

            if (!empty($titulo)) {
                if ($this->content->saveFromApiWithGenres($titulo, $tipo, $descripcion, $poster_url, $api_id, $genderIds)) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'Contenido agregado exitosamente con sus géneros desde el panel.'
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

        header("Location: /streammatch/public/admin");
        exit();
    }

    public function update()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            // CORRECCIÓN XSS: Quitar htmlspecialchars de la edición
            $titulo = trim($_POST['titulo'] ?? '');
            $tipo = ($_POST['tipo'] === 'serie') ? 'serie' : 'pelicula';
            $descripcion = trim($_POST['descripcion'] ?? '');
            $poster_url = filter_input(INPUT_POST, 'poster_url', FILTER_VALIDATE_URL) ? $_POST['poster_url'] : '';
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

        header("Location: /streammatch/public/admin");
        exit();
    }

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

    public function deleteAll()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/admin/login");
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if($this->content->deleteAll()) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'Todo el contenido fue eliminado.'
                ];
            }
            else{
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'No hay ningun contenido.'
                ];
            }
        }
        header("Location: /streammatch/public/admin");
        exit();
    }
}