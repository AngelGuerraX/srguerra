<?php
// modules/portal/historial.php
// HISTORIAL DE FINALIZADOS (TIPO WALLET MULTI-EMPRESA)

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
    header("Location: index.php?ruta=portal/login");
    exit();
}

$id_trans = $_SESSION['transportadora_id'];

// --- FILTRO DE FECHAS ---
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'hoy';
$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-d');
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-d');

// 1. OBTENER SALDOS SEPARADOS POR EMPRESA (WALLET)
$sql_wallet = "
    SELECT 
        e.id as empresa_id,
        e.nombre_comercial,
        -- Calculamos lo ganado (Solo Entregados)
        (SELECT COUNT(*) * t.costo_envio_fijo 
         FROM pedidos p2 
         JOIN transportadoras t ON p2.transportadora_id = t.id
         WHERE p2.transportadora_id = ? AND p2.empresa_id = e.id AND p2.estado_interno = 'Entregado'
        ) as ganado,
        -- Calculamos lo pagado
        COALESCE((SELECT SUM(monto) 
         FROM transportadoras_pagos tp 
         WHERE tp.transportadora_id = ? AND tp.empresa_id = e.id
        ), 0) as pagado
    FROM pedidos p
    JOIN empresas e ON p.empresa_id = e.id
    WHERE p.transportadora_id = ?
    GROUP BY p.empresa_id
";

$stmt = $pdo->prepare($sql_wallet);
$stmt->execute([$id_trans, $id_trans, $id_trans]);
$cartera = $stmt->fetchAll();

// Totales Globales
$global_pendiente = 0;
foreach($cartera as $c) {
    $global_pendiente += ($c['ganado'] - $c['pagado']);
}

// 2. LISTA DE ENVÍOS (CORREGIDO: SOLO FINALIZADOS)
// Agregamos el filtro IN ('Entregado', 'Devuelto', 'Rechazado')
$sql_envios = "SELECT p.numero_orden, p.fecha_entrega, p.fecha_creacion, p.estado_interno, p.total_venta, 
               c.nombre as cliente, e.nombre_comercial as empresa_origen
               FROM pedidos p
               JOIN clientes c ON p.cliente_id = c.id
               JOIN empresas e ON p.empresa_id = e.id
               WHERE p.transportadora_id = ? 
               AND p.estado_interno IN ('Entregado', 'Devuelto', 'Rechazado') /* <--- FILTRO AGREGADO */
               AND DATE(p.fecha_creacion) BETWEEN ? AND ?
               ORDER BY p.fecha_creacion DESC";

$stmt3 = $pdo->prepare($sql_envios);
$stmt3->execute([$id_trans, $f_ini, $f_fin]);
$envios = $stmt3->fetchAll();

// 3. LISTA DE PAGOS
$sql_pagos = "SELECT tp.*, e.nombre_comercial 
              FROM transportadoras_pagos tp
              LEFT JOIN empresas e ON tp.empresa_id = e.id 
              WHERE tp.transportadora_id = ? 
              ORDER BY tp.fecha DESC LIMIT 20";
