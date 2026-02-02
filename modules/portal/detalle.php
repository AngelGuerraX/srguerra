<?php
// modules/portal/detalle.php
// VISTA DETALLADA PARA ACCIONAR EL PEDIDO

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
    header("Location: index.php?ruta=portal/login");
    exit();
}

$pedido_id = $_GET['id'] ?? 0;
$id_trans = $_SESSION['transportadora_id'];

// Consultar datos del pedido
$sql = "SELECT p.*, 
        c.nombre as nombre_cliente, c.telefono, c.direccion, c.ciudad
        FROM pedidos p 
        INNER JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ? AND p.transportadora_id = ? LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido_id, $id_trans]);
$p = $stmt->fetch();

if (!$p) {
    echo "<script>alert('Pedido no encontrado o no asignado a ti.'); window.location='index.php?ruta=portal/dashboard';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gestionar #<?php echo $p['numero_orden']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #fff; font-family: sans-serif; padding-bottom: 20px; }
        .top-bar { padding: 15px; background: #111; border-bottom: 1px solid #222; display: flex; align-items: center; }
        .btn-back { color: white; font-size: 1.2rem; text-decoration: none; margin-right: 15px; }
        
        .info-card { background: #1a1a1a; padding: 20px; border-radius: 15px; margin: 20px; border: 1px solid #333; }
        .label-text { color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
        .value-text { font-size: 1.1rem; font-weight: 500; margin-bottom: 15px; display: block; }
        
        .total-box { background: #0d6efd; color: white; padding: 20px; border-radius: 15px; text-align: center; margin: 20px; }
        .total-amount { font-size: 2.5rem; font-weight: 800; }
        
        /* BOTONES DE ACCIÓN */
        .btn-big { width: 100%; padding: 15px; border-radius: 12px; font-weight: bold; font-size: 1.1rem; margin-bottom: 15px; border: none; }
        .btn-success-custom { background: #28a745; color: white; }
        .btn-danger-custom { background: #dc3545; color: white; opacity: 0.8; }
        
        /* Modal Oscuro */
        .modal-content { background-color: #222; color: white; border: 1px solid #444; }
        .modal-header { border-bottom: 1px solid #444; }
        .modal-footer { border-top: 1px solid #444; }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="index.php?ruta=portal/dashboard" class="btn-back"><i class="fas fa-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Pedido #<?php echo $p['numero_orden']; ?></h5>
    </div>

    <div class="total-box">
        <small class="text-uppercase opacity-75">Monto a Cobrar</small>
        <div class="total-amount">RD$ <?php echo number_format($p['total_venta'], 0); ?></div>
        <span class="badge bg-white text-primary mt-2"><?php echo $p['estado_interno']; ?></span>
    </div>

    <div class="info-card">
        <span class="label-text">Cliente</span>
        <span class="value-text"><?php echo $p['nombre_cliente']; ?></span>

        <span class="label-text">Teléfono</span>
        <span class="value-text">
            <?php echo $p['telefono']; ?> 
            <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $p['telefono']); ?>" class="ms-2 text-decoration-none text-info"><i class="fas fa-phone"></i></a>
        </span>

        <span class="label-text">Dirección de Entrega</span>
        <span class="value-text text-white-50">
            <?php echo $p['direccion']; ?><br>
            <?php echo $p['ciudad']; ?>
        </span>
        
        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($p['direccion'] . ' ' . $p['ciudad']); ?>" target="_blank" class="btn btn-outline-light w-100 rounded-pill mt-2">
            <i class="fas fa-map-marked-alt me-2"></i> Abrir GPS
        </a>
    </div>

    <div class="container pb-4">
        <h6 class="text-muted text-center mb-3 small">¿QUÉ PASÓ CON EL PEDIDO?</h6>

        <button class="btn btn-big btn-success-custom" data-bs-toggle="modal" data-bs-target="#modalEntregado">
            <i class="fas fa-check-circle me-2"></i> ENTREGADO CON ÉXITO
        </button>

        <button class="btn btn-big btn-danger-custom" data-bs-toggle="modal" data-bs-target="#modalFallido">
            <i class="fas fa-times-circle me-2"></i> NO ENTREGADO / DEVUELTO
        </button>
    </div>


    <div class="modal fade" id="modalEntregado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="index.php?ruta=portal/logic" method="POST">
                    <input type="hidden" name="action" value="actualizar_estado_ruta">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="nuevo_estado" value="Entregado">

                    <div class="modal-header">
                        <h5 class="modal-title text-success fw-bold">Confirmar Entrega</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>¿Recibiste el dinero completo?</p>
                        <h2 class="text-white fw-bold">RD$ <?php echo number_format($p['total_venta'], 0); ?></h2>
                        
                        <div class="form-floating mt-3">
                            <textarea class="form-control bg-dark text-white border-secondary" name="comentario" style="height: 80px"></textarea>
                            <label class="text-white-50">Nota opcional (Ej: Recibió el portero)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold w-100">¡SÍ, COBRADO!</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFallido" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="index.php?ruta=portal/logic" method="POST">
                    <input type="hidden" name="action" value="actualizar_estado_ruta">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="nuevo_estado" value="Devuelto">

                    <div class="modal-header">
                        <h5 class="modal-title text-danger fw-bold">Reportar Problema</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">¿Por qué no se entregó?</p>
                        
                        <select name="motivo" class="form-select bg-dark text-white border-secondary mb-3" required>
                            <option value="">Selecciona un motivo...</option>
                            <option value="Cliente no estaba">Cliente no estaba</option>
                            <option value="Dirección incorrecta">Dirección incorrecta</option>
                            <option value="Cliente no tiene dinero">Cliente no tiene dinero</option>
                            <option value="Cliente rechazó pedido">Cliente rechazó pedido</option>
                            <option value="No contesta teléfono">No contesta teléfono</option>
                        </select>

                        <div class="form-floating">
                            <textarea class="form-control bg-dark text-white border-secondary" name="comentario" style="height: 80px"></textarea>
                            <label class="text-white-50">Detalle adicional...</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger fw-bold w-100">REPORTAR DEVOLUCIÓN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>