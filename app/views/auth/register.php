<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card mt-5 shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
                    <h3 class="card-title mt-2">Crear Cuenta</h3>
                </div>
                <form action="/streammatch/public/register" method="POST">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="juan@ejemplo.com" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Crea una contraseña segura" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold">Registrarse</button>
                </form>
                <div class="text-center mt-3">
                    <p class="text-muted small">¿Ya tienes cuenta? <a href="/streammatch/public/login" class="text-decoration-none">Inicia sesión</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>
