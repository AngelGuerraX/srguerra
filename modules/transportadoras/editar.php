<?php
// modules/transportadoras/editar.php
// FORMULARIO DE EDICIÓN

// 1. Verificar ID y Seguridad
if (!isset($_GET['id'])) {
    echo "<script>window.location='index.php?ruta=transportadoras';</script>";
    exit();
}

$id_trans = $_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// 2. Buscar datos actuales
$stmt = $pdo->prepare("SELECT * FROM transportadoras WHERE id = ? AND empresa_id = ? LIMIT 1");
$stmt->execute([$id_trans, $empresa_id]);
$t = $stmt->fetch();

if (!$t) {
    die("<div class='alert alert-danger'>Error: Transportadora no encontrada o no tienes permiso.</div>");
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">CONFIGURACIÓN</span>
        <h2 class="fw-bold text-white">Editar Transportadora</h2>
    </div>
    <a href="index.php?ruta=transportadoras" class="btn btn-outline-light rounded-pill px-4">
        <i class="fas fa-arrow-left me-2"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-glass p-5">
            
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-25 p-3 rounded-circle d-inline-block mb-3">
                    <i class="fas fa-shipping-fast text-primary fs-2"></i>
                </div>
                <h4 class="text-white fw-bold">Actualizar Datos</h4>
                <p class="text-muted small">Modifica el nombre o la tarifa base de envío.</p>
            </div>
<form action="index.php?ruta=transportadoras/logic" method="POST" autocomplete="off">
    <input type="hidden" name="action" value="editar_transportadora">
    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">

    <h6 class="text-muted mb-3 text-uppercase small ls-1">Datos Generales</h6>
    
    <div class="mb-3">
        <label class="form-label text-info small fw-bold">NOMBRE EMPRESA</label>
        <div class="input-group">
            <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-truck"></i></span>
            <input type="text" name="nombre" value="<?php echo $t['nombre']; ?>" class="form-control bg-dark text-white border-secondary" required>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label text-neon small fw-bold">TARIFA DE ENVÍO (RD$)</label>
        <div class="input-group">
            <span class="input-group-text bg-dark border-secondary text-muted">RD$</span>
            <input type="number" step="0.01" name="costo_envio_fijo" value="<?php echo $t['costo_envio_fijo']; ?>" class="form-control bg-dark text-white border-secondary fw-bold text-success" required>
        </div>
    </div>

    <hr class="border-secondary opacity-25 my-4">

    <h6 class="text-muted mb-3 text-uppercase small ls-1"><i class="fas fa-mobile-alt me-2"></i>Acceso App Conductor</h6>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label text-white small">CÓDIGO DE RUTA (Usuario)</label>
            <input type="text" name="codigo_acceso" value="<?php echo $t['codigo_acceso']; ?>" class="form-control bg-dark text-warning border-secondary fw-bold" placeholder="Ej: METRO-01" style="text-transform: uppercase;">
            <div class="form-text text-muted small">El chofer usará esto para entrar.</div>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label text-white small">NUEVO PIN (4 Digitos)</label>
            <input type="text" name="pin_acceso" class="form-control bg-dark text-white border-secondary" placeholder="Dejar vacío para no cambiar" maxlength="6">
            <div class="form-text text-muted small">Solo escribe si quieres cambiar la clave actual.</div>
        </div>
    </div>

    <div class="mb-4 mt-3">
        <div class="form-check form-switch">
            <input class="form-check-input bg-secondary border-0" type="checkbox" name="activo" value="1" id="switchActivo" <?php echo ($t['activo'] == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label text-white ms-2" for="switchActivo">Cuenta Activa (Permitir Acceso)</label>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-glow rounded-pill py-2 fw-bold">
            <i class="fas fa-save me-2"></i> Guardar Credenciales
        </button>
    </div>
</form>
        </div>
    </div>
</div>