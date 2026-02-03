<?php
// modules/portal/dashboard.php
// DASHBOARD DEL CONDUCTOR

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
    header("Location: index.php?ruta=portal/login");
    exit();
}

$id_trans = $_SESSION['transportadora_id'];

// A. Pendientes (En Ruta)
$sql_pend = "SELECT COUNT(*) FROM pedidos WHERE transportadora_id = ? AND estado_interno = 'En Ruta'";
$stmt = $pdo->prepare($sql_pend);
$stmt->execute([$id_trans]);
$total_pendientes = $stmt->fetchColumn();

// B. Entregados (Hoy)
$sql_ent = "SELECT COUNT(*) FROM pedidos WHERE transportadora_id = ? AND estado_interno = 'Entregado' AND DATE(fecha_entrega) = CURDATE()";
$stmt = $pdo->prepare($sql_ent);
$stmt->execute([$id_trans]);
$total_entregados = $stmt->fetchColumn();

// C. RECHAZADOS (Retornos pendientes de entrega al almacén)
$sql_rech = "SELECT COUNT(*) FROM pedidos WHERE transportadora_id = ? AND estado_interno = 'Rechazado'";
$stmt = $pdo->prepare($sql_rech);
$stmt->execute([$id_trans]);
$total_rechazados = $stmt->fetchColumn();

// LISTA DE PEDIDOS ACTIVOS
$sql_ruta = "SELECT p.*, c.nombre as nombre_cliente, c.telefono, c.direccion, c.ciudad, a.nombre as nombre_almacen
             FROM pedidos p 
             INNER JOIN clientes c ON p.cliente_id = c.id
             LEFT JOIN almacenes a ON p.almacen_id = a.id
             WHERE p.transportadora_id = ? 
             AND p.estado_interno IN ('Confirmado', 'En Ruta') 
             ORDER BY p.id DESC";
$stmt2 = $pdo->prepare($sql_ruta);
$stmt2->execute([$id_trans]);
$pedidos = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mi Ruta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #e0e0e0; font-family: sans-serif; padding-bottom: 80px; }
        .header-card { background: linear-gradient(135deg, #0d6efd, #0043a8); border-radius: 0 0 25px 25px; padding: 25px 20px 30px 20px; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3); }
        .kpi-box { background: rgba(0,0,0,0.3); border-radius: 10px; padding: 8px; text-align: center; }
        .order-card { background: #1a1a1a; border: 1px solid #333; border-radius: 15px; margin-bottom: 15px; overflow: hidden; }
        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: #111; border-top: 1px solid #333; padding: 10px 0; display: flex; justify-content: space-around; z-index: 100; }
        .nav-item { color: #666; text-decoration: none; font-size: 0.8rem; text-align: center; }
        .nav-item.active { color: #0d6efd; }
    </style>
</head>
<body>

    <div class="header-card">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="text-white fw-bold m-0">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h5>
            <a href="index.php?ruta=portal/logic&action=logout" class="text-white"><i class="fas fa-power-off"></i></a>
        </div>
        
        <div class="row g-2">
            <div class="col-4">
                <div class="kpi-box">
                    <h3 class="m-0 fw-bold text-white"><?php echo $total_pendientes; ?></h3>
                    <small class="text-white-50" style="font-size: 10px;">EN RUTA</small>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-box">
                    <h3 class="m-0 fw-bold text-success"><?php echo $total_entregados; ?></h3>
                    <small class="text-white-50" style="font-size: 10px;">ENTREGADOS</small>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-box border border-danger">
                    <h3 class="m-0 fw-bold text-danger"><?php echo $total_rechazados; ?></h3>
                    <small class="text-white-50" style="font-size: 10px;">RETORNOS</small>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <h6 class="text-muted fw-bold mb-3 ps-1">ASIGNACIONES DE HOY</h6>

        <?php if (empty($pedidos)): ?>
            <div class="text-center py-5 opacity-50">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <p>No tienes entregas pendientes.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($pedidos as $p): ?>
            <div class="order-card">
                <div class="p-3 border-bottom border-secondary">
                    <div class="d-flex justify-content-between">
                        <span class="text-white fw-bold">#<?php echo $p['numero_orden']; ?></span>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary">En Ruta</span>
                    </div>
                    <small class="text-muted"><?php echo $p['ciudad']; ?></small>
                </div>
                <div class="p-3">
                    <h5 class="text-white mb-1"><?php echo $p['nombre_cliente']; ?></h5>
                    <div class="alert alert-info py-1 px-2 d-inline-block border-0 rounded-pill mb-3" style="font-size: 11px; background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                        <i class="fas fa-warehouse me-1"></i> Sale de: <strong><?php echo $p['nombre_almacen'] ?: 'Central'; ?></strong>
                    </div>
                    <div class="d-grid">
                        <a href="index.php?ruta=portal/detalle&id=<?php echo $p['id']; ?>" class="btn btn-primary fw-bold">GESTIONAR</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bottom-nav">
        <a href="#" class="nav-item active"><i class="fas fa-box-open fa-lg mb-1"></i><br>Ruta</a>
        <a href="index.php?ruta=portal/historial" class="nav-item"><i class="fas fa-history fa-lg mb-1"></i><br>Historial</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>