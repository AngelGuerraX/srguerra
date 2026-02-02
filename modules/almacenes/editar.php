<?php
// modules/almacenes/editar.php
// FORMULARIO PARA EDITAR ALMACÉN

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresa_id = $_SESSION['empresa_id'];

// Consultar datos actuales del almacén
$stmt = $pdo->prepare("SELECT * FROM almacenes WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$almacen = $stmt->fetch();

if (!$almacen) {
    echo "<div class='alert alert-danger'>Almacén no encontrado.</div>";
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-white">Editar Almacén</h2>
            <a href="index.php?ruta=almacenes" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="card bg-dark border-secondary shadow-lg">
            <div class="card-body p-4">
                
                <form action="index.php?ruta=almacenes/logic" method="POST">
                    <input type="hidden" name="action" value="actualizar_almacen">
                    <input type="hidden" name="id" value="<?php echo $almacen['id']; ?>">
                    
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="mb-3">
                        <label class="form-label text-white small">Nombre del Almacén / Sucursal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-white"><i class="fas fa-warehouse"></i></span>
                            <input type="text" name="nombre" class="form-control bg-black text-white border-secondary" required 
                                   value="<?php echo htmlspecialchars($almacen['nombre']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small">Ubicación / Dirección</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-white"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="ubicacion" class="form-control bg-black text-white border-secondary" 
                                   value="<?php echo htmlspecialchars($almacen['ubicacion'] ?? ''); ?>" placeholder="Ej: Zona Industrial, Nave 4">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white small">Costo de Empaque (Fulfillment)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-white">RD$</span>
                            <input type="number" name="costo_empaque" class="form-control bg-black text-white border-secondary" step="0.01" 
                                   value="<?php echo $almacen['costo_empaque']; ?>">
                        </div>
                        <div class="form-text text-muted small">Costo operativo que se cobra por cada pedido despachado desde aquí.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning fw-bold py-2">
                            <i class="fas fa-save me-2"></i> GUARDAR CAMBIOS
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>