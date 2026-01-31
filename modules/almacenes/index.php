<?php
// modules/almacenes/index.php
// LISTADO DE ALMACENES (CORREGIDO)

$empresa_id = $_SESSION['empresa_id'];

// Consultamos almacenes y sumamos el stock total
$sql = "SELECT a.*, 
        (SELECT COUNT(*) FROM inventario_almacen WHERE almacen_id = a.id AND cantidad > 0) as total_referencias,
        (SELECT SUM(cantidad) FROM inventario_almacen WHERE almacen_id = a.id) as total_unidades
        FROM almacenes a
        WHERE a.empresa_id = ? AND a.activo = 1
        ORDER BY a.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);
$almacenes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">LOGÍSTICA</span>
        <h2 class="fw-bold text-white">Almacenes y Sucursales</h2>
    </div>
    <a href="index.php?ruta=almacenes/nuevo" class="btn btn-glow rounded-pill px-4">
        <i class="fas fa-plus me-2"></i> Nuevo Almacén
    </a>
</div>

<div class="row g-4">
    <?php if(count($almacenes) > 0): ?>
        <?php foreach($almacenes as $a): ?>
        <div class="col-md-4">
            <div class="card-glass h-100 p-4 position-relative group-hover border-secondary">
                
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                        <i class="fas fa-warehouse text-info fs-4"></i>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light border-0" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="index.php?ruta=almacenes/ver&id=<?php echo $a['id']; ?>"><i class="fas fa-eye me-2"></i> Ver Inventario</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i> Desactivar</a></li>
                        </ul>
                    </div>
                </div>

                <h4 class="text-white fw-bold mb-1"><?php echo $a['nombre']; ?></h4>
                
                <p class="text-muted small mb-3">
                    <i class="fas fa-map-marker-alt me-1"></i> 
                    <?php echo !empty($a['ubicacion']) ? $a['ubicacion'] : 'Sin ubicación registrada'; ?>
                </p>

                <div class="p-3 bg-black bg-opacity-25 rounded border border-secondary mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small text-muted">Referencias:</span>
                        <span class="text-white fw-bold"><?php echo $a['total_referencias']; ?> prods</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Stock Total:</span>
                        <span class="text-neon fw-bold"><?php echo number_format($a['total_unidades'] ?: 0); ?> u.</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto border-top border-secondary pt-3">
                    <small class="text-muted" style="font-size: 10px;">
                        FULFILLMENT: <b class="text-white">RD$ <?php echo number_format($a['costo_empaque'], 0); ?></b>
                    </small>
                    <a href="index.php?ruta=almacenes/ver&id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        Ver Detalles <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-warehouse fs-1 text-muted opacity-25 mb-3"></i>
            <h4 class="text-white">No tienes almacenes creados</h4>
            <p class="text-muted">Crea el primero para organizar tu inventario.</p>
        </div>
    <?php endif; ?>
</div>