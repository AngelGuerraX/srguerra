<?php
// modules/transportadoras/ver.php
// PERFIL CON FILTRO DE FECHAS + ESTADO

$id = (int)$_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// ---------------------------------------------------------
// 1. CAPTURAR FILTROS
// ---------------------------------------------------------
// Fechas
$fecha_inicio = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$fecha_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-t');

// Estado (Nuevo filtro)
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$sql_f_ini = $fecha_inicio . " 00:00:00";
$sql_f_fin = $fecha_fin . " 23:59:59";

// ---------------------------------------------------------
// 2. DATOS DE LA TRANSPORTADORA
// ---------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM transportadoras WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$t = $stmt->fetch();

if(!$t) die("Transportadora no encontrada");

// ---------------------------------------------------------
// 3. CONSULTA DE PEDIDOS FILTRADA
// ---------------------------------------------------------
$sql_pedidos = "SELECT p.*, c.nombre as cliente_nombre 
                FROM pedidos p 
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE p.transportadora_id = ? 
                AND p.empresa_id = ? 
                AND p.fecha_creacion BETWEEN ? AND ?";

$params = [$id, $empresa_id, $sql_f_ini, $sql_f_fin];

// Agregar filtro de estado si existe
if (!empty($filtro_estado)) {
    $sql_pedidos .= " AND p.estado_interno = ?";
    $params[] = $filtro_estado;
}

$sql_pedidos .= " ORDER BY p.fecha_creacion DESC";

$stmt_p = $pdo->prepare($sql_pedidos);
$stmt_p->execute($params);
$pedidos = $stmt_p->fetchAll();

// ---------------------------------------------------------
// 4. ESTADÍSTICAS (Calculadas sobre lo filtrado)
// ---------------------------------------------------------
$total_deuda = 0;
$total_envios = count($pedidos);
$total_pagar_transportadora = 0;

foreach($pedidos as $p) {
    if($p['estado_interno'] == 'En Ruta') $total_deuda += $p['total_venta'];
    $total_pagar_transportadora += $p['costo_envio_real'];
}

// Función auxiliar para generar URLs de filtro sin perder datos
function url_filtro_t($nuevo_estado = null) {
    global $id, $fecha_inicio, $fecha_fin, $filtro_estado;
    $e = ($nuevo_estado === 'all') ? '' : ($nuevo_estado ?? $filtro_estado);
    return "index.php?ruta=transportadoras/ver&id=$id&f_ini=$fecha_inicio&f_fin=$fecha_fin&estado=$e";
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php?ruta=transportadoras" class="btn btn-outline-light btn-sm me-3 rounded-circle"><i class="fas fa-arrow-left"></i></a>
    <div>
        <span class="h-label text-neon">PERFIL LOGÍSTICO</span>
        <h2 class="fw-bold text-white mb-0"><?php echo $t['nombre']; ?></h2>
    </div>
</div>

<div class="card-glass p-3 mb-3">
    <form action="index.php" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="ruta" value="transportadoras/ver">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="estado" value="<?php echo $filtro_estado; ?>"> <div class="col-md-3">
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
        <div class="col-md-4 text-end">
            <div class="btn-group btn-group-sm">
                <a href="index.php?ruta=transportadoras/ver&id=<?php echo $id; ?>&f_ini=<?php echo date('Y-m-01'); ?>&f_fin=<?php echo date('Y-m-t'); ?>" class="btn btn-outline-secondary">Este Mes</a>
                <a href="index.php?ruta=transportadoras/ver&id=<?php echo $id; ?>&f_ini=<?php echo date('Y-m-01', strtotime('last month')); ?>&f_fin=<?php echo date('Y-m-t', strtotime('last month')); ?>" class="btn btn-outline-secondary">Mes Pasado</a>
            </div>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-glass p-4 text-center">
            <small class="text-muted text-uppercase">Envíos (Selección)</small>
            <h1 class="text-white fw-bold my-2"><?php echo $total_envios; ?></h1>
            <span class="badge bg-dark border border-secondary text-muted">Costo: RD$ <?php echo number_format($total_pagar_transportadora, 0); ?></span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-4 text-center">
            <small class="text-muted text-uppercase">Dinero en Ruta (C.O.D)</small>
            <h1 class="text-warning fw-bold my-2">RD$ <?php echo number_format($total_deuda, 0); ?></h1>
            <span class="badge bg-warning text-dark">Pendiente de cobro</span>
        </div>
    </div>
</div>

<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <span class="text-white small fw-bold me-2">FILTRAR ESTADO:</span>

    <a href="<?php echo url_filtro_t('all'); ?>" 
       class="btn btn-sm <?php echo empty($filtro_estado) ? 'btn-light' : 'btn-outline-secondary'; ?> rounded-pill">Todos</a>
    
    <a href="<?php echo url_filtro_t('En Ruta'); ?>" 
       class="btn btn-sm <?php echo $filtro_estado=='En Ruta' ? 'btn-warning text-dark' : 'btn-outline-warning'; ?> rounded-pill">
       <i class="fas fa-truck me-1"></i> En Ruta
    </a>

    <a href="<?php echo url_filtro_t('Entregado'); ?>" 
       class="btn btn-sm <?php echo $filtro_estado=='Entregado' ? 'btn-success' : 'btn-outline-success'; ?> rounded-pill">
       <i class="fas fa-check me-1"></i> Entregados
    </a>

    <a href="<?php echo url_filtro_t('Devuelto'); ?>" 
       class="btn btn-sm <?php echo $filtro_estado=='Devuelto' ? 'btn-secondary' : 'btn-outline-secondary'; ?> rounded-pill">
       <i class="fas fa-undo me-1"></i> Devueltos
    </a>
</div>

<div class="card-glass p-4">
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle">
            <thead>
                <tr class="text-muted small">
                    <th>Orden</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th class="text-end">Costo Envío</th>
                    <th class="text-end">Monto (COD)</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($pedidos) > 0): ?>
                    <?php foreach($pedidos as $p): ?>
                        <tr>
                            <td class="text-neon fw-bold"><?php echo $p['numero_orden']; ?></td>
                            <td class="small text-muted"><?php echo date('d/m', strtotime($p['fecha_creacion'])); ?></td>
                            <td class="text-white"><?php echo $p['cliente_nombre']; ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($p['estado_interno'] == 'En Ruta') $badge = 'bg-warning text-dark';
                                    if($p['estado_interno'] == 'Entregado') $badge = 'bg-success';
                                    if($p['estado_interno'] == 'Cancelado') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge; ?> rounded-pill px-3"><?php echo $p['estado_interno']; ?></span>
                            </td>
                            <td class="text-end text-danger small">RD$ <?php echo number_format($p['costo_envio_real'], 0); ?></td>
                            <td class="text-end fw-bold">RD$ <?php echo number_format($p['total_venta'], 0); ?></td>
                            <td class="text-end">
                                <a href="index.php?ruta=pedidos/ver&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-info rounded-circle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron pedidos con estos filtros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>