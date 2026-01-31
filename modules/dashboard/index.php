<?php
// modules/dashboard/index.php
// DASHBOARD CON FILTROS DE FECHA Y NOMBRE DE EMPRESA

$empresa_id = $_SESSION['empresa_id'];

// 1. OBTENER NOMBRE DE LA EMPRESA
$stmt_emp = $pdo->prepare("SELECT nombre_comercial FROM empresas WHERE id = ?");
$stmt_emp->execute([$empresa_id]);
$nombre_empresa = $stmt_emp->fetchColumn();

// Si por alguna razón está vacío, usamos un genérico
if (!$nombre_empresa) $nombre_empresa = "Mi Empresa";


// 2. LÓGICA DE FECHAS (Por defecto: HOY)
// Si no llegan datos por la URL, usamos la fecha de hoy.
$fecha_inicio = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-d');
$fecha_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-d');

// Formato SQL (Inicio del día 00:00 y Fin del día 23:59)
$sql_f_ini = $fecha_inicio . " 00:00:00";
$sql_f_fin = $fecha_fin . " 23:59:59";


// ---------------------------------------------------------
// 3. CONSULTAS DE KPIs (CON FILTRO DE FECHA)
// ---------------------------------------------------------

// A. VENTAS EN EL PERIODO (Solo pedidos no cancelados)
$sql_ventas = "SELECT SUM(total_venta) FROM pedidos 
               WHERE empresa_id = ? 
               AND estado_interno != 'Cancelado'
               AND fecha_creacion BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_ventas);
$stmt->execute([$empresa_id, $sql_f_ini, $sql_f_fin]);
$ventas_periodo = $stmt->fetchColumn() ?: 0;

// B. PEDIDOS CREADOS EN EL PERIODO
$sql_conteo = "SELECT COUNT(*) FROM pedidos 
               WHERE empresa_id = ? 
               AND fecha_creacion BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_conteo);
$stmt->execute([$empresa_id, $sql_f_ini, $sql_f_fin]);
$total_pedidos_periodo = $stmt->fetchColumn() ?: 0;

// C. DINERO EN RUTA (FILTRADO)
// Muestra el dinero que está en la calle DE LOS PEDIDOS CREADOS EN ESTAS FECHAS.
$sql_ruta = "SELECT SUM(total_venta) FROM pedidos 
             WHERE empresa_id = ? 
             AND estado_interno = 'En Ruta'
             AND fecha_creacion BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_ruta);
$stmt->execute([$empresa_id, $sql_f_ini, $sql_f_fin]);
$dinero_calle = $stmt->fetchColumn() ?: 0;

// D. ALERTAS DE STOCK (SIEMPRE TIEMPO REAL - NO FILTRABLE)
// El stock es el que hay "ahora", no se puede viajar al pasado fácilmente.
$sql_stock = "SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND stock_actual <= 10";
$stmt = $pdo->prepare($sql_stock);
$stmt->execute([$empresa_id]);
$alertas_stock = $stmt->fetchColumn() ?: 0;

// E. ACTIVIDAD RECIENTE (FILTRADA)
$sql_recent = "SELECT p.*, c.nombre as cliente FROM pedidos p 
               LEFT JOIN clientes c ON p.cliente_id = c.id 
               WHERE p.empresa_id = ? 
               AND p.fecha_creacion BETWEEN ? AND ?
               ORDER BY p.id DESC LIMIT 10";
$stmt = $pdo->prepare($sql_recent);
$stmt->execute([$empresa_id, $sql_f_ini, $sql_f_fin]);
$ultimos_pedidos = $stmt->fetchAll();
?>

<div class="row align-items-end mb-4" style="position: relative; z-index: 1050;">
        <div class="col-md-6">
        <span class="h-label text-neon">PANEL DE CONTROL</span>
        <h2 class="fw-bold text-white mb-0"><?php echo strtoupper($nombre_empresa); ?></h2>
    </div>
    
    <div class="col-md-6">
        <div class="card-glass p-2">
            <form action="index.php" method="GET" class="d-flex gap-2 align-items-center justify-content-end">
                <input type="hidden" name="ruta" value="dashboard">
                
                <input type="date" name="f_ini" value="<?php echo $fecha_inicio; ?>" class="form-control form-control-sm bg-dark text-white border-secondary" style="max-width: 130px;">
                <span class="text-muted">-</span>
                <input type="date" name="f_fin" value="<?php echo $fecha_fin; ?>" class="form-control form-control-sm bg-dark text-white border-secondary" style="max-width: 130px;">
                
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                
                <div class="vr bg-secondary mx-1"></div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Rápido
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php?ruta=dashboard&f_ini=<?php echo date('Y-m-d'); ?>&f_fin=<?php echo date('Y-m-d'); ?>">📅 Hoy</a></li>
                        <li><a class="dropdown-item" href="index.php?ruta=dashboard&f_ini=<?php echo date('Y-m-d', strtotime('-1 day')); ?>&f_fin=<?php echo date('Y-m-d', strtotime('-1 day')); ?>">⏪ Ayer</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?ruta=dashboard&f_ini=<?php echo date('Y-m-01'); ?>&f_fin=<?php echo date('Y-m-t'); ?>">📆 Este Mes</a></li>
                        <li><a class="dropdown-item" href="index.php?ruta=dashboard&f_ini=<?php echo date('Y-m-01', strtotime('last month')); ?>&f_fin=<?php echo date('Y-m-t', strtotime('last month')); ?>">🗓️ Mes Pasado</a></li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="d-flex align-items-center mb-3">
    <span class="badge bg-dark border border-secondary text-muted fw-normal">
        <i class="far fa-calendar-alt me-1 text-neon"></i>
        Mostrando datos del: <b class="text-white"><?php echo date('d/m/Y', strtotime($fecha_inicio)); ?></b> al <b class="text-white"><?php echo date('d/m/Y', strtotime($fecha_fin)); ?></b>
    </span>
