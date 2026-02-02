<?php
// modules/portal/dashboard.php
// DASHBOARD DEL CONDUCTOR (VISTA MÓVIL)

// 1. SEGURIDAD: Solo conductores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
    header("Location: index.php?ruta=portal/login");
    exit();
}

$id_trans = $_SESSION['transportadora_id'];
$empresa_id = $_SESSION['empresa_id'];

// 2. DATOS FINANCIEROS DE HOY
// Calculamos cuánto ha ganado hoy (Pedidos entregados hoy * Tarifa)
$sql_hoy = "SELECT COUNT(*) as entregas, 
            (COUNT(*) * t.costo_envio_fijo) as ganado 
            FROM pedidos p 
            JOIN transportadoras t ON p.transportadora_id = t.id
            WHERE p.transportadora_id = ? 
            AND p.empresa_id = ? 
            AND p.estado_interno = 'Entregado' 
            AND DATE(p.fecha_entrega) = CURDATE()"; // Solo hoy

$stmt = $pdo->prepare($sql_hoy);
$stmt->execute([$id_trans, $empresa_id]);
$stats = $stmt->fetch();
$ganado_hoy = $stats['ganado'] ?? 0;
$entregas_hoy = $stats['entregas'] ?? 0;

// 3. OBTENER PEDIDOS PENDIENTES (La Ruta del Día)
// Corrección: Usamos JOIN para traer el nombre desde la tabla clientes
$sql_ruta = "SELECT p.id, p.numero_orden, p.total_venta, p.estado_interno,
             c.nombre as nombre_cliente, -- Traemos el nombre de la tabla clientes
             c.telefono, c.direccion, c.ciudad -- Usamos datos del cliente
             FROM pedidos p 
             INNER JOIN clientes c ON p.cliente_id = c.id
             WHERE p.transportadora_id = ? 
             AND p.empresa_id = ? 
             AND p.estado_interno IN ('Confirmado', 'En Ruta') 
             ORDER BY p.estado_interno ASC, p.id DESC";

