<?php
class ApiService {
    private $baseUrl = "https://api.themoviedb.org/3";
    // RECOMENDACIÓN: Guarda tu token en un archivo de configuración (.env) y no directamente en el código
    private $bearerToken = "eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIwMzcyMGZlNjcwYjYzZmQzMGQyZDRlYmNjMDIxYzQ2ZCIsIm5iZiI6MTc4MzYxMTUzOS42Niwic3ViIjoiNmE0ZmMwOTNkZWRhM2I5ZjdkNjE1MWZkIiwic2NvcGVzIjpbImFwaV9yZWFkIl0sInZlcnNpb24iOjF9.MomCqk2mmxbCF8IzCY5VGqNFayf-ooYHVvFINCjuPrs";

    /**
     * Busca películas por texto.
     * * @param string $query Texto a buscar (ej: "Inception")
     * @param string $language Idioma de los resultados (por defecto español)
     * @return array
     */
    public function searchMovies($query, $language = "es-ES") {
        $url = $this->baseUrl . "/search/movie?query=" . urlencode($query) . "&language=" . $language;

        $context = stream_context_create([
            "http" => [
                "method" => "GET",
                "header" => [
                    "Authorization: Bearer " . $this->bearerToken,
                    "Content-Type: application/json;charset=utf-8",
                    "User-Agent: StreamMatch PHP Application"
                ]
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return [];
        }

        $data = json_decode($response, true);

        // TMDb envuelve los resultados en una clave llamada 'results'
        return $data['results'] ?? [];
    }
}