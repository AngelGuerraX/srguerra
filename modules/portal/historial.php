<?php
// modules/portal/historial.php
// HISTORIAL DE ENVÍOS Y PAGOS

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
    header("Location: index.php?ruta=portal/login");
    exit();
}

$id_trans = $_SESSION['transportadora_id'];
$empresa_id = $_SESSION['empresa_id'];

// 1. CALCULAR BALANCE GLOBAL (Deuda vs Pagos)
// A. Total Ganado (Histórico)
$sql_ganado = "SELECT COUNT(*) as total, (COUNT(*) * t.costo_envio_fijo) as dinero 
               FROM pedidos p 
               JOIN transportadoras t ON p.transportadora_id = t.id
               WHERE p.transportadora_id = ? AND p.estado_interno = 'Entregado'";
$stmt = $pdo->prepare($sql_ganado);
$stmt->execute([$id_trans]);
$ganado = $stmt->fetch();
$total_ganado = $ganado['dinero'] ?? 0;

// B. Total Pagado (Lo que tú le has dado)
$sql_pagado = "SELECT SUM(monto) as total FROM transportadoras_pagos WHERE transportadora_id = ?";
$stmt2 = $pdo->prepare($sql_pagado);
$stmt2->execute([$id_trans]);
$pagado = $stmt2->fetch();
$total_pagado = $pagado['total'] ?? 0;

$saldo_pendiente = $total_ganado - $total_pagado;

// 2. LISTA DE ÚLTIMOS ENVÍOS (50 más recientes)
$sql_envios = "SELECT p.numero_orden, p.fecha_entrega, p.estado_interno, p.total_venta, c.nombre as cliente
               FROM pedidos p
               JOIN clientes c ON p.cliente_id = c.id
               WHERE p.transportadora_id = ? AND p.estado_interno IN ('Entregado', 'Devuelto')
               ORDER BY p.fecha_entrega DESC LIMIT 50";
$stmt3 = $pdo->prepare($sql_envios);
$stmt3->execute([$id_trans]);
$envios = $stmt3->fetchAll();

// 3. LISTA DE PAGOS RECIBIDOS
$sql_pagos = "SELECT * FROM transportadoras_pagos WHERE transportadora_id = ? ORDER BY fecha DESC LIMIT 20";
$stmt4 = $pdo->prepare($sql_pagos);
$stmt4->execute([$id_trans]);
$lista_pagos = $stmt4->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mi Historial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #e0e0e0; font-family: sans-serif; padding-bottom: 80px; }
        
        /* TARJETA DE BALANCE */
        .balance-card { background: #1a1a1a; padding: 20px; border-bottom: 1px solid #333; margin-bottom: 20px; }
        .balance-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #888; }
        .balance-amount { font-size: 2rem; font-weight: 700; color: #ffc107; } /* Amarillo alerta */
        
        /* TABS */
        .nav-tabs { border-bottom: 1px solid #333; }
        .nav-link { color: #888; width: 50%; text-align: center; border: none; padding: 15px; font-weight: bold; }
        .nav-link.active { background: transparent; color: #0d6efd; border-bottom: 3px solid #0d6efd; }
        
        /* LISTAS */
        .list-group-item { background: #111; border: 1px solid #222; margin-bottom: 10px; border-radius: 10px !important; color: white; }
        
        /* BOTTOM NAV */
        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: #111; border-top: 1px solid #333; padding: 10px 0; display: flex; justify-content: space-around; z-index: 100; }
        .nav-item { color: #666; text-align: center; font-size: 0.8rem; text-decoration: none; }
        .nav-item.active { color: #0d6efd; }
        .nav-item i { font-size: 1.4rem; display: block; margin-bottom: 3px; }
    </style>
</head>
<body>

    <div class="balance-card text-center">
        <div class="balance-title">Saldo a tu Favor (Pendiente de Pago)</div>
        <div class="balance-amount">RD$ <?php echo number_format($saldo_pendiente, 2); ?></div>
        <small class="text-muted">Total ganado histórico: RD$ <?php echo number_format($total_ganado, 0); ?></small>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="envios-tab" data-bs-toggle="tab" data-bs-target="#envios" type="button">MIS ENVÍOS</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button">MIS PAGOS</button>
        </li>
    </ul>

    <div class="tab-content p-3" id="myTabContent">
        
        <div class="tab-pane fade show active" id="envios">
            <?php if(empty($envios)): ?>
                <div class="text-center text-muted mt-5">No hay historial reciente.</div>
            <?php endif; ?>

            <div class="list-group">
                <?php foreach($envios as $e): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-white">#<?php echo $e['numero_orden']; ?> - <?php echo explode(' ', $e['cliente'])[0]; ?></div>
                            <small class="text-muted">
                                <?php echo date('d/M h:i A', strtotime($e['fecha_entrega'])); ?>
                            </small>
                        </div>
                        <span class="badge <?php echo ($e['estado_interno']=='Entregado')?'bg-success':'bg-danger'; ?>">
                            <?php echo $e['estado_interno']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pagos">
            <?php if(empty($lista_pagos)): ?>
                <div class="text-center text-muted mt-5">Aún no has recibido pagos registrados.</div>
            <?php endif; ?>

            <div class="list-group">
                <?php foreach($lista_pagos as $pago): ?>
                    <div class="list-group-item border-success border-opacity-25">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 text-success fw-bold">+ RD$ <?php echo number_format($pago['monto'], 2); ?></h6>
                            <small class="text-white-50"><?php echo date('d/M/Y', strtotime($pago['fecha'])); ?></small>
                        </div>
                        <p class="mb-1 small text-white"><?php echo $pago['referencia']; ?></p>
                        <small class="text-muted fst-italic"><?php echo $pago['nota']; ?></small>
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
        <a href="#" class="nav-item text-muted" onclick="return false;">
            <i class="fas fa-user"></i> Perfil
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>