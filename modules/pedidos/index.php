<?php
// modules/pedidos/index.php
// LISTADO OPTIMIZADO: FILTROS GLOBALES + SUMATORIA + PAGINACIÓN 50

$empresa_id = $_SESSION['empresa_id'];
$nombre_empresa_sesion = $_SESSION['empresa_nombre'] ?? 'SRGUERRA'; 

// --- 1. CAPTURAR FILTROS ---
$fecha_inicio = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$fecha_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-t');
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_trans  = isset($_GET['transporte']) ? (int)$_GET['transporte'] : 0;
$filtro_almacen = isset($_GET['almacen']) ? (int)$_GET['almacen'] : 0; 
$busqueda      = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql_f_ini = $fecha_inicio . " 00:00:00";
$sql_f_fin = $fecha_fin . " 23:59:59";

// --- CONFIGURACIÓN DE PAGINACIÓN (OPTIMIZACIÓN) ---
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limite = 50; // Mantenemos 50 para que cargue rápido
$inicio = ($pagina - 1) * $limite;

// --- 2. CONSTRUIR CONSULTA ---
$sql = "SELECT SQL_CALC_FOUND_ROWS p.*, 
               c.nombre as cliente_nombre, 
               c.telefono as cliente_telefono,
               c.direccion as cliente_direccion,
               c.ciudad as cliente_ciudad,
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
if ($filtro_almacen > 0) {
    $sql .= " AND p.almacen_id = ?";
    $params[] = $filtro_almacen;
}
if (!empty($busqueda)) {
    $sql .= " AND (c.nombre LIKE ? OR c.telefono LIKE ? OR p.numero_orden LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// Ordenar y Limitar (Clave para velocidad)
$sql .= " ORDER BY p.fecha_creacion DESC LIMIT $inicio, $limite";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Totales Paginación
$total_pedidos = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_paginas = ceil($total_pedidos / $limite);

// --- LISTAS PARA SELECTS (MODIFICADO PARA PÚBLICAS) ---
// Ahora traemos las públicas (es_publica=1) o las propias
$sql_trans_list = "SELECT id, nombre, es_publica 
                   FROM transportadoras 
                   WHERE (empresa_id = $empresa_id OR es_publica = 1) 
                   AND activo = 1 
                   ORDER BY es_publica DESC, nombre ASC";
$lista_trans = $pdo->query($sql_trans_list)->fetchAll();

$lista_almacenes = $pdo->query("SELECT id, nombre FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();

// URL Helper
function url_filtro() {
    global $fecha_inicio, $fecha_fin, $filtro_estado, $filtro_trans, $filtro_almacen, $busqueda;
    return "index.php?ruta=pedidos&f_ini=$fecha_inicio&f_fin=$fecha_fin&estado=$filtro_estado&transporte=$filtro_trans&almacen=$filtro_almacen&q=$busqueda";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">LOGÍSTICA</span>
        <h2 class="fw-bold text-white">Gestión de Pedidos</h2>
    </div>
    
    <div class="d-flex gap-2">
        <button type="button" onclick="submitAccion('eliminar')" class="btn btn-danger rounded-pill px-3 shadow">
            <i class="fas fa-trash-alt me-2"></i> Borrar
        </button>
 
        <button type="button" onclick="submitAccion('exportar')" class="btn btn-success rounded-pill px-3 shadow">
            <i class="fas fa-file-excel me-2"></i> Exportar
        </button>

        <a href="index.php?ruta=pedidos/nuevo" class="btn btn-primary rounded-pill px-4 btn-glow">
            <i class="fas fa-plus me-2"></i> Crear Pedido
        </a>
    </div>
</div>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'eliminados'): ?>
    <div class="alert alert-success border-0 bg-success text-white bg-opacity-75 fade show mb-3">
        <i class="fas fa-check-circle me-2"></i> Pedidos eliminados correctamente.
    </div>
<?php endif; ?>

<div class="card-glass p-3 mb-3">
    <form action="index.php" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="ruta" value="pedidos">
        
        <div class="col-6 col-md-2">
            <label class="small text-muted">Desde</label>
            <input type="date" name="f_ini" value="<?php echo $fecha_inicio; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
        </div>
        <div class="col-6 col-md-2">
            <label class="small text-muted">Hasta</label>
            <input type="date" name="f_fin" value="<?php echo $fecha_fin; ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
        </div>

        <div class="col-12 col-md-2">
            <label class="small text-muted">Buscar</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="q" value="<?php echo $busqueda; ?>" class="form-control bg-dark text-white border-secondary" placeholder="Cliente/Orden">
            </div>
        </div>

        <div class="col-6 col-md-2">
            <label class="small text-muted">Estado</label>
            <select name="estado" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="">Todos</option>
                <option value="Nuevo" <?php echo $filtro_estado=='Nuevo'?'selected':''; ?>>Nuevo</option>
                <option value="Confirmado" <?php echo $filtro_estado=='Confirmado'?'selected':''; ?>>Confirmado</option>
                <option value="En Ruta" <?php echo $filtro_estado=='En Ruta'?'selected':''; ?>>En Ruta</option>
                <option value="Entregado" <?php echo $filtro_estado=='Entregado'?'selected':''; ?>>Entregado</option>
                <option value="Devuelto" <?php echo $filtro_estado=='Devuelto'?'selected':''; ?>>Devuelto</option>
                <option value="Cancelado" <?php echo $filtro_estado=='Cancelado'?'selected':''; ?>>Cancelado</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="small text-muted">Transporte</label>
            <select name="transporte" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="0">Todos</option>
                <?php foreach($lista_trans as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo $filtro_trans==$t['id']?'selected':''; ?>>
                        <?php echo ($t['es_publica'] == 1) ? '🌎 ' : ''; ?>
                        <?php echo $t['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-10 col-md-1">
            <label class="small text-muted">Almacén</label>
            <select name="almacen" class="form-select form-select-sm bg-dark text-white border-secondary">
                <option value="0">Todos</option>
                <?php foreach($lista_almacenes as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php echo $filtro_almacen==$a['id']?'selected':''; ?>><?php echo $a['nombre']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-2 col-md-1">
            <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
        </div>
    </form>
</div>

<form id="formMaestro" method="POST">
    <div class="card-glass p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle mb-0">
                <thead>
                    <tr class="text-uppercase small text-muted">
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="checkAll" onclick="toggleAll(this)">
                        </th>
                        <th>Orden #</th>
                        <th>Cliente / Contacto</th>
                        <th>Destino / Almacén</th>
                        <th>Estado</th>
                        <th>Transporte</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $suma_pagina = 0; // Inicializamos acumulador
                        if(count($pedidos) > 0): 
                    ?>
                        <?php foreach($pedidos as $p): ?>
                            
                            <?php 
                                // Sumar al total
                                $suma_pagina += $p['total_venta'];

                                $num_clean = preg_replace('/[^0-9]/', '', $p['cliente_telefono']);
                                if(strlen($num_clean) == 10) $num_clean = '1'.$num_clean;
                                $mensaje_ws = "Hola " . $p['cliente_nombre'] . ", te escribimos de " . $nombre_empresa_sesion . " sobre tu pedido #" . $p['numero_orden'];
                                $link_ws = "https://wa.me/" . $num_clean . "?text=" . urlencode($mensaje_ws);
                            ?>

                            <tr class="group-hover">
                                <td class="text-center">
                                    <input type="checkbox" name="pedidos[]" value="<?php echo $p['id']; ?>" class="form-check-input check-item">
                                </td>
                                
                                <td>
                                    <div class="fw-bold text-neon"><?php echo $p['numero_orden']; ?></div>
                                    <div class="small text-muted"><?php echo date('d/m H:i', strtotime($p['fecha_creacion'])); ?></div>
                                </td>
                                
                                <td>
                                    <div class="fw-bold text-white"><?php echo $p['cliente_nombre']; ?></div>
                                    <div class="small text-muted mb-1"><?php echo $p['cliente_telefono']; ?></div>
                                    
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo $link_ws; ?>" target="_blank" class="btn btn-success" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <a href="tel:<?php echo $p['cliente_telefono']; ?>" class="btn btn-outline-light" title="Llamar">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    </div>
                                </td>

                                <td>
                                    <div class="text-info small mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo substr($p['cliente_direccion'], 0, 20); ?>...
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo $p['cliente_ciudad']; ?>
                                    </div>
                                    <div class="badge bg-dark border border-secondary text-white mt-1">
                                        <i class="fas fa-warehouse me-1 text-warning"></i> <?php echo $p['almacen_nombre']; ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($p['estado_interno'] == 'Nuevo') $badge = 'bg-primary';
                                        if($p['estado_interno'] == 'Confirmado') $badge = 'bg-info text-dark';
                                        if($p['estado_interno'] == 'En Ruta') $badge = 'bg-warning text-dark';
                                        if($p['estado_interno'] == 'Entregado') $badge = 'bg-success';
                                        if($p['estado_interno'] == 'Devuelto') $badge = 'bg-danger';
                                        if($p['estado_interno'] == 'Cancelado') $badge = 'bg-dark border border-secondary';
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
                
                <tfoot>
                    <tr class="border-top border-secondary bg-dark bg-opacity-50">
                        <td colspan="6" class="text-end text-white-50 text-uppercase small py-3 fw-bold">Total en esta página (<?php echo count($pedidos); ?>):</td>
                        <td class="text-end text-warning fw-bold fs-5 py-3">RD$ <?php echo number_format($suma_pagina, 0); ?></td>
                        <td></td>
                    </tr>
                </tfoot>

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
function toggleAll(source) {
    checkboxes = document.getElementsByClassName('check-item');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}

function submitAccion(tipo) {
    var form = document.getElementById('formMaestro');
    var checkboxes = document.querySelectorAll('.check-item:checked');
    
    if (checkboxes.length === 0) {
        alert("Primero selecciona al menos un pedido.");
        return;
    }

    if (tipo === 'exportar') {
        form.action = "index.php?ruta=exportar-pedidos";
        form.target = "_blank";
        var oldInput = document.getElementById('hiddenAction');
        if(oldInput) oldInput.remove();
        form.submit();
        
    } else if (tipo === 'eliminar') {
        if(confirm("¿Seguro que quieres borrar estos pedidos?")) {
            form.action = "index.php?ruta=pedidos/logic";
            form.target = "_self";
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'eliminar_masivo';
            input.id = 'hiddenAction';
            form.appendChild(input);
            form.submit();
        }
    }
}
</script>