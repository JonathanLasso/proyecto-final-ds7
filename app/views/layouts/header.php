<?php
// Obtener tema de la cookie, por defecto 'light'
$tema = $_COOKIE['theme'] ?? 'light';
$bodyClass = ($tema === 'dark') ? 'bg-dark text-white' : 'bg-light text-dark';
$navbarClass = ($tema === 'dark') ? 'navbar-dark bg-dark' : 'navbar-light bg-light';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamMatch</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/streammatch/public/assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<nav class="navbar navbar-expand-lg <?= htmlspecialchars($navbarClass) ?> shadow-sm">
  <div class="container">
    <a class="navbar-brand text-primary fw-bold" href="/streammatch/public/">
        <i class="bi bi-play-circle-fill"></i> StreamMatch
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="/streammatch/public/">Inicio</a>
        </li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="/streammatch/public/recomendations">Recomendaciones</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/streammatch/public/preferences">Preferencias</a>
            </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
            <li class="nav-item">
              <a class="nav-link text-danger fw-bold" href="/streammatch/public/admin">Panel Admin</a>
            </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <!-- Theme Toggle -->
          <li class="nav-item me-3">
              <a href="/streammatch/public/theme/update?current=<?= $tema ?>" class="btn btn-outline-secondary btn-sm">
                  <i class="bi <?= $tema === 'dark' ? 'bi-sun' : 'bi-moon' ?>"></i>
              </a>
          </li>
        <?php if (!isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="/streammatch/public/login">Login</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-primary btn-sm ms-2" href="/streammatch/public/register">Registro</a>
            </li>
        <?php else: ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Perfil') ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="/streammatch/public/logout">Cerrar Sesión</a></li>
                </ul>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Contenedor principal para alertas globales -->
<div class="container mt-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
