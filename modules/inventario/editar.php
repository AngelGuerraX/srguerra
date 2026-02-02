<?php
// modules/inventario/editar.php
// FICHA DEL PRODUCTO + EDICIÓN DE STOCK MULTI-ALMACÉN + RASTREADOR

$id = (int)$_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// 1. DATOS DEL PRODUCTO
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$prod = $stmt->fetch();

if(!$prod) die("Producto no encontrado");

// 2. STOCK ACTUAL POR ALMACÉN (CORREGIDO PARA EDITAR)
// Usamos LEFT JOIN para traer TODOS los almacenes activos, aunque no tengan stock registrado (saldrá 0)
$sql_stock = "SELECT a.id as almacen_id, a.nombre, COALESCE(i.cantidad, 0) as cantidad 
              FROM almacenes a 
              LEFT JOIN inventario_almacen i ON a.id = i.almacen_id AND i.producto_id = ?
              WHERE a.empresa_id = ? AND a.activo = 1
              ORDER BY a.nombre ASC";
$stmt_s = $pdo->prepare($sql_stock);
$stmt_s->execute([$id, $empresa_id]);
$stocks = $stmt_s->fetchAll();

// ---------------------------------------------------------
// 3. RASTREADOR: FILTROS
// ---------------------------------------------------------
$fecha_inicio = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$fecha_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-t');

// Filtros Opcionales
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_trans  = isset($_GET['transporte']) ? (int)$_GET['transporte'] : 0;

$sql_f_ini = $fecha_inicio . " 00:00:00";
$sql_f_fin = $fecha_fin . " 23:59:59";

// ---------------------------------------------------------
// 4. CONSULTA DE MOVIMIENTOS (HISTORIAL)
// ---------------------------------------------------------
$sql_mov = "SELECT p.numero_orden, p.fecha_creacion, p.estado_interno, p.id as pedido_id,
                   t.nombre as trans_nombre,
                   d.cantidad as cantidad_vendida, d.precio_unitario
            FROM pedidos_detalle d
            JOIN pedidos p ON d.pedido_id = p.id
            LEFT JOIN transportadoras t ON p.transportadora_id = t.id
            WHERE d.producto_id = ? 
            AND p.empresa_id = ?
            AND p.fecha_creacion BETWEEN ? AND ?";

$params = [$id, $empresa_id, $sql_f_ini, $sql_f_fin];

// A) Filtro Estado
if (!empty($filtro_estado)) {
    $sql_mov .= " AND p.estado_interno = ?";
    $params[] = $filtro_estado;
}
// B) Filtro Transporte
if ($filtro_trans > 0) {
    $sql_mov .= " AND p.transportadora_id = ?";
    $params[] = $filtro_trans;
}

$sql_mov .= " ORDER BY p.fecha_creacion DESC";

$stmt_m = $pdo->prepare($sql_mov);
$stmt_m->execute($params);
$movimientos = $stmt_m->fetchAll();

// KPIs del Producto en este periodo
$unidades_vendidas = 0;
$unidades_en_ruta = 0;
$ingresos_generados = 0;

foreach($movimientos as $m) {
    $unidades_vendidas += $m['cantidad_vendida'];
    $ingresos_generados += ($m['cantidad_vendida'] * $m['precio_unitario']);
    
    if($m['estado_interno'] == 'En Ruta') {
        $unidades_en_ruta += $m['cantidad_vendida'];
    }
}