</div>

<div class="row g-4 mb-4">
    
    <div class="col-md-3">
        <div class="card-glass p-4 h-100 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="fas fa-cash-register fa-3x text-success"></i>
            </div>
            <small class="text-muted text-uppercase fw-bold">Ventas (Periodo)</small>
            <h2 class="text-white fw-bold mb-0 mt-2">RD$ <?php echo number_format($ventas_periodo, 0); ?></h2>
            <small class="text-success">Generadas en estas fechas</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-glass p-4 h-100 position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="fas fa-clipboard-list fa-3x text-primary"></i>
            </div>
            <small class="text-muted text-uppercase fw-bold">Pedidos Nuevos</small>
            <h2 class="text-white fw-bold mb-0 mt-2"><?php echo $total_pedidos_periodo; ?></h2>
            <small class="text-primary">Creados en el rango</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-glass p-4 h-100 position-relative overflow-hidden border-start border-4 border-warning">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="fas fa-shipping-fast fa-3x text-warning"></i>
            </div>
            <small class="text-warning text-uppercase fw-bold">En Ruta (COD)</small>
            <h2 class="text-white fw-bold mb-0 mt-2">RD$ <?php echo number_format($dinero_calle, 0); ?></h2>
            <small class="text-muted">De los pedidos filtrados</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-glass p-4 h-100 position-relative overflow-hidden <?php echo $alertas_stock > 0 ? 'bg-danger bg-opacity-10' : ''; ?>">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
            </div>
            <small class="text-danger text-uppercase fw-bold">Alertas Stock</small>
            <h2 class="text-white fw-bold mb-0 mt-2"><?php echo $alertas_stock; ?></h2>
            <small class="text-danger">Estado Actual (Tiempo Real)</small>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-md-4">
        <div class="card-glass p-4 h-100">
            <h5 class="text-white fw-bold mb-3">Accesos Rápidos</h5>
            <div class="d-grid gap-3">
                <a href="index.php?ruta=pedidos/nuevo" class="btn btn-outline-light text-start p-3 hover-scale">
                    <i class="fas fa-plus-circle text-neon me-2 fs-5"></i>
                    <span class="fw-bold">Crear Nuevo Pedido</span>
                    <small class="d-block text-muted ms-4">Registrar venta manual</small>
                </a>
                <a href="index.php?ruta=inventario/nuevo" class="btn btn-outline-light text-start p-3 hover-scale">
                    <i class="fas fa-cubes text-info me-2 fs-5"></i>
                    <span class="fw-bold">Agregar Producto</span>
                    <small class="d-block text-muted ms-4">Dar entrada a mercancía</small>
                </a>
                <a href="index.php?ruta=transportadoras" class="btn btn-outline-light text-start p-3 hover-scale">
                    <i class="fas fa-truck text-warning me-2 fs-5"></i>
                    <span class="fw-bold">Ver Transportadoras</span>
                    <small class="d-block text-muted ms-4">Gestión logística</small>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-glass p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white fw-bold mb-0">Actividad en el Periodo</h5>
                <a href="index.php?ruta=pedidos&f_ini=<?php echo $fecha_inicio; ?>&f_fin=<?php echo $fecha_fin; ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                    Ver todos
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="small text-muted text-uppercase">
                            <th>Orden</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th class="text-end">Monto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($ultimos_pedidos) > 0): ?>
                            <?php foreach($ultimos_pedidos as $p): ?>
                            <tr>
                                <td class="text-neon fw-bold"><?php echo $p['numero_orden']; ?></td>
                                <td class="text-muted small"><?php echo date('d/m H:i', strtotime($p['fecha_creacion'])); ?></td>
                                <td class="text-white small"><?php echo $p['cliente']; ?></td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($p['estado_interno'] == 'Nuevo') $badge = 'bg-primary';
                                        if($p['estado_interno'] == 'En Ruta') $badge = 'bg-warning text-dark';
                                        if($p['estado_interno'] == 'Entregado') $badge = 'bg-success';
                                        if($p['estado_interno'] == 'Cancelado') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge; ?> rounded-pill" style="font-size: 10px;"><?php echo $p['estado_interno']; ?></span>
                                </td>
                                <td class="text-end fw-bold">RD$ <?php echo number_format($p['total_venta'], 0); ?></td>
                                <td class="text-end">
                                    <a href="index.php?ruta=pedidos/ver&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-light rounded-circle">
                                        <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-5">No hay actividad registrada en estas fechas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.02); background: rgba(255,255,255,0.05); }
</style>