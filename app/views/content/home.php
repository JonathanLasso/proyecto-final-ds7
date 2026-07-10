<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

    <div class="mb-5 bg-primary text-white p-5 rounded shadow-sm text-center bg-gradient">
        <h1 class="display-4 fw-bold">Bienvenido a StreamMatch</h1>
        <p class="lead">Descubre las mejores películas en tiempo real con TMDb.</p>
        <form action="/streammatch/public/home" method="GET" class="d-flex justify-content-center mt-4 mx-auto" style="max-width: 600px;">
            <div class="input-group input-group-lg shadow-sm">
                <input type="text" name="q" class="form-control border-0" placeholder="Buscar películas en TMDb (Ej. Inception)..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-dark px-4 fw-bold" type="submit"><i class="bi bi-search"></i> Buscar</button>
            </div>
        </form>
    </div>

    <div class="container-fluid px-0">
        <h3 class="mb-4 border-bottom pb-2 fw-bold text-secondary"><i class="bi bi-film"></i> Resultados de TMDb</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            <?php if(!empty($apiResults)): ?>
                <?php foreach($apiResults as $item): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm bg-body-tertiary">

                            <?php if(!empty($item['poster_path'])): ?>
                                <?php $posterUrl = "https://image.tmdb.org/t/p/w500" . $item['poster_path']; ?>
                                <img src="<?= htmlspecialchars($posterUrl) ?>" class="card-img-top poster-img" alt="Poster">
                            <?php else: ?>
                                <div class="card-img-top poster-img bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 350px;">
                                    <span>Sin Imagen</span>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-truncate" title="<?= htmlspecialchars($item['title']) ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </h5>

                                <p class="card-text small text-muted mb-3">
                                    <i class="bi bi-star-fill text-warning"></i> <?= htmlspecialchars(round($item['vote_average'], 1) ?? 'N/A') ?>
                                    <span class="float-end badge bg-info text-dark">Película</span>
                                </p>

                                <div class="mt-auto">
                                    <a href="https://www.themoviedb.org/movie/<?= $item['id'] ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-bold mb-2">
                                        Ver Detalles
                                    </a>

                                    <form action="/streammatch/public/content/guardar" method="POST">
                                        <input type="hidden" name="api_id" value="<?= htmlspecialchars($item['id']) ?>">
                                        <input type="hidden" name="titulo" value="<?= htmlspecialchars($item['title']) ?>">
                                        <input type="hidden" name="descripcion" value="<?= htmlspecialchars($item['overview'] ?? '') ?>">
                                        <input type="hidden" name="poster_url" value="<?= htmlspecialchars("https://image.tmdb.org/t/p/w500" . ($item['poster_path'] ?? '')) ?>">

                                        <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                            <i class="bi bi-download"></i> Importar a Local
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted"><p>No se encontraron resultados.</p></div>
            <?php endif; ?>
        </div>

        <h3 class="mb-4 border-bottom pb-2 fw-bold text-secondary"><i class="bi bi-hdd-stack"></i> Catálogo Local</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            <?php if(!empty($localContent)): ?>
                <?php foreach($localContent as $item): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                            <?php if(!empty($item['poster_url'])): ?>
                                <img src="<?= htmlspecialchars($item['poster_url']) ?>" class="card-img-top poster-img" alt="Poster">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-truncate"><?= htmlspecialchars($item['titulo']) ?></h5>
                                <div class="mt-auto">
                                    <span class="badge bg-secondary w-100 py-2"><?= htmlspecialchars(ucfirst($item['tipo'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-muted"><p>El catálogo local está vacío. El administrador puede importar datos desde el panel.</p></div>
            <?php endif; ?>
        </div>
    </div>

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>