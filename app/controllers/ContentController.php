<?php
require_once BASE_PATH . "/app/models/Content.php";
require_once BASE_PATH . "/app/services/ApiService.php";
require_once BASE_PATH . "/app/models/Preference.php";

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
        $searchTerm = $_GET['q'] ?? 'superman';
        $apiResults = $this->api->searchMovies($searchTerm);
        $localContent = $this->content->getAll();

        $prefModel = new Preference($this->db);
        $allGenres = $prefModel->getAllGenres();

        // Procesamos los resultados de la API de forma segura
        if (!empty($apiResults)) {
            $apiResults = $this->procesarGenerosAPI($apiResults, $allGenres);
        }

        require_once BASE_PATH . "/app/views/content/home.php";
    }

    private function procesarGenerosAPI(array $movies, array $allGenres): array {
        $tmdbGenders = [
            28 => 'Action', 12 => 'Adventure', 16 => 'Animation', 35 => 'Comedy',
            80 => 'Crime', 99 => 'Documentary', 18 => 'Drama', 10751 => 'Family',
            14 => 'Fantasy', 36 => 'History', 27 => 'Horror', 10402 => 'Music',
            9648 => 'Mystery', 10749 => 'Romance', 878 => 'Science Fiction',
            10770 => 'TV Movie', 53 => 'Thriller', 10752 => 'War', 37 => 'Western'
        ];

        // Mapeamos géneros locales [nombre_limpio => id_local]
        $localGenreMap = [];
        foreach ($allGenres as $g) {
            $localGenreMap[strtolower(trim($g['nombre']))] = $g['id'];
        }

        // Usamos un bucle normal por índice para evitar los bugs de las referencias por dirección ($item)
        foreach ($movies as $index => $item) {
            $nombresVisibles = [];
            $idsLocalesAEnviar = [];

            if (!empty($item['genre_ids'])) {
                foreach ($item['genre_ids'] as $tmdbId) {
                    if (isset($tmdbGenders[$tmdbId])) {
                        $nombreGenero = $tmdbGenders[$tmdbId];
                        $nombresVisibles[] = $nombreGenero;

                        $nombreBuscar = strtolower(trim($nombreGenero));
                        if (isset($localGenreMap[$nombreBuscar])) {
                            $idsLocalesAEnviar[] = $localGenreMap[$nombreBuscar];
                        }
                    }
                }
            }

            // Modificación segura directamente sobre el índice del array original
            $movies[$index]['generos_texto'] = !empty($nombresVisibles) ? implode(', ', $nombresVisibles) : 'Sin género';
            $movies[$index]['ids_locales_string'] = implode(',', $idsLocalesAEnviar);
        }

        return $movies;
    }

    public function recomendations() {
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Debes iniciar sesión para ver tus recomendaciones.'
            ];
            header("Location: /streammatch/public/login");
            exit();
        }

        $userId = $_SESSION['usuario_id'];
        $recommended = $this->content->getRecommended($userId);

        $apiRecommended = [];

        // Instanciamos Preference una sola vez afuera para usarla si es necesario
        $prefModel = new Preference($this->db);
        $allGenres = $prefModel->getAllGenres();

        if (empty($recommended)) {
            $userGenresIds = $prefModel->getUserPreferences($userId);

            $search = 'action';
            if (!empty($userGenresIds)) {
                foreach ($allG = $allGenres as $g) {
                    if ($g['id'] == $userGenresIds[0]) {
                        $search = $g['nombre'];
                        break;
                    }
                }
            }

            $apiResults = $this->api->searchMovies($search);

            // Procesamos los géneros de la API igual que en el Home para que la data sea consistente
            if (!empty($apiResults)) {
                $apiRecommended = $this->procesarGenerosAPI($apiResults, $allGenres);
            }
        }

        require_once BASE_PATH . "/app/views/content/recommended.php";
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_id'])) {
            $title = $_POST['titulo'] ?? 'Sin título';
            $type = 'pelicula';
            $description = $_POST['descripcion'] ?? '';
            $poster_url = $_POST['poster_url'] ?? '';
            $api_id = $_POST['api_id'];

            $genreString = $_POST['generos_api'] ?? '';
            $genderIds = !empty($genreString) ? explode(',', $genreString) : [];
            $genderIds = array_map('intval', $genderIds);

            $success = $this->content->saveFromApiWithGenres($title, $type, $description, $poster_url, $api_id, $genderIds);

            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => '¡Película guardada en el catálogo local con sus géneros!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'warning', 'text' => 'La película ya existía en el catálogo o no se pudo guardar.'];
            }
        }

        header("Location: /streammatch/public/home");
        exit();
    }
}
