<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

<div class="row">
    <div class="col-md-10 col-lg-8 mx-auto">
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white p-3">
                <h4 class="mb-0"><i class="bi bi-sliders"></i> Mis Preferencias de Género</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">Selecciona los géneros que más te gustan para afinar tus recomendaciones.</p>
                <form action="/streammatch/public/preferencias" method="POST">
                    <div class="row g-3">
                        <?php foreach($allGenres as $genre): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check form-switch p-3 border rounded shadow-sm hover-shadow transition h-100 d-flex align-items-center">
                                    <input class="form-check-input ms-0 mt-0 me-3 custom-switch" type="checkbox" role="switch" name="generos[]" value="<?= $genre['id'] ?>" id="genre_<?= $genre['id'] ?>" <?= in_array($genre['id'], $userGenres) ? 'checked' : '' ?>>
                                    <label class="form-check-label d-block cursor-pointer flex-grow-1 user-select-none" for="genre_<?= $genre['id'] ?>">
                                        <span class="fw-bold"><?= htmlspecialchars($genre['nombre']) ?></span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success fw-bold px-4"><i class="bi bi-save"></i> Guardar Preferencias</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.custom-switch { width: 3em !important; height: 1.5em !important; cursor: pointer; }
.hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; border-color: var(--bs-primary)!important; }
.transition { transition: all 0.3s ease; }
</style>

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>
