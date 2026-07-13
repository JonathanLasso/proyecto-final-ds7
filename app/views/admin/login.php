<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mt-5 shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle text-primary" style="font-size: 3rem;"></i>
                    <h3 class="card-title mt-2">Iniciar Sesión de Administrador</h3>
                </div>
                <form action="/streammatch/public/admin/login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="******" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar</button>
                </form>
                <div class="text-center mt-3">
                    <p class="text-muted small">¿No tienes cuenta? <a href="/streammatch/public/admin/register" class="text-decoration-none">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>
