<?php
require_once BASE_PATH . "/app/models/Content.php";
require_once BASE_PATH . "/app/services/ApiService.php";

class ContentController {
    private $db;
    private $content;
    private $api;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->content = new Content($this->db);
        $this->api = new ApiService();
    }

    public function home() {
        $searchTerm = $_GET['q'] ?? 'batman'; 
        $apiResults = $this->api->searchMovies($searchTerm);
        $localContent = $this->content->getAll();

        require_once BASE_PATH . "/app/views/content/home.php";
    }

    public function recomendaciones() {
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Debes iniciar sesión para ver tus recomendaciones.'
            ];
            header("Location: /streammatch/public/login");
            exit();
        }

        $userId = $_SESSION['usuario_id'];
        $recomendados = $this->content->getRecommended($userId);
        
        $apiRecomendados = [];
        if (empty($recomendados)) {
            require_once BASE_PATH . "/app/models/Preference.php";
            $prefModel = new Preference($this->db);
            $userGenresIds = $prefModel->getUserPreferences($userId);
            
            $busqueda = 'action'; 
            if(!empty($userGenresIds)){
                 $allG = $prefModel->getAllGenres();
                 foreach($allG as $g){
                     if($g['id'] == $userGenresIds[0]){
                         $busqueda = $g['nombre'];
                         break;
                     }
                 }
            }
            $apiRecomendados = $this->api->searchMovies($busqueda);
        }

        require_once BASE_PATH . "/app/views/content/recomendaciones.php";
    }

    public function guardar() {
        // Verificar si es una petición POST y si vienen los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_id'])) {

            // Mapeamos los datos que vienen del formulario/TMDb al formato de tu BD
            $titulo = $_POST['titulo'] ?? 'Sin título';
            $tipo = 'pelicula'; // O 'serie' según corresponda
            $descripcion = $_POST['descripcion'] ?? '';
            $poster_url = $_POST['poster_url'] ?? '';
            $api_id = $_POST['api_id'];

            // Llamamos al modelo que ya tienes listo
            $exito = $this->content->saveFromApi($titulo, $tipo, $descripcion, $poster_url, $api_id);

            if ($exito) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => '¡Película guardada en el catálogo local!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'warning', 'text' => 'La película ya existía en el catálogo o no se pudo guardar.'];
            }
        }

        // Redireccionar de vuelta a la página de inicio o donde prefieras
        header("Location: /streammatch/public/home");
        exit();
    }
}
