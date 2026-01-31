<?php
// modules/clientes/editar.php
$id = (int)$_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$c = $stmt->fetch();

if(!$c) die("Cliente no encontrado");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <a href="index.php?ruta=clientes/ver&id=<?php echo $id; ?>" class="btn btn-outline-light btn-sm mb-3"><i class="fas fa-arrow-left"></i> Volver</a>
        
        <div class="card-glass p-5">
            <h3 class="text-white fw-bold mb-4">Editar Cliente</h3>
            
            <form action="index.php?ruta=clientes/logic" method="POST">
                <input type="hidden" name="action" value="editar_cliente">
                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                <div class="mb-3">
                    <label class="text-white small mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" value="<?php echo $c['nombre']; ?>" class="form-control bg-dark text-white border-secondary" required>
                </div>

                <div class="mb-3">
                    <label class="text-white small mb-1">Teléfono / WhatsApp</label>
                    <input type="text" name="telefono" value="<?php echo $c['telefono']; ?>" class="form-control bg-dark text-white border-secondary" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-white small mb-1">Provincia</label>
                        <input type="text" name="provincia" value="<?php echo $c['provincia']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6">
                        <label class="text-white small mb-1">Ciudad / Sector</label>
                        <input type="text" name="ciudad" value="<?php echo $c['ciudad']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-white small mb-1">Dirección Detallada</label>
                    <textarea name="direccion" class="form-control bg-dark text-white border-secondary" rows="3"><?php echo $c['direccion']; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</div>