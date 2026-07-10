<?php
class Router {
    public function route($url) {
        // Definición de rutas simples
        $routes = [
            '' => ['controller' => 'ContentController', 'method' => 'home'],
            'home' => ['controller' => 'ContentController', 'method' => 'home'],
            'login' => ['controller' => 'AuthController', 'method' => 'login'],
            'register' => ['controller' => 'AuthController', 'method' => 'register'],
            'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
            'preferencias' => ['controller' => 'UserController', 'method' => 'preferencias'],
            'recomendaciones' => ['controller' => 'ContentController', 'method' => 'recomendaciones'],

            // NUEVA RUTA: Mapea la acción del formulario con el método guardar() de tu ContentController
            'content/guardar' => ['controller' => 'ContentController', 'method' => 'guardar'],

            'admin' => ['controller' => 'AdminController', 'method' => 'dashboard'],
            'admin/export_json' => ['controller' => 'AdminController', 'method' => 'exportJson'],
            'admin/export_xml' => ['controller' => 'AdminController', 'method' => 'exportXml'],
            'admin/import' => ['controller' => 'AdminController', 'method' => 'importData']
        ];

        // Limpiar la URL de barras finales
        $url = trim($url, '/');

        if (array_key_exists($url, $routes)) {
            $controllerName = $routes[$url]['controller'];
            $methodName = $routes[$url]['method'];

            $controllerPath = BASE_PATH . "/app/controllers/" . $controllerName . ".php";
            if (file_exists($controllerPath)) {
                require_once $controllerPath;
                $controller = new $controllerName();
                $controller->$methodName();
            } else {
                $this->show404("Controlador no encontrado.");
            }
        } else {
            $this->show404();
        }
    }

    private function show404($message = "Página no encontrada") {
        http_response_code(404);
        require_once BASE_PATH . "/app/views/layouts/header.php";
        echo "<div class='container mt-5 text-center'>
                <h1 class='display-4 text-danger'>404</h1>
                <p class='lead'>$message</p>
                <a href='/streammatch/public/home' class='btn btn-primary'>Volver al Inicio</a>
              </div>";
        require_once BASE_PATH . "/app/views/layouts/footer.php";
    }
}