<?php
// modules/almacenes/ver.php
// ALMACÉN CON UBICACIÓN FÍSICA DETALLADA

$id = (int)$_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// 1. DATOS DEL ALMACÉN
$stmt = $pdo->prepare("SELECT * FROM almacenes WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$almacen = $stmt->fetch();

if(!$almacen) die("Almacén no encontrado");

// 2. FILTROS (Historial)
$fecha_inicio = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$fecha_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-t');
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_trans  = isset($_GET['transporte']) ? (int)$_GET['transporte'] : 0;

$sql_f_ini = $fecha_inicio . " 00:00:00";
$sql_f_fin = $fecha_fin . " 23:59:59";

// ---------------------------------------------------------
// 3. CONSULTA INVENTARIO (AHORA CON UBICACIÓN FÍSICA)
// ---------------------------------------------------------
$sql_stock = "SELECT i.cantidad, i.ubicacion_fisica, 
                     p.nombre, p.sku, p.imagen, p.precio_venta, p.id as prod_id
              FROM inventario_almacen i
              JOIN productos p ON i.producto_id = p.id
              WHERE i.almacen_id = ? AND i.cantidad > 0
              ORDER BY i.ubicacion_fisica ASC, p.nombre ASC"; // Ordenado por ubicación para facilitar el picking

$stmt_s = $pdo->prepare($sql_stock);
$stmt_s->execute([$id]);
$stock_actual = $stmt_s->fetchAll();

// Valor del inventario
$valor_stock = 0;
foreach($stock_actual as $p) {
    $valor_stock += ($p['cantidad'] * $p['precio_venta']);
}

// ---------------------------------------------------------
// 4. CONSULTA HISTORIAL PEDIDOS
// ---------------------------------------------------------
$sql_pedidos = "SELECT p.*, c.nombre as cliente_nombre, t.nombre as trans_nombre
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN transportadoras t ON p.transportadora_id = t.id
                WHERE p.almacen_id = ? 
                AND p.empresa_id = ?
                AND p.fecha_creacion BETWEEN ? AND ?";

$params = [$id, $empresa_id, $sql_f_ini, $sql_f_fin];

if (!empty($filtro_estado)) {
    $sql_pedidos .= " AND p.estado_interno = ?";
    $params[] = $filtro_estado;
}
if ($filtro_trans > 0) {
    $sql_pedidos .= " AND p.transportadora_id = ?";
    $params[] = $filtro_trans;
}

$sql_pedidos .= " ORDER BY p.fecha_creacion DESC";

$stmt_p = $pdo->prepare($sql_pedidos);
$stmt_p->execute($params);
$pedidos = $stmt_p->fetchAll();

// KPIs Historial
$total_ventas_periodo = 0;
$total_pedidos_periodo = count($pedidos);
foreach($pedidos as $ped) {
    $total_ventas_periodo += $ped['total_venta'];
}
$lista_trans = $pdo->query("SELECT id, nombre FROM transportadoras WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();

function url_alm($nuevo_estado=null, $nuevo_trans=null) {
    global $id, $fecha_inicio, $fecha_fin, $filtro_estado, $filtro_trans;
    $e = ($nuevo_estado === 'all') ? '' : ($nuevo_estado ?? $filtro_estado);
    $t = ($nuevo_trans === 'all') ? 0 : ($nuevo_trans ?? $filtro_trans);
    return "index.php?ruta=almacenes/ver&id=$id&f_ini=$fecha_inicio&f_fin=$fecha_fin&estado=$e&transporte=$t";
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php?ruta=almacenes" class="btn btn-outline-light btn-sm me-3 rounded-circle shadow"><i class="fas fa-arrow-left"></i></a>
    <div>
        <span class="h-label text-neon">CENTRO DE DISTRIBUCIÓN</span>
        <h2 class="fw-bold text-white mb-0"><?php echo $almacen['nombre']; ?></h2>
        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo !empty($almacen['ubicacion']) ? $almacen['ubicacion'] : 'Sin ubicación'; ?></small>
    </div>
</div>

<div class="card-glass p-3 mb-4">
    <form action="index.php" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="ruta" value="almacenes/ver">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="estado" value="<?php echo $filtro_estado; ?>">
        <input type="hidden" name="transporte" value="<?php echo $filtro_trans; ?>">

        <div class="col-md-3">
            <label class="small text-muted">Desde</label>
            <input type="date" name="f_ini" value="<?php echo $fecha_inicio; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
        </div>
        <div class="col-md-3">
            <label class="small text-muted">Hasta</label>
            <input type="date" name="f_fin" value="<?php echo $fecha_fin; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i> Filtrar</button>
        </div>
        <div class="col-md-2">
             <a href="index.php?ruta=almacenes/ver&id=<?php echo $id; ?>" class="btn btn-sm btn-outline-light w-100">Reset</a>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-glass p-3 border-start border-4 border-info">
            <small class="text-info text-uppercase fw-bold">Inventario Físico</small>
            <h3 class="text-white fw-bold mb-0 mt-2">RD$ <?php echo number_format($valor_stock, 0); ?></h3>
            <small class="text-muted"><?php echo count($stock_actual); ?> Referencias</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-3 border-start border-4 border-success">
            <small class="text-success text-uppercase fw-bold">Ventas (Periodo)</small>
            <h3 class="text-white fw-bold mb-0 mt-2">RD$ <?php echo number_format($total_ventas_periodo, 0); ?></h3>
            <small class="text-muted"><?php echo $total_pedidos_periodo; ?> Pedidos</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-3 border-start border-4 border-warning">
            <small class="text-warning text-uppercase fw-bold">Costo Fulfillment</small>
            <h3 class="text-white fw-bold mb-0 mt-2">RD$ <?php echo number_format($almacen['costo_empaque'], 0); ?></h3>
            <small class="text-muted">Por pedido</small>
        </div>
    </div>
</div>

<hr class="border-secondary opacity-25 my-4">

<h5 class="text-white fw-bold mb-3"><i class="fas fa-boxes me-2"></i> Inventario Actual</h5>
<div class="card-glass p-0 overflow-hidden mb-5">
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-dark-custom align-middle mb-0">
            <thead class="sticky-top bg-dark">
                <tr class="small text-muted text-uppercase">
                    <th style="width: 50px;">Img</th>
                    <th>Producto</th>
                    <th>Ubicación Física</th> <th class="text-end">Precio</th>
                    <th class="text-center">Stock</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($stock_actual) > 0): ?>
                    <?php foreach($stock_actual as $p): ?>
                        <tr>
                            <td>
                                <div style="width: 35px; height: 35px;" class="rounded bg-black d-flex align-items-center justify-content-center overflow-hidden">
                                    <?php if($p['imagen']): ?>
                                        <img src="uploads/productos/<?php echo $p['imagen']; ?>" class="w-100 h-100" style="object-fit:cover;">
                                    <?php else: ?>
                                        <i class="fas fa-box text-muted small"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-white small fw-bold"><?php echo $p['nombre']; ?></div>
                                <small class="text-muted" style="font-size:10px;"><?php echo $p['sku']; ?></small>
                            </td>
                            
                            <td>
                                <?php if(!empty($p['ubicacion_fisica'])): ?>
                                    <span class="badge bg-dark border border-secondary text-warning">
                                        <i class="fas fa-map-pin me-1"></i> <?php echo $p['ubicacion_fisica']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small opacity-50">Sin asignar</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end text-muted small">RD$ <?php echo number_format($p['precio_venta'], 0); ?></td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-25 text-info rounded-pill px-3"><?php echo $p['cantidad']; ?></span>
                            </td>
                            <td class="text-end">
                                <a href="index.php?ruta=inventario/editar&id=<?php echo $p['prod_id']; ?>" class="btn btn-sm btn-outline-secondary rounded-circle">
                                    <i class="fas fa-pen" style="font-size:10px;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-3 text-muted small">Almacén vacío.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<h5 class="text-white fw-bold mb-3"><i class="fas fa-history me-2"></i> Historial de Salidas</h5>
<div class="mb-3 d-flex flex-wrap gap-2">
    <div class="btn-group btn-group-sm">
        <a href="<?php echo url_alm('all'); ?>" class="btn <?php echo empty($filtro_estado)?'btn-light':'btn-outline-secondary'; ?>">Todos</a>
        <a href="<?php echo url_alm('En Ruta'); ?>" class="btn <?php echo $filtro_estado=='En Ruta'?'btn-warning text-dark':'btn-outline-warning'; ?>">En Ruta</a>
        <a href="<?php echo url_alm('Entregado'); ?>" class="btn <?php echo $filtro_estado=='Entregado'?'btn-success':'btn-outline-success'; ?>">Entregados</a>
    </div>
    <div class="d-flex gap-1 overflow-auto">
        <a href="<?php echo url_alm(null, 'all'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_trans==0?'btn-light':'btn-outline-secondary'; ?>">Todas</a>
        <?php foreach($lista_trans as $t): ?>
            <a href="<?php echo url_alm(null, $t['id']); ?>" class="btn btn-sm rounded-pill text-nowrap <?php echo $filtro_trans==$t['id']?'btn-info text-dark':'btn-outline-info'; ?>">
                <?php echo $t['nombre']; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card-glass p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle mb-0">
            <thead>
                <tr class="small text-muted text-uppercase">
                    <th>Orden</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Transporte</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Ver</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($pedidos) > 0): ?>
                    <?php foreach($pedidos as $p): ?>
                        <tr>
                            <td class="text-neon fw-bold"><?php echo $p['numero_orden']; ?></td>
                            <td class="small text-muted"><?php echo date('d/m H:i', strtotime($p['fecha_creacion'])); ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($p['estado_interno']=='Nuevo') $badge = 'bg-primary';
                                    if($p['estado_interno']=='En Ruta') $badge = 'bg-warning text-dark';
                                    if($p['estado_interno']=='Entregado') $badge = 'bg-success';
                                    if($p['estado_interno']=='Cancelado') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge; ?> rounded-pill" style="font-size: 10px;"><?php echo $p['estado_interno']; ?></span>
                            </td>
                            <td class="small text-muted"><?php echo $p['trans_nombre'] ?: '-'; ?></td>
                            <td class="text-end fw-bold text-white">RD$ <?php echo number_format($p['total_venta'], 0); ?></td>
                            <td class="text-end">
                                <a href="index.php?ruta=pedidos/ver&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-light rounded-circle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No hay pedidos con estos filtros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>