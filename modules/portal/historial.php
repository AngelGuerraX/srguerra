<?php
// modules/portal/historial.php
// VERSIÓN CORREGIDA: El número grande responde al filtro

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
    header("Location: index.php?ruta=portal/login");
    exit();
}

$id_trans = $_SESSION['transportadora_id'];

// 1. FILTRO DE FECHAS
$fecha_inicio = $_GET['desde'] ?? date('Y-m-01'); // Por defecto 1ro de mes
$fecha_fin    = $_GET['hasta'] ?? date('Y-m-t');  // Por defecto fin de mes

// 2. CONSULTA FILTRADA (Para el número grande)
$sql_envios = "SELECT p.numero_orden, p.fecha_entrega, p.estado_interno, p.total_venta, 
               c.nombre as cliente, t.costo_envio_fijo
               FROM pedidos p
               JOIN clientes c ON p.cliente_id = c.id
               JOIN transportadoras t ON p.transportadora_id = t.id
               WHERE p.transportadora_id = ? 
               AND p.estado_interno IN ('Entregado', 'Devuelto')
               AND DATE(p.fecha_entrega) BETWEEN ? AND ?
               ORDER BY p.fecha_entrega DESC";

$stmt = $pdo->prepare($sql_envios);
$stmt->execute([$id_trans, $fecha_inicio, $fecha_fin]);
$envios_filtrados = $stmt->fetchAll();

// Calcular GANANCIA DEL PERIODO (Suma de lo filtrado)
$ganado_filtrado = 0;
foreach($envios_filtrados as $e) {
    if($e['estado_interno'] == 'Entregado') {
        $ganado_filtrado += $e['costo_envio_fijo'];
    }
}

// 3. CONSULTA DE PAGOS FILTRADOS
$sql_pagos = "SELECT * FROM transportadoras_pagos 
              WHERE transportadora_id = ? 
              AND DATE(fecha) BETWEEN ? AND ?
              ORDER BY fecha DESC";
$stmt2 = $pdo->prepare($sql_pagos);
$stmt2->execute([$id_trans, $fecha_inicio, $fecha_fin]);
$pagos_filtrados = $stmt2->fetchAll();

// Sumar pagos del periodo
$pagado_filtrado = 0;
foreach($pagos_filtrados as $p) $pagado_filtrado += $p['monto'];

// 4. DEUDA TOTAL REAL (Dato informativo secundario)
// Esto es lo que se te debe GLOBALMENTE, sin importar el filtro
$sql_global = "SELECT 
    (SELECT COUNT(*) * costo_envio_fijo FROM pedidos p JOIN transportadoras t ON p.transportadora_id = t.id WHERE p.transportadora_id = ? AND p.estado_interno = 'Entregado') as ganado_total,
    (SELECT SUM(monto) FROM transportadoras_pagos WHERE transportadora_id = ?) as pagado_total";
