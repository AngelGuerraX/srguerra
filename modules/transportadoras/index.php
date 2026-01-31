<?php
// modules/transportadoras/index.php
// LISTADO DE TRANSPORTADORAS

$empresa_id = $_SESSION['empresa_id'];

// Consultamos las transportadoras y contamos cuántos pedidos activos tienen
$sql = "SELECT t.*, 
        (SELECT COUNT(*) FROM pedidos WHERE transportadora_id = t.id) as total_pedidos
        FROM transportadoras t
        WHERE t.empresa_id = ? AND t.activo = 1
        ORDER BY t.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);
$transportadoras = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">CONFIGURACIÓN</span>
        <h2 class="fw-bold text-white">Transportadoras y Tarifas</h2>
    </div>
    <a href="index.php?ruta=transportadoras/nuevo" class="btn btn-glow rounded-pill px-4">
        <i class="fas fa-plus me-2"></i> Nueva Transportadora
    </a>
</div>

<div class="row">
    <?php foreach($transportadoras as $t): ?>
    <div class="col-md-4 mb-4">
        <div class="card-glass p-4 h-100 position-relative group-hover">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                    <i class="fas fa-shipping-fast text-primary fs-4"></i>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light border-0" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="index.php?ruta=transportadoras/editar&id=<?php echo $t['id']; ?>"><i class="fas fa-edit me-2"></i> Editar Tarifa</a></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i> Desactivar</a></li>
                    </ul>
                </div>
            </div>

            <h4 class="text-white fw-bold mb-1"><?php echo $t['nombre']; ?></h4>
            <p class="text-muted small mb-3">Tarifa Fija Configurada</p>

            <div class="d-flex justify-content-between align-items-end mt-4">
                <div>
                    <span class="d-block small text-muted">Costo Base (Fulfillment)</span>
                    <span class="text-neon fw-bold fs-4">RD$ <?php echo number_format($t['costo_envio_fijo'], 0); ?></span>
                </div>
                <div class="text-end">
                    <span class="d-block small text-muted">Pedidos Asignados</span>
                    <span class="text-white fw-bold fs-5"><?php echo $t['total_pedidos']; ?></span>
                </div>
            </div>
            
            <hr class="border-secondary opacity-25 my-3">
            
            <a href="index.php?ruta=transportadoras/ver&id=<?php echo $t['id']; ?>" class="btn btn-outline-light w-100 btn-sm rounded-pill">
                <i class="fas fa-list-alt me-2"></i> Ver Pedidos
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>