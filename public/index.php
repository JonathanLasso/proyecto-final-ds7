<?php
session_start();

// Definir constante con la ruta base del proyecto (C:\xampp\htdocs\streammatch)
define('BASE_PATH', dirname(__DIR__));

// Requerir dependencias core
require_once BASE_PATH . "/app/config/database.php";
require_once BASE_PATH . "/app/core/Router.php";

// Obtener la URL amigable solicitada
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Instanciar enrutador y procesar petición
$router = new Router();
$router->route($url);