// Lista de transportadoras para el filtro
$lista_trans = $pdo->query("SELECT id, nombre FROM transportadoras WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();

// Función URL para mantener filtros
function url_prod($nuevo_estado=null, $nuevo_trans=null) {
    global $id, $fecha_inicio, $fecha_fin, $filtro_estado, $filtro_trans;
    $e = ($nuevo_estado === 'all') ? '' : ($nuevo_estado ?? $filtro_estado);
    $t = ($nuevo_trans === 'all') ? 0 : ($nuevo_trans ?? $filtro_trans);
    return "index.php?ruta=inventario/editar&id=$id&f_ini=$fecha_inicio&f_fin=$fecha_fin&estado=$e&transporte=$t";
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php?ruta=inventario" class="btn btn-outline-light btn-sm me-3 rounded-circle shadow"><i class="fas fa-arrow-left"></i></a>
    <div>
        <span class="h-label text-neon">FICHA DE PRODUCTO</span>
        <h2 class="fw-bold text-white mb-0"><?php echo $prod['nombre']; ?></h2>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-4">
        
        <div class="card-glass p-4 mb-4">
            <h5 class="text-white fw-bold mb-3"><i class="fas fa-edit me-2"></i> Datos Básicos</h5>
            <form action="index.php?ruta=inventario/logic" method="POST">
                <input type="hidden" name="action" value="editar_producto">
                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                <div class="mb-3">
                    <label class="small text-muted">Nombre</label>
                    <input type="text" name="nombre" value="<?php echo $prod['nombre']; ?>" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="mb-3">
                    <label class="small text-muted">SKU (Código)</label>
                    <input type="text" name="sku" value="<?php echo $prod['sku']; ?>" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Precio Venta</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-success">$</span>
                        <input type="number" name="precio_venta" value="<?php echo $prod['precio_venta']; ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>

                <button class="btn btn-primary w-100 btn-sm fw-bold">Guardar Datos</button>
            </form>
        </div>

        <div class="card-glass p-4">
            <h5 class="text-white fw-bold mb-3"><i class="fas fa-boxes me-2"></i> Inventario por Almacén</h5>
            
            <form action="index.php?ruta=inventario/logic" method="POST">
                <input type="hidden" name="action" value="actualizar_stock_almacenes">
                <input type="hidden" name="producto_id" value="<?php echo $prod['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generar_csrf_token(); ?>">

                <div class="vstack gap-2 mb-3">
                    <?php 
                    $total_calculado = 0;
                    if(empty($stocks)): ?>
                        <div class="text-center text-muted small py-2">No hay almacenes creados. <a href="index.php?ruta=almacenes/nuevo">Crea uno aquí</a>.</div>
                    <?php else:
                        foreach($stocks as $s): 
                            $total_calculado += $s['cantidad'];
                        ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2">
                            <label class="text-white small m-0" style="width: 60%;"><?php echo $s['nombre']; ?></label>
                            <input type="number" 
                                   name="cantidades[<?php echo $s['almacen_id']; ?>]" 
                                   value="<?php echo $s['cantidad']; ?>" 
                                   class="form-control form-control-sm bg-black text-neon text-end border-secondary" 
                                   style="width: 35%;"
                                   min="0">
                        </div>
                        <?php endforeach; 
                    endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pt-2">
                    <small class="text-muted">Total Global:</small>
                    <span class="text-white fw-bold fs-5"><?php echo $total_calculado; ?></span>
                </div>

                <button type="submit" class="btn btn-warning fw-bold w-100 btn-sm text-dark">
                    <i class="fas fa-sync-alt me-2"></i> Actualizar Stock
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        
        <div class="card-glass p-3 mb-3">
            <form action="index.php" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="ruta" value="inventario/editar">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="estado" value="<?php echo $filtro_estado; ?>">
                <input type="hidden" name="transporte" value="<?php echo $filtro_trans; ?>">

                <div class="col-md-4">
                    <label class="small text-muted">Desde</label>
                    <input type="date" name="f_ini" value="<?php echo $fecha_inicio; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Hasta</label>
                    <input type="date" name="f_fin" value="<?php echo $fecha_fin; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
                <div class="col-md-2">
                     <a href="index.php?ruta=inventario/editar&id=<?php echo $id; ?>" class="btn btn-sm btn-outline-light w-100">Reset</a>
                </div>
            </form>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="p-3 bg-dark border border-secondary rounded text-center">
                    <small class="text-muted d-block">Vendidos (Periodo)</small>
                    <span class="text-white fw-bold fs-5"><?php echo $unidades_vendidas; ?> u.</span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 bg-dark border border-warning rounded text-center">
                    <small class="text-warning d-block">📦 En Ruta Ahora</small>
                    <span class="text-white fw-bold fs-5"><?php echo $unidades_en_ruta; ?> u.</span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 bg-dark border border-success rounded text-center">
                    <small class="text-success d-block">Ingresos</small>
                    <span class="text-white fw-bold fs-5">$<?php echo number_format($ingresos_generados,0); ?></span>
                </div>
            </div>
        </div>

        <div class="mb-2">
            <small class="text-muted text-uppercase d-block mb-1" style="font-size: 10px;">Filtrar por Estado:</small>
            <div class="d-flex gap-2 overflow-auto pb-2">
                <a href="<?php echo url_prod('all'); ?>" class="btn btn-sm rounded-pill <?php echo empty($filtro_estado)?'btn-light':'btn-outline-secondary'; ?>">Todos</a>
                
                <a href="<?php echo url_prod('Nuevo'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_estado=='Nuevo'?'btn-primary':'btn-outline-primary'; ?>">Nuevo</a>
                
                <a href="<?php echo url_prod('Confirmado'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_estado=='Confirmado'?'btn-info text-dark':'btn-outline-info'; ?>">Confirmado</a>
                
                <a href="<?php echo url_prod('En Ruta'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_estado=='En Ruta'?'btn-warning text-dark':'btn-outline-warning'; ?>">En Ruta</a>
                
                <a href="<?php echo url_prod('Entregado'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_estado=='Entregado'?'btn-success':'btn-outline-success'; ?>">Entregado</a>
                
                <a href="<?php echo url_prod('Devuelto'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_estado=='Devuelto'?'btn-secondary':'btn-outline-secondary'; ?>">Devuelto</a>
                
                <a href="<?php echo url_prod('Cancelado'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_estado=='Cancelado'?'btn-danger':'btn-outline-danger'; ?>">Cancelado</a>
            </div>
        </div>

        <div class="mb-3">
            <small class="text-muted text-uppercase d-block mb-1" style="font-size: 10px;">Filtrar por Transportadora:</small>
            <div class="d-flex gap-2 overflow-auto pb-2">
                <a href="<?php echo url_prod(null, 'all'); ?>" class="btn btn-sm rounded-pill <?php echo $filtro_trans==0 ? 'btn-light':'btn-outline-secondary'; ?>">Todas</a>
                
                <?php foreach($lista_trans as $t): ?>
                    <a href="<?php echo url_prod(null, $t['id']); ?>" 
                       class="btn btn-sm rounded-pill text-nowrap <?php echo $filtro_trans==$t['id']?'btn-info text-dark':'btn-outline-info'; ?>">
                        <i class="fas fa-shipping-fast me-1"></i> <?php echo $t['nombre']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-glass p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="small text-muted">
                            <th>Orden</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Transportadora</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($movimientos) > 0): ?>
                            <?php foreach($movimientos as $m): ?>
                            <tr>
                                <td class="text-neon fw-bold"><?php echo $m['numero_orden']; ?></td>
                                <td class="small text-muted"><?php echo date('d/m', strtotime($m['fecha_creacion'])); ?></td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($m['estado_interno']=='Nuevo') $badge = 'bg-primary';
                                        if($m['estado_interno']=='Confirmado') $badge = 'bg-info text-dark';
                                        if($m['estado_interno']=='En Ruta') $badge = 'bg-warning text-dark';
                                        if($m['estado_interno']=='Entregado') $badge = 'bg-success';
                                        if($m['estado_interno']=='Cancelado') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge; ?> rounded-pill" style="font-size: 10px;"><?php echo $m['estado_interno']; ?></span>
                                </td>
                                <td class="small text-white">
                                    <i class="fas fa-shipping-fast me-1 text-muted"></i>
                                    <?php echo $m['trans_nombre'] ?: 'Sin Asignar'; ?>
                                </td>
                                <td class="text-center fw-bold text-white fs-5"><?php echo $m['cantidad_vendida']; ?></td>
                                <td class="text-end">
                                    <a href="index.php?ruta=pedidos/ver&id=<?php echo $m['pedido_id']; ?>" class="btn btn-sm btn-outline-light rounded-circle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No hay movimientos de este producto con estos filtros.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>