$stmt3 = $pdo->prepare($sql_global);
$stmt3->execute([$id_trans, $id_trans]);
$global = $stmt3->fetch();
$deuda_global = ($global['ganado_total'] ?? 0) - ($global['pagado_total'] ?? 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Historial Filtrado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #e0e0e0; font-family: sans-serif; padding-bottom: 80px; }
        
        .filter-section { background: #1a1a1a; padding: 15px; border-bottom: 1px solid #333; }
        .form-control-dark { background: #333; border: 1px solid #444; color: white; font-size: 0.9rem; }
        
        /* TARJETA PRINCIPAL (RESULTADO DEL FILTRO) */
        .result-card { background: linear-gradient(180deg, #1a1a1a 0%, #000 100%); padding: 20px; text-align: center; border-bottom: 1px solid #333; }
        .result-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #888; }
        .result-amount { font-size: 2.2rem; font-weight: 800; color: #0d6efd; margin: 5px 0; }
        
        /* BARRA DE DEUDA GLOBAL (SECUNDARIA) */
        .global-debt-bar { background: #222; padding: 10px; text-align: center; font-size: 0.9rem; border-bottom: 1px solid #333; }
        
        /* TABS Y LISTAS */
        .nav-tabs { border-bottom: 1px solid #333; }
        .nav-link { color: #666; width: 50%; text-align: center; border: none; padding: 12px; font-weight: bold; }
        .nav-link.active { background: transparent; color: #fff; border-bottom: 3px solid #0d6efd; }
        
        .list-group-item { background: #111; border: 1px solid #222; margin-bottom: 8px; border-radius: 8px !important; color: white; }
        
        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: #111; border-top: 1px solid #333; padding: 10px 0; display: flex; justify-content: space-around; z-index: 100; }
        .nav-item { color: #666; text-align: center; font-size: 0.8rem; text-decoration: none; }
        .nav-item.active { color: #0d6efd; }
        .nav-item i { font-size: 1.4rem; display: block; margin-bottom: 3px; }
    </style>
</head>
<body>

    <div class="filter-section">
        <form action="index.php" method="GET" class="row g-2">
            <input type="hidden" name="ruta" value="portal/historial">
            <div class="col-5">
                <input type="date" name="desde" value="<?php echo $fecha_inicio; ?>" class="form-control form-control-dark">
            </div>
            <div class="col-5">
                <input type="date" name="hasta" value="<?php echo $fecha_fin; ?>" class="form-control form-control-dark">
            </div>
            <div class="col-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>

    <div class="result-card">
        <div class="result-title">Ganado en este periodo</div>
        <div class="result-amount">RD$ <?php echo number_format($ganado_filtrado, 0); ?></div>
        <small class="text-muted">
            Del <?php echo date('d/m', strtotime($fecha_inicio)); ?> 
            al <?php echo date('d/m', strtotime($fecha_fin)); ?>
        </small>
    </div>

    <div class="global-debt-bar">
        <span class="text-muted me-2">Saldo Global Pendiente:</span>
        <span class="fw-bold text-warning">RD$ <?php echo number_format($deuda_global, 0); ?></span>
    </div>

    <ul class="nav nav-tabs mt-2" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="envios-tab" data-bs-toggle="tab" data-bs-target="#envios" type="button">
                ENVÍOS (<?php echo count($envios_filtrados); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button">
                PAGOS (<?php echo count($pagos_filtrados); ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content p-3" id="myTabContent">
        <div class="tab-pane fade show active" id="envios">
            <?php if(empty($envios_filtrados)): ?>
                <div class="text-center text-muted mt-4 opacity-50">Sin envíos en este rango.</div>
            <?php endif; ?>
            
            <div class="list-group">
                <?php foreach($envios_filtrados as $e): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">#<?php echo $e['numero_orden']; ?></div>
                            <small class="text-muted"><?php echo date('d/m h:i A', strtotime($e['fecha_entrega'])); ?></small>
                        </div>
                        <div class="text-end">
                            <?php if($e['estado_interno'] == 'Entregado'): ?>
                                <span class="text-success fw-bold">+ RD$ <?php echo number_format($e['costo_envio_fijo'], 0); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Devuelto</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pagos">
            <?php if(empty($pagos_filtrados)): ?>
                <div class="text-center text-muted mt-4 opacity-50">Sin pagos en este rango.</div>
            <?php endif; ?>

            <div class="list-group">
                <?php foreach($pagos_filtrados as $p): ?>
                    <div class="list-group-item border-start border-3 border-success">
                        <div class="d-flex justify-content-between">
                            <span class="text-success fw-bold">Recibido</span>
                            <span class="text-white fw-bold">RD$ <?php echo number_format($p['monto'], 0); ?></span>
                        </div>
                        <small class="text-muted"><?php echo $p['referencia']; ?> - <?php echo date('d/m/Y', strtotime($p['fecha'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="index.php?ruta=portal/dashboard" class="nav-item">
            <i class="fas fa-box-open"></i> Ruta
        </a>
        <a href="#" class="nav-item active">
            <i class="fas fa-history"></i> Historial
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>