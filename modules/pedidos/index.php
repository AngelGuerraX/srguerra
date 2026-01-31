<?php
// modules/pedidos/index.php
// LISTADO CON FILTROS AVANZADOS Y SELECCIÓN PARA EXPORTAR

$empresa_id = $_SESSION['empresa_id'];

// --- 1. CAPTURAR FILTROS ---
$fecha_inicio = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$fecha_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-t');
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_trans  = isset($_GET['transporte']) ? (int)$_GET['transporte'] : 0;
$busqueda      = isset($_GET['q']) ? trim($_GET['q']) : ''; // Nuevo: Buscador texto

$sql_f_ini = $fecha_inicio . " 00:00:00";
$sql_f_fin = $fecha_fin . " 23:59:59";

// Paginación
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limite = 50;
$inicio = ($pagina - 1) * $limite;

// --- 2. CONSTRUIR CONSULTA ---
$sql = "SELECT SQL_CALC_FOUND_ROWS p.*, 
               c.nombre as cliente_nombre, 
               c.telefono as cliente_telefono,
               t.nombre as transporte_nombre,
               a.nombre as almacen_nombre
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN transportadoras t ON p.transportadora_id = t.id
        LEFT JOIN almacenes a ON p.almacen_id = a.id
        WHERE p.empresa_id = ? 
        AND p.fecha_creacion BETWEEN ? AND ?";

$params = [$empresa_id, $sql_f_ini, $sql_f_fin];

// Filtros dinámicos
if (!empty($filtro_estado)) {
    $sql .= " AND p.estado_interno = ?";
    $params[] = $filtro_estado;
}
if ($filtro_trans > 0) {
    $sql .= " AND p.transportadora_id = ?";
    $params[] = $filtro_trans;
}
// Nuevo: Filtro de Búsqueda General
if (!empty($busqueda)) {
    $sql .= " AND (c.nombre LIKE ? OR c.telefono LIKE ? OR p.numero_orden LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sql .= " ORDER BY p.fecha_creacion DESC LIMIT $inicio, $limite";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Totales Paginación
$total_pedidos = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_paginas = ceil($total_pedidos / $limite);

// Lista transportadoras para el select
$lista_trans = $pdo->query("SELECT id, nombre FROM transportadoras WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();

// Función auxiliar URL
function url_filtro($nuevo_estado = null, $nuevo_trans = null, $reset = false) {
    global $fecha_inicio, $fecha_fin, $filtro_estado, $filtro_trans, $busqueda;
    if ($reset) return "index.php?ruta=pedidos";
    $e = ($nuevo_estado === 'all') ? '' : ($nuevo_estado ?? $filtro_estado);
    $t = ($nuevo_trans === 'all') ? 0 : ($nuevo_trans ?? $filtro_trans);
    return "index.php?ruta=pedidos&f_ini=$fecha_inicio&f_fin=$fecha_fin&estado=$e&transporte=$t&q=$busqueda";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">LOGÍSTICA</span>
        <h2 class="fw-bold text-white">Gestión de Pedidos</h2>
    </div>
    <div class="d-flex gap-2">
        <button type="button" onclick="document.getElementById('formExportar').submit();" class="btn btn-success rounded-pill px-4 shadow">
            <i class="fas fa-file-excel me-2"></i> Exportar Selección
        </button>
        <a href="index.php?ruta=pedidos/nuevo" class="btn btn-primary rounded-pill px-4 btn-glow">
            <i class="fas fa-plus me-2"></i> Crear Pedido
        </a>
    </div>
</div>

<div class="card-glass p-3 mb-3">
    <form action="index.php" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="ruta" value="pedidos">
        
        <div class="col-md-2">
            <label class="small text-muted">Desde</label>
            <input type="date" name="f_ini" value="<?php echo $fecha_inicio; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
        </div>
        <div class="col-md-2">
            <label class="small text-muted">Hasta</label>
            <input type="date" name="f_fin" value="<?php echo $fecha_fin; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
        </div>

        <div class="col-md-3">
            <label class="small text-muted">Buscar (Cliente, Tel, Orden)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="q" value="<?php echo $busqueda; ?>" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Juan, 809...">
            </div>
        </div>

        <div class="col-md-2">
            <label class="small text-muted">Estado</label>
            <select name="estado" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">Todos</option>
                <option value="Nuevo" <?php echo $filtro_estado=='Nuevo'?'selected':''; ?>>Nuevo</option>
                <option value="En Ruta" <?php echo $filtro_estado=='En Ruta'?'selected':''; ?>>En Ruta</option>
                <option value="Entregado" <?php echo $filtro_estado=='Entregado'?'selected':''; ?>>Entregado</option>
                <option value="Cancelado" <?php echo $filtro_estado=='Cancelado'?'selected':''; ?>>Cancelado</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="small text-muted">Transportadora</label>
            <select name="transporte" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="0">Todas</option>
                <?php foreach($lista_trans as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo $filtro_trans==$t['id']?'selected':''; ?>><?php echo $t['nombre']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-1">
            <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
        </div>
    </form>
</div>

<form id="formExportar" action="index.php?ruta=exportar-pedidos" method="POST" target="_blank">
    <div class="card-glass p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle mb-0">
                <thead>
                    <tr class="text-uppercase small text-muted">
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="checkAll" onclick="toggleAll(this)">
                        </th>
                        <th>Orden #</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Transporte</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($pedidos) > 0): ?>
                        <?php foreach($pedidos as $p): ?>
                            <tr class="group-hover">
                                <td class="text-center">
                                    <input type="checkbox" name="pedidos[]" value="<?php echo $p['id']; ?>" class="form-check-input check-item">
                                </td>
                                <td class="fw-bold text-neon"><?php echo $p['numero_orden']; ?></td>
                                <td class="small text-muted">
                                    <?php echo date('d/m H:i', strtotime($p['fecha_creacion'])); ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?php echo $p['cliente_nombre']; ?></div>
                                    <div class="small text-muted"><?php echo $p['cliente_telefono']; ?></div>
                                </td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($p['estado_interno'] == 'Nuevo') $badge = 'bg-primary';
                                        if($p['estado_interno'] == 'En Ruta') $badge = 'bg-warning text-dark';
                                        if($p['estado_interno'] == 'Entregado') $badge = 'bg-success';
                                        if($p['estado_interno'] == 'Cancelado') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge rounded-pill <?php echo $badge; ?> px-3">
                                        <?php echo $p['estado_interno']; ?>
                                    </span>
                                </td>
                                <td class="small text-warning">
                                    <?php echo $p['transporte_nombre'] ?: '-'; ?>
                                </td>
                                <td class="text-end fw-bold text-white">
                                    RD$ <?php echo number_format($p['total_venta'], 0); ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?ruta=pedidos/ver&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-light rounded-circle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No se encontraron pedidos con estos filtros.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php if($total_paginas > 1): ?>
<div class="mt-3 text-center">
    <nav>
        <ul class="pagination justify-content-center pagination-sm">
            <?php for($i=1; $i<=$total_paginas; $i++): ?>
                <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                    <a class="page-link bg-dark text-white border-secondary" 
                       href="<?php echo url_filtro() . '&p=' . $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>

<script>
// Script para seleccionar todos los checkboxes
function toggleAll(source) {
    checkboxes = document.getElementsByClassName('check-item');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>