<?php
// modules/transportadoras/nuevo.php
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <a href="index.php?ruta=transportadoras" class="btn btn-outline-light btn-sm mb-3"><i class="fas fa-arrow-left"></i> Volver</a>
        
        <div class="card-glass p-5">
            <h3 class="text-white fw-bold mb-4">Registrar Transportadora</h3>
            
            <form action="index.php?ruta=transportadoras/logic" method="POST">
                <input type="hidden" name="action" value="crear_transportadora">
                <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                <div class="mb-3">
                    <label class="text-white small mb-1">Nombre de la Empresa</label>
                    <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Metro Pac, Vimenpaq..." required>
                </div>

                <div class="mb-4">
                    <label class="text-white small mb-1">Costo Fijo de Envío / Fulfillment</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-success">RD$</span>
                        <input type="number" name="costo_envio_fijo" class="form-control bg-dark text-white border-secondary" placeholder="0.00" required>
                    </div>
                    <div class="form-text text-muted small">Este costo se asignará automáticamente a los pedidos nuevos.</div>
                </div>
<?php if($_SESSION['rol'] == 'SuperAdmin'): ?>
    <div class="card bg-warning bg-opacity-10 border-warning mb-3">
        <div class="card-body">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="es_publica" value="1" id="checkPublica">
                <label class="form-check-label fw-bold text-warning" for="checkPublica">
                    <i class="fas fa-globe me-2"></i> Transportadora Pública (Global)
                </label>
            </div>
            <small class="text-white-50">
                Si activas esto, <strong>TODAS</strong> las empresas registradas en el sistema podrán ver y asignarle pedidos a este chofer.
            </small>
        </div>
    </div>
<?php endif; ?>

<button type="submit" class="btn btn-primary w-100">Guardar Transportadora</button>
            </form>
        </div>
    </div>
</div>