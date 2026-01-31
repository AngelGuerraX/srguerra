<?php
// modules/inventario/nuevo.php 
$empresa_id = $_SESSION['empresa_id'];

// 1. OBTENER ALMACENES ACTIVOS
// Necesitamos saber qué almacenes tienes para crear los campos de stock correspondientes
$stmt = $pdo->prepare("SELECT * FROM almacenes WHERE empresa_id = ? AND activo = 1");
$stmt->execute([$empresa_id]);
$almacenes = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <a href="index.php?ruta=inventario" class="btn btn-outline-light btn-sm mb-3"><i class="fas fa-arrow-left"></i> Volver al listado</a>
        <h2 class="fw-bold text-white">Registrar Nuevo Producto</h2>
    </div>
</div>

<form action="index.php?ruta=guardar-producto" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="action" value="guardar_producto">
    <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card-glass p-4 mb-4">
                <span class="h-label text-neon mb-3">INFORMACIÓN GENERAL</span>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-white small">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Reloj T500 Negro">
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small">SKU (Código Único) *</label>
                        <input type="text" name="sku" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: REL-T500-BLK">
                        <small class="text-muted" style="font-size: 10px;">Debe ser igual al SKU de Shopify.</small>
                    </div>
                    <div class="col-12">
                        <label class="text-white small">Descripción</label>
                        <textarea name="descripcion" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Detalles del producto..."></textarea>
                    </div>
                </div>
            </div>

            <div class="card-glass p-4">
                <span class="h-label text-neon mb-3"><i class="fas fa-cubes"></i> INVENTARIO INICIAL</span>
                <p class="text-muted small">Indica cuántas unidades tienes físicamente en cada ubicación.</p>

                <div class="table-responsive">
                    <table class="table table-dark-custom align-middle">
                        <thead>
                            <tr>
                                <th>Almacén</th>
                                <th>Costo Fulfillment</th>
                                <th width="150" class="text-center">Stock Inicial</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($almacenes) > 0): ?>
                                <?php foreach ($almacenes as $alm): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-warehouse text-muted me-2"></i>
                                            <strong class="text-white"><?php echo $alm['nombre']; ?></strong>
                                        </td>
                                        <td class="text-warning">RD$ <?php echo number_format($alm['costo_empaque'], 2); ?></td>
                                        <td>
                                            <input type="number" name="stock_almacen[<?php echo $alm['id']; ?>]" class="form-control bg-black text-white text-center border-secondary" placeholder="0" min="0">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-danger">
                                        No hay almacenes creados. Ve a la base de datos y crea uno.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="card-glass p-4 mb-4">
                <span class="h-label text-neon mb-3">FINANZAS</span>
                <div class="mb-3">
                    <label class="text-white small">Precio Venta (RD$) *</label>
                    <input type="number" name="precio_venta" class="form-control bg-dark text-white border-secondary text-end fs-5 fw-bold" required placeholder="0.00" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="text-white small">Costo Compra (RD$) <span class="text-muted">(Opcional)</span></label>
                    <input type="number" name="costo_compra" class="form-control bg-dark text-white border-secondary text-end" placeholder="0.00" step="0.01">
                    <small class="text-muted">Para calcular ganancias netas.</small>
                </div>
            </div>

            <div class="card-glass p-4">
                <span class="h-label text-neon mb-3">IMAGEN DEL PRODUCTO</span>

                <div class="text-center mb-3 p-4 border border-secondary border-dashed rounded bg-black bg-opacity-25 position-relative" style="min-height: 150px;">

                    <div id="previewContainer" class="d-none mb-2">
                        <img id="imgPreview" src="" class="img-fluid rounded shadow" style="max-height: 180px;">
                    </div>

                    <div id="defaultIcon">
                        <i class="fas fa-cloud-upload-alt fs-1 text-muted mb-2"></i>
                        <p class="text-muted small">Arrastra o selecciona una foto</p>
                    </div>

                    <label for="imagenInput" class="btn btn-sm btn-outline-primary mt-2 w-100 stretched-link">Seleccionar Archivo</label>
                    <input type="file" name="imagen" id="imagenInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                </div>
            </div>

            <button type="submit" class="btn btn-glow w-100 py-3 mt-4 fw-bold rounded-pill shadow-lg">
                <i class="fas fa-save me-2"></i> GUARDAR PRODUCTO
            </button>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                // Mostrar imagen y ocultar icono
                document.getElementById('imgPreview').src = e.target.result;
                document.getElementById('previewContainer').classList.remove('d-none');
                document.getElementById('defaultIcon').classList.add('d-none');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>