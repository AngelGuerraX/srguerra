<?php
// modules/inventario/comprar.php
// REGISTRO DE ENTRADA DE MERCANCÍA (COMPRAS)

$empresa_id = $_SESSION['empresa_id'];

// Obtener datos para los selects
$almacenes = $pdo->query("SELECT * FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE empresa_id = $empresa_id ORDER BY nombre ASC")->fetchAll();
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Estilo oscuro para Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        background-color: #212529 !important;
        border-color: #6c757d !important;
        color: #fff !important;
    }
    .select2-search__field { background-color: #212529 !important; color: #fff !important; }
    .select2-results__option { background-color: #212529; color: #fff; }
    .select2-results__option--highlighted { background-color: #0d6efd !important; }
</style>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        
        <div class="d-flex align-items-center mb-4">
            <a href="index.php?ruta=inventario" class="btn btn-outline-light btn-sm me-3 rounded-circle"><i class="fas fa-arrow-left"></i></a>
            <h2 class="fw-bold text-white mb-0">Registrar Compra / Entrada</h2>
        </div>

        <div class="card bg-dark border-secondary shadow-lg">
            <div class="card-header border-secondary bg-transparent py-3">
                <p class="text-muted small m-0"><i class="fas fa-info-circle me-1"></i> Esta acción aumentará el stock y actualizará el costo del producto.</p>
            </div>
            <div class="card-body p-4">
                
                <form action="index.php?ruta=inventario/logic" method="POST">
                    <input type="hidden" name="action" value="registrar_compra">
                    <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                    <div class="mb-4">
                        <label class="text-white small mb-1">Producto a Ingresar *</label>
                        <select name="producto_id" id="select_producto" class="form-select text-white" required onchange="actualizarCostoAnterior(this)">
                            <option value="">-- Buscar Producto --</option>
                            <?php foreach ($productos as $p): ?>
                                <option class="text-white" value="<?php echo $p['id']; ?>" data-costo="<?php echo $p['costo_compra']; ?>">
                                    <?php echo $p['sku']; ?> - <?php echo $p['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="text-white small mb-1">Almacén de Destino *</label>
                        <select name="almacen_id" class="form-select bg-dark text-white border-secondary" required>
                            <?php foreach ($almacenes as $a): ?>
                                <option value="<?php echo $a['id']; ?>"><?php echo $a['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-white small mb-1">Cantidad (Unidades) *</label>
                            <input type="number" name="cantidad" class="form-control bg-black text-white border-secondary fs-5 fw-bold text-center" min="1" required placeholder="0">
                        </div>

                        <div class="col-md-6">
                            <label class="text-white small mb-1">Costo Unitario de Compra *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-secondary border-secondary text-white">RD$</span>
                                <input type="number" name="costo_unitario" id="input_costo" class="form-control bg-black text-white border-secondary fs-5 fw-bold text-end" step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="form-text text-muted small" id="info_costo_actual">Costo actual: RD$ 0.00</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-white small mb-1">Proveedor / Nota (Opcional)</label>
                        <input type="text" name="proveedor" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Importadora China, Factura #123...">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success py-3 fw-bold shadow">
                            <i class="fas fa-save me-2"></i> REGISTRAR ENTRADA
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // Inicializar Select2
    $(document).ready(function() {
        $('#select_producto').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: '-- Buscar Producto --'
        });
    });

    // Mostrar el costo actual como referencia
    function actualizarCostoAnterior(select) {
        const opcion = select.options[select.selectedIndex];
        const costo = opcion.getAttribute('data-costo') || 0;
        document.getElementById('input_costo').value = costo; // Sugerir el costo anterior
        document.getElementById('info_costo_actual').innerText = "Costo registrado anteriormente: RD$ " + parseFloat(costo).toFixed(2);
    }
</script>