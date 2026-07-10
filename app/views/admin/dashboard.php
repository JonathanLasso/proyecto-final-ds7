<?php require_once BASE_PATH . "/app/views/layouts/header.php"; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2 mt-3">
        <h2 class="mb-0 text-danger fw-bold"><i class="bi bi-shield-lock"></i> Panel de Administración</h2>
        <div>
            <a href="/streammatch/public/admin/export_json" class="btn btn-outline-dark"><i class="bi bi-filetype-json"></i> Exportar JSON</a>
            <a href="/streammatch/public/admin/export_xml" class="btn btn-outline-dark"><i class="bi bi-filetype-xml"></i> Exportar XML</a>
        </div>
    </div>

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
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Agregado</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($localContent as $item): ?>
                                <tr>
                                    <td class="ps-4"><?= $item['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($item['titulo']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($item['tipo'])) ?></span></td>
                                    <td>
                                        <?= !empty($item['creado_en']) ? date('d/m/Y', strtotime($item['creado_en'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($localContent)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No hay contenido en la base de datos.<br>Sube un archivo XML o JSON para llenar el catálogo.
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

<?php require_once BASE_PATH . "/app/views/layouts/footer.php"; ?>