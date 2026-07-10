<?php
require_once BASE_PATH . "/app/models/Content.php";

class AdminController {
    private $db;
    private $content;

    public function __construct() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header("Location: /streammatch/public/");
            exit();
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->content = new Content($this->db);
    }

    public function dashboard() {
        $localContent = $this->content->getAll();
        require_once BASE_PATH . "/app/views/admin/dashboard.php";
    }

    // Cambiado de exportJson a export_json para hacer match con tu vista
    public function export_json() {
        $data = $this->content->getAll();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Descarga directa sin escribir archivos físicos en el servidor
        header('Content-disposition: attachment; filename=export.json');
        header('Content-type: application/json; charset=utf-8');
        echo $json;
        exit();
    }

    // Cambiado de exportXml a export_xml para hacer match con tu vista
    public function export_xml() {
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

    // Cambiado de importData a import para que coincida con action="/admin/import"
    public function import() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
            $fileTmpPath = $_FILES['import_file']['tmp_name'];
            $fileName = $_FILES['import_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension === 'json') {
                $jsonString = file_get_contents($fileTmpPath);
                $data = json_decode($jsonString, true);
                $count = 0;
                if(is_array($data)) {
                    foreach($data as $item) {
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
                    foreach($xml->contenido as $item) {
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