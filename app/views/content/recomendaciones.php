<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

    <div class="d-flex align-items-center mb-4 border-bottom pb-2 mt-3">
        <i class="bi bi-stars text-warning fs-2 me-2"></i>
        <h2 class="mb-0 fw-bold">Recomendaciones para Ti</h2>
    </div>

<?php if(!empty($recomendados)): ?>
    <div class="alert alert-info shadow-sm border-0"><i class="bi bi-info-circle"></i> Basado en tus preferencias y nuestro catálogo local:</div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
        <?php foreach($recomendados as $item): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
                    <?php if(!empty($item['poster_url'])): ?>
                        <img src="<?= htmlspecialchars($item['poster_url']) ?>" class="card-img-top poster-img" alt="Poster">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-truncate"><?= htmlspecialchars($item['titulo']) ?></h5>
                        <div class="mt-auto">
                            <span class="badge bg-primary w-100 py-2">Recomendado</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif(!empty($apiRecomendados)): ?>
    <div class="alert alert-success shadow-sm border-0"><i class="bi bi-film"></i> No encontramos mucho en nuestra base local, pero esto de TMDb te encantará:</div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
        <?php foreach($apiRecomendados as $item): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm bg-body-tertiary">

                    <?php if(!empty($item['poster_path'])): ?>
                        <?php $posterUrl = "https://image.tmdb.org/t/p/w500" . $item['poster_path']; ?>
                        <img src="<?= htmlspecialchars($posterUrl) ?>" class="card-img-top poster-img" alt="Poster">
                    <?php else: ?>
                        <div class="card-img-top poster-img bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 300px;">
                            <span>Sin Imagen</span>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-truncate" title="<?= htmlspecialchars($item['title']) ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </h5>

                        <p class="card-text small text-muted mb-3">
                            <i class="bi bi-star-fill text-warning"></i> <?= htmlspecialchars(round($item['vote_average'], 1) ?? 'N/A') ?>
                        </p>

                        <a href="https://www.themoviedb.org/movie/<?= $item['id'] ?>" target="_blank" class="btn btn-outline-success fw-bold btn-sm mt-auto w-100">
                            Ver en TMDb
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning shadow-sm border-0">
        <i class="bi bi-exclamation-triangle"></i> Aún no sabemos qué te gusta. <a href="/streammatch/public/preferencias" class="alert-link fw-bold">Actualiza tus géneros aquí</a>.
    </div>
<?php endif; ?>

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>