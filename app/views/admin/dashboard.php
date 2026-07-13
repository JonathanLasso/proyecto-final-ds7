<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2 mt-3">
        <h2 class="mb-0 text-danger fw-bold"><i class="bi bi-shield-lock"></i> Panel de Administración</h2>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalAgregar"><i class="bi bi-plus-circle"></i> Agregar Manual</button>
            <a href="/streammatch/public/admin/export_json" class="btn btn-outline-secondary"><i class="bi bi-filetype-json"></i> Exportar JSON</a>
            <a href="/streammatch/public/admin/export_xml" class="btn btn-outline-secondary"><i class="bi bi-filetype-xml"></i> Exportar XML</a>
            <button class="btn btn-outline-danger ms-2" data-bs-toggle="modal" data-bs-target="#modalBorrarTodo">
                <i class="bi bi-trash-fill"></i> Vaciar
            </button>
        </div>
    </div>

<?php if(isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message']['text'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white p-3">
                    <h5 class="mb-0"><i class="bi bi-cloud-upload"></i> Importar Catálogo</h5>
                </div>
                <div class="card-body p-4">
                    <form action="/streammatch/public/admin/import" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="import_file" class="form-label text-muted">Sube un archivo JSON o XML</label>
                            <input class="form-control" type="file" id="import_file" name="import_file" accept=".json,.xml" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Ejecutar Importación</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white p-3">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Catálogo Local (<?= count($localContent) ?> items)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-dark">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Género</th>
                                <th>Agregado</th>
                                <th class="text-end pe-4">Acciones</th> </tr>
                            </thead>
                            <tbody>
                            <?php foreach($localContent as $item): ?>
                                <tr>
                                    <td class="ps-4"><?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($item['titulo']) ?></td>
                                    <td>
                                        <span class="badge <?= $item['tipo'] === 'pelicula' ? 'bg-primary' : 'bg-info text-dark' ?>">
                                            <?= htmlspecialchars(ucfirst($item['tipo'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= !empty($item['generos']) ? htmlspecialchars($item['generos']) : 'Sin géneros' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= !empty($item['creado_en']) ? date('d/m/Y', strtotime($item['creado_en'])) : 'N/A' ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button"
                                                    class="btn btn-outline-primary btn-editar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditar"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-titulo="<?= htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-tipo="<?= htmlspecialchars($item['tipo'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-descripcion="<?= htmlspecialchars((string)($item['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-poster="<?= htmlspecialchars($item['poster_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-api="<?= htmlspecialchars($item['api_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-eliminar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEliminar"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-titulo="<?= htmlspecialchars($item['titulo']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($localContent)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No hay contenido en la base de datos.<br>Sube un archivo XML o JSON o agrega uno manualmente.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalAgregarLabel"><i class="bi bi-plus-circle"></i> Agregar Contenido Nuevo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/streammatch/public/admin/create" method="POST">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label fw-bold text-muted">Título de la Obra</label>
                                    <input type="text" class="form-control" name="titulo" required placeholder="Ej: Inception">
                                </div>
                                <div class="mb-3">
                                    <label for="tipo" class="form-label fw-bold text-muted">Tipo de Contenido</label>
                                    <select class="form-select" name="tipo" required>
                                        <option value="pelicula">Película</option>
                                        <option value="serie">Serie</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="poster_url" class="form-label fw-bold text-muted">URL del Póster / Imagen</label>
                                    <input type="url" class="form-control" name="poster_url" placeholder="https://ejemplo.com/imagen.jpg">
                                </div>
                                <div class="mb-3">
                                    <label for="api_id" class="form-label fw-bold text-muted">ID Externo de API (Opcional)</label>
                                    <input type="text" class="form-control" name="api_id" placeholder="Ej: tt0133093">
                                </div>
                            </div>

                            <div class="col-md-6 border-start ps-4">
                                <label class="form-label fw-bold text-muted d-block mb-2"><i class="bi bi-tags-fill"></i> Selecciona los Géneros</label>
                                <div class="row g-2 overflow-auto" style="max-height: 250px;">
                                    <?php if(!empty($allGenres)): ?>
                                        <?php foreach($allGenres as $genre): ?>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="generos[]" value="<?= $genre['id'] ?>" id="genre_<?= $genre['id'] ?>">
                                                    <label class="form-check-label text-secondary" for="genre_<?= $genre['id'] ?>">
                                                        <?= htmlspecialchars($genre['nombre']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small">No hay géneros cargados en la base de datos.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="descripcion" class="form-label fw-bold text-muted">Descripción / Sinopsis</label>
                            <textarea class="form-control" name="descripcion" rows="3" placeholder="Escribe un breve resumen de la trama..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold">Guardar Contenido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarLabel"><i class="bi bi-pencil-square"></i> Editar Contenido</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/streammatch/public/admin/update" method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_titulo" class="form-label fw-bold text-muted">Título</label>
                            <input type="text" class="form-control" name="titulo" id="edit_titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tipo" class="form-label fw-bold text-muted">Tipo</label>
                            <select class="form-select" name="tipo" id="edit_tipo" required>
                                <option value="pelicula">Película</option>
                                <option value="serie">Serie</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_poster_url" class="form-label fw-bold text-muted">URL del Póster</label>
                            <input type="url" class="form-control" name="poster_url" id="edit_poster_url">
                        </div>
                        <div class="mb-3">
                            <label for="edit_api_id" class="form-label fw-bold text-muted">ID Externo API (Opcional)</label>
                            <input type="text" class="form-control" name="api_id" id="edit_api_id">
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label fw-bold text-muted">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel"><i class="bi bi-exclamation-triangle"></i> ¿Eliminar?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/streammatch/public/admin/delete" method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-body text-center p-4">
                        <p class="mb-1 text-muted fs-6">Vas a borrar de forma permanente</p>
                        <h6 class="fw-bold mb-0" id="delete_titulo"></h6>
                    </div>
                    <div class="modal-footer justify-content-center border-0 bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">No, cancelar</button>
                        <button type="submit" class="btn btn-sm btn-danger fw-bold">Sí, eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalBorrarTodo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow bg-dark text-light">
                <div class="modal-header border-bottom-0 bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> ¡Advertencia!</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <p>¿Estás seguro de que deseas <strong>eliminar TODO el catálogo</strong>? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer border-top-0 bg-dark justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="/streammatch/public/admin/deleteAll" method="POST">
                        <button type="submit" class="btn btn-danger">Sí, borrar todo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Pasar datos al modal de Edición
            const botonesEditar = document.querySelectorAll('.btn-editar');
            botonesEditar.forEach(boton => {
                boton.addEventListener('click', function () {
                    document.getElementById('edit_id').value = this.getAttribute('data-id');
                    document.getElementById('edit_titulo').value = this.getAttribute('data-titulo');
                    document.getElementById('edit_tipo').value = this.getAttribute('data-tipo');
                    document.getElementById('edit_poster_url').value = this.getAttribute('data-poster');
                    document.getElementById('edit_api_id').value = this.getAttribute('data-api');
                    document.getElementById('edit_descripcion').value = this.getAttribute('data-descripcion');
                });
            });

            // Pasar datos al modal de Eliminación
            const botonesEliminar = document.querySelectorAll('.btn-eliminar');
            botonesEliminar.forEach(boton => {
                boton.addEventListener('click', function () {
                    document.getElementById('delete_id').value = this.getAttribute('data-id');
                    document.getElementById('delete_titulo').textContent = this.getAttribute('data-titulo');
                });
            });
        });
    </script>

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>