$stmt4 = $pdo->prepare($sql_pagos);
$stmt4->execute([$id_trans]);
$lista_pagos = $stmt4->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mi Billetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #e0e0e0; font-family: sans-serif; padding-bottom: 80px; }
        
        /* WALLET STYLES */
        .wallet-container { 
            display: flex; overflow-x: auto; scroll-snap-type: x mandatory; 
            gap: 15px; padding: 20px 15px; background: #111; border-bottom: 1px solid #333;
        }
        .wallet-card { 
            flex: 0 0 85%; scroll-snap-align: center; border-radius: 15px; 
            padding: 20px; position: relative; overflow: hidden; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .card-global { background: linear-gradient(135deg, #0d6efd, #0043a8); }
        .card-company { background: linear-gradient(135deg, #198754, #0f5132); }
        .card-debt { background: linear-gradient(135deg, #dc3545, #842029); } /* Opcional si deben negativo */
        .balance-amount { font-size: 2rem; font-weight: 700; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .company-label { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; opacity: 0.8; }
        
        /* UI ELEMENTS */
        .nav-tabs { border-bottom: 1px solid #333; margin-top: 10px; }
        .nav-link { color: #888; width: 50%; text-align: center; border: none; padding: 15px; font-weight: bold; }
        .nav-link.active { background: transparent; color: #ffc107; border-bottom: 3px solid #ffc107; }
        .list-group-item { background: #1a1a1a; border: 1px solid #333; margin-bottom: 10px; border-radius: 10px !important; color: white; }
        
        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: #111; border-top: 1px solid #333; padding: 10px 0; display: flex; justify-content: space-around; z-index: 100; }
        .nav-item { color: #666; text-align: center; font-size: 0.8rem; text-decoration: none; }
        .nav-item.active { color: #ffc107; }
        
        .wallet-container::-webkit-scrollbar { display: none; }
        .date-input-group.d-none { display: none !important; }
    </style>
</head>
<body>

    <div class="wallet-container">
        <div class="wallet-card card-global">
            <div class="d-flex justify-content-between">
                <span class="company-label text-white">💰 BALANCE TOTAL</span>
                <i class="fas fa-wallet text-white-50"></i>
            </div>
            <div class="mt-3">
                <div class="balance-amount">RD$ <?php echo number_format($global_pendiente, 2); ?></div>
                <small class="text-white-50">Suma total pendiente</small>
            </div>
        </div>

        <?php foreach($cartera as $c): ?>
            <?php 
                $deuda = $c['ganado'] - $c['pagado']; 
                $bg_class = ($deuda > 0) ? 'card-company' : 'bg-secondary';
            ?>
            <div class="wallet-card <?php echo $bg_class; ?>">
                <div class="d-flex justify-content-between">
                    <span class="company-label text-white">🏢 <?php echo substr($c['nombre_comercial'], 0, 15); ?></span>
                    <i class="fas fa-building text-white-50"></i>
                </div>
                <div class="mt-3">
                    <div class="balance-amount">RD$ <?php echo number_format($deuda, 2); ?></div>
                    <div class="d-flex justify-content-between text-white-50 small mt-1">
                        <span>Ganado: <?php echo number_format($c['ganado']); ?></span>
                        <span>Pagado: <?php echo number_format($c['pagado']); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="envios-tab" data-bs-toggle="tab" data-bs-target="#envios" type="button">FINALIZADOS</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button">RECIBOS</button>
        </li>
    </ul>

    <div class="tab-content p-3" id="myTabContent">
        
        <div class="tab-pane fade show active" id="envios">
            
            <form id="filterForm" action="index.php" method="GET" class="mb-3">
                <input type="hidden" name="ruta" value="portal/historial">
                <div class="mb-2">
                    <select id="periodoSelect" name="periodo" class="form-select bg-dark text-white border-secondary fw-bold text-center text-uppercase" onchange="aplicarPeriodo(this.value)">
                        <option value="hoy" <?php echo $periodo=='hoy'?'selected':''; ?>>📅 Hoy</option>
                        <option value="ayer" <?php echo $periodo=='ayer'?'selected':''; ?>>⏮️ Ayer</option>
                        <option value="mes" <?php echo $periodo=='mes'?'selected':''; ?>>📅 Este Mes</option>
                        <option value="custom" <?php echo $periodo=='custom'?'selected':''; ?>>⚙️ Personalizado...</option>
                    </select>
                </div>
                <div id="customDates" class="row g-2 <?php echo $periodo=='custom'?'':'d-none'; ?>">
                    <div class="col-6"><input type="date" id="f_ini" name="f_ini" value="<?php echo $f_ini; ?>" class="form-control form-control-sm bg-dark text-white border-secondary"></div>
                    <div class="col-6"><input type="date" id="f_fin" name="f_fin" value="<?php echo $f_fin; ?>" class="form-control form-control-sm bg-dark text-white border-secondary"></div>
                    <div class="col-12"><button class="btn btn-primary btn-sm w-100">Aplicar</button></div>
                </div>
            </form>

            <?php if(empty($envios)): ?>
                <div class="text-center text-muted mt-5">
                    <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                    <p>No hay pedidos finalizados en esta fecha.</p>
                </div>
            <?php endif; ?>

            <div class="list-group">
                <?php foreach($envios as $e): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-warning text-dark text-uppercase" style="font-size:10px;">
                                <i class="fas fa-store"></i> <?php echo $e['empresa_origen']; ?>
                            </span>
                            <small class="text-muted">
                                <?php 
                                    // Mostrar fecha de cierre (entrega o rechazo)
                                    echo ($e['fecha_entrega']) ? date('d/m H:i', strtotime($e['fecha_entrega'])) : date('d/m', strtotime($e['fecha_creacion'])); 
                                ?>
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-white">#<?php echo $e['numero_orden']; ?></div>
                                <small class="text-white-50"><?php echo explode(' ', $e['cliente'])[0]; ?></small>
                            </div>
                            <?php 
                                $badge = 'bg-secondary';
                                if($e['estado_interno'] == 'Entregado') $badge = 'bg-success';
                                if($e['estado_interno'] == 'Rechazado') $badge = 'bg-danger';
                                if($e['estado_interno'] == 'Devuelto')  $badge = 'bg-warning text-dark';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo $e['estado_interno']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="pagos">
            <?php if(empty($lista_pagos)): ?>
                <div class="text-center text-muted mt-5">No hay pagos registrados.</div>
            <?php endif; ?>

            <div class="list-group">
                <?php foreach($lista_pagos as $pago): ?>
                    <div class="list-group-item border-start border-4 border-success">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-0 text-success fw-bold">+ RD$ <?php echo number_format($pago['monto'], 2); ?></h6>
                            <small class="text-white-50"><?php echo date('d/m/y', strtotime($pago['fecha'])); ?></small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-white">
                                <i class="fas fa-building me-1 text-muted"></i> 
                                <?php echo $pago['nombre_comercial'] ?? 'Desconocido'; ?>
                            </small>
                            <span class="badge bg-dark border border-secondary text-muted" style="font-weight:normal;">
                                <?php echo $pago['referencia']; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="index.php?ruta=portal/dashboard" class="nav-item"><i class="fas fa-box-open mb-1"></i><br>Ruta</a>
        <a href="#" class="nav-item active"><i class="fas fa-wallet mb-1"></i><br>Billetera</a>
        <a href="#" class="nav-item text-muted"><i class="fas fa-user mb-1"></i><br>Perfil</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function aplicarPeriodo(val) {
            const form = document.getElementById('filterForm');
            const divCustom = document.getElementById('customDates');
            const hoy = new Date();
            const formatDate = (d) => d.toISOString().split('T')[0];

            if (val === 'custom') { divCustom.classList.remove('d-none'); return; }
            else { divCustom.classList.add('d-none'); }

            const inputIni = document.getElementById('f_ini');
            const inputFin = document.getElementById('f_fin');

            if(val === 'hoy') { inputIni.value = inputFin.value = formatDate(hoy); }
            else if(val === 'ayer') { 
                let ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
                inputIni.value = inputFin.value = formatDate(ayer); 
            }
            else if(val === 'mes') {
                inputIni.value = formatDate(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
                inputFin.value = formatDate(new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0));
            }
            form.submit();
        }
    </script>
</body>
</html>