$stmt2 = $pdo->prepare($sql_ruta);
$stmt2->execute([$id_trans, $empresa_id]);
$pedidos = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mi Ruta - Driver App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #e0e0e0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding-bottom: 80px; }
        
        /* TARJETA DE RESUMEN (HEADER) */
        .header-card { background: linear-gradient(135deg, #0d6efd, #0043a8); border-radius: 0 0 25px 25px; padding: 25px 20px 40px 20px; margin-bottom: -20px; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3); }
        .driver-name { font-size: 1.2rem; font-weight: 700; color: white; margin-bottom: 5px; }
        .money-display { font-size: 2.5rem; font-weight: 800; color: white; letter-spacing: -1px; }
        
        /* TARJETAS DE PEDIDOS */
        .order-card { background: #1a1a1a; border: 1px solid #333; border-radius: 15px; margin-bottom: 15px; overflow: hidden; position: relative; }
        .order-header { padding: 15px; border-bottom: 1px solid #2a2a2a; display: flex; justify-content: space-between; align-items: center; }
        .order-body { padding: 15px; }
        .order-footer { background: #222; padding: 10px; display: flex; gap: 10px; }
        
        /* ESTADOS */
        .badge-status { padding: 5px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .bg-en-ruta { background: rgba(13, 110, 253, 0.2); color: #5c9eff; border: 1px solid #0d6efd; }
        .bg-confirmado { background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid #ffc107; }

        /* BOTONES DE ACCIÓN */
        .btn-action { flex: 1; border-radius: 10px; padding: 12px 0; font-weight: 600; border: none; }
        .btn-waze { background: #333; color: white; }
        .btn-whatsapp { background: #25D366; color: black; }
        .btn-details { background: #0d6efd; color: white; width: 100%; border-radius: 12px; font-size: 1.1rem; padding: 15px; font-weight: bold; margin-top: 10px;}

        /* BARRA INFERIOR FIJA */
        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: #111; border-top: 1px solid #333; padding: 10px 0; display: flex; justify-content: space-around; z-index: 100; }
        .nav-item { color: #666; text-align: center; font-size: 0.8rem; text-decoration: none; }
        .nav-item.active { color: #0d6efd; }
        .nav-item i { font-size: 1.4rem; display: block; margin-bottom: 3px; }
    </style>
</head>
<body>

    <div class="header-card text-center">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="text-start">
                <small class="text-white-50">Hola,</small>
                <div class="driver-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></div>
            </div>
            <a href="index.php?ruta=portal/logic&action=logout" class="btn btn-sm btn-dark rounded-pill border-0 bg-opacity-25 bg-white"><i class="fas fa-power-off"></i></a>
        </div>
        
        <small class="text-white-50 text-uppercase fw-bold ls-1">Ganado Hoy</small>
        <div class="money-display">RD$ <?php echo number_format($ganado_hoy, 0); ?></div>
        <div class="badge bg-white text-dark rounded-pill px-3 mt-2 fw-bold">
            <i class="fas fa-check-circle me-1 text-success"></i> <?php echo $entregas_hoy; ?> Entregas
        </div>
    </div>

    <div class="container mt-4 px-3">
        <h6 class="text-muted fw-bold mb-3 ps-1">EN RUTA (<?php echo count($pedidos); ?>)</h6>

        <?php if (empty($pedidos)): ?>
            <div class="text-center py-5 opacity-50">
                <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                <p>¡Todo listo! No tienes entregas pendientes.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($pedidos as $p): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="text-white fw-bold">#<?php echo $p['numero_orden']; ?></span>
                        <br>
                        <small class="text-muted"><?php echo $p['ciudad']; ?></small>
                    </div>
                    <?php 
                        $clase_estado = ($p['estado_interno'] == 'En Ruta') ? 'bg-en-ruta' : 'bg-confirmado';
                    ?>
                    <span class="badge-status <?php echo $clase_estado; ?>">
                        <?php echo $p['estado_interno']; ?>
                    </span>
                </div>

                <div class="order-body">
                    <h5 class="text-white mb-1"><?php echo $p['nombre_cliente']; ?></h5>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-map-marker-alt me-1 text-danger"></i> 
                        <?php echo substr($p['direccion'], 0, 40) . '...'; ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded border border-secondary border-opacity-25">
                        <small class="text-muted">Cobrar al cliente:</small>
                        <span class="fw-bold text-success fs-5">RD$ <?php echo number_format($p['total_venta'], 0); ?></span>
                    </div>

                    <a href="index.php?ruta=portal/detalle&id=<?php echo $p['id']; ?>" class="btn btn-details mt-3">
                        GESTIONAR ENTREGA <i class="fas fa-chevron-right ms-2"></i>
                    </a>
                </div>

                <div class="order-footer">
                    <?php 
                        // Limpiar teléfono para WhatsApp (solo números)
                        $tel_clean = preg_replace('/[^0-9]/', '', $p['telefono']);
                    ?>
                    <a href="https://wa.me/1<?php echo $tel_clean; ?>?text=Hola, soy el delivery de El Clavito. Estoy en camino con su pedido #<?php echo $p['numero_orden']; ?>." target="_blank" class="btn btn-action btn-whatsapp">
                        <i class="fab fa-whatsapp fa-lg"></i>
                    </a>
                    
                    <a href="tel:<?php echo $tel_clean; ?>" class="btn btn-action btn-waze">
                        <i class="fas fa-phone fa-lg"></i>
                    </a>

                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($p['direccion'] . ' ' . $p['ciudad']); ?>" target="_blank" class="btn btn-action btn-waze">
                        <i class="fas fa-map-marked-alt fa-lg"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

   <div class="bottom-nav">
    <a href="#" class="nav-item active"> <i class="fas fa-box-open"></i> Ruta
    </a>
    
    <a href="index.php?ruta=portal/historial" class="nav-item"> 
        <i class="fas fa-history"></i> Historial
    </a>

    <a href="#" class="nav-item">
        <i class="fas fa-wallet"></i> Pagos
    </a>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>