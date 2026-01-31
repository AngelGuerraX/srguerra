<?php
// modules/almacenes/nuevo.php
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <a href="index.php?ruta=almacenes" class="btn btn-outline-light btn-sm mb-3"><i class="fas fa-arrow-left"></i> Volver</a>
        
        <div class="card-glass p-5">
            <h3 class="text-white fw-bold mb-4">Registrar Almacén</h3>
            
            <form action="index.php?ruta=almacenes/logic" method="POST">
                <input type="hidden" name="action" value="crear_almacen">
                <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                <div class="mb-3">
                    <label class="text-white small mb-1">Nombre del Almacén</label>
                    <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Almacén China, Tienda Local..." required>
                </div>

                <div class="mb-3">
                    <label class="text-white small mb-1">Ubicación (Opcional)</label>
                    <input type="text" name="ubicacion" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Santo Domingo, Beijing...">
                </div>

                <div class="mb-4">
                    <label class="text-white small mb-1">Costo de Empaque / Fulfillment</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-success">RD$</span>
                        <input type="number" name="costo_empaque" class="form-control bg-dark text-white border-secondary" placeholder="0.00" value="0">
                    </div>
                    <div class="form-text text-muted small">Costo operativo que se suma a cada pedido que salga de aquí.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> Guardar Almacén
                </button>
            </form>
        </div>
    </div>
</div>