<?php
// modules/portal/detalle.php
// VISTA DETALLE DEL PEDIDO (APP CONDUCTOR)

if (!isset($_SESSION['rol'])) exit;

$id = $_GET['id'];
$trans_id = $_SESSION['transportadora_id'];

// Consultamos datos del pedido, cliente y almacén
$stmt = $pdo->prepare("SELECT p.*, c.nombre as cli_nom, c.telefono, c.direccion, c.ciudad, a.nombre as nombre_almacen 
                       FROM pedidos p 
                       JOIN clientes c ON p.cliente_id = c.id 
                       LEFT JOIN almacenes a ON p.almacen_id = a.id
                       WHERE p.id = ? AND p.transportadora_id = ?");
$stmt->execute([$id, $trans_id]);
$p = $stmt->fetch();

// Si no existe o no es de este chofer, volver
if(!$p) echo "<script>window.location='index.php?ruta=portal/dashboard';</script>";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Pedido #<?php echo $p['numero_orden']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #e0e0e0; font-family: sans-serif; padding-bottom: 40px; }
        .card { border-radius: 15px; }
        .btn-xl { padding: 15px; font-size: 1.1rem; font-weight: bold; width: 100%; border-radius: 12px; }
        .info-box { background: rgba(255, 255, 255, 0.05); border: 1px solid #333; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container py-3">
        <a href="index.php?ruta=portal/dashboard" class="text-decoration-none text-muted mb-3 d-flex align-items-center">
            <i class="fas fa-arrow-left me-2"></i> Volver a la ruta
        </a>
        
        <div class="card bg-primary bg-gradient border-0 mb-3 text-center shadow-lg">
            <div class="card-body py-4">
                <small class="text-white-50 text-uppercase fw-bold">Total a Cobrar</small>
                <h1 class="text-white fw-bold display-4 m-0">RD$ <?php echo number_format($p['total_venta']); ?></h1>
                <span class="badge bg-black bg-opacity-25 mt-2">Pago Contra Entrega</span>
            </div>
        </div>

        <div class="card bg-dark border-secondary mb-4">
            <div class="card-body">
                <h5 class="fw-bold text-white mb-1"><?php echo $p['cli_nom']; ?></h5>
                
                <div class="d-flex align-items-center text-info small mb-3">
                    <i class="fas fa-warehouse me-2"></i> 
                    <span>Retorno a: <strong><?php echo $p['nombre_almacen'] ?: 'Central'; ?></strong></span>
                </div>
                
                <div class="info-box">
                    <div class="d-flex">
                        <i class="fas fa-map-marker-alt text-danger me-3 mt-1"></i>
                        <div>
                            <span class="text-white d-block"><?php echo $p['direccion']; ?></span>
                            <span class="text-muted small"><?php echo $p['ciudad']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <a href="tel:<?php echo $p['telefono']; ?>" class="btn btn-outline-success flex-fill fw-bold">
                        <i class="fas fa-phone me-2"></i> Llamar
                    </a>
                    <a href="https://wa.me/1<?php echo preg_replace('/[^0-9]/','',$p['telefono']); ?>" target="_blank" class="btn btn-success flex-fill fw-bold">
                        <i class="fab fa-whatsapp me-2"></i> Chat
                    </a>
                </div>
                
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($p['direccion'].' '.$p['ciudad']); ?>" target="_blank" class="btn btn-dark border-secondary w-100 text-info">
                    <i class="fas fa-location-arrow me-2"></i> Abrir GPS (Google Maps)
                </a>
            </div>
        </div>

        <h6 class="text-muted text-center mb-3 small text-uppercase ls-1">Acciones de Entrega</h6>
        
        <form action="index.php?ruta=portal/logic" method="POST" class="mb-3" onsubmit="return confirm('¿Confirmas que recibiste el dinero y entregaste el paquete?');">
            <input type="hidden" name="action" value="marcar_entregado">
            <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
            
            <button type="submit" class="btn btn-success btn-xl shadow">
                <i class="fas fa-check-circle me-2"></i> CONFIRMAR ENTREGA
            </button>
        </form>

        <button class="btn btn-danger btn-xl mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRechazo">
            <i class="fas fa-times-circle me-2"></i> NO ENTREGADO / RECHAZAR
        </button>

        <div class="collapse" id="collapseRechazo">
            <div class="card bg-danger bg-opacity-10 border-danger">
                <div class="card-body">
                    <h6 class="text-danger fw-bold mb-2">Reportar Problema</h6>
                    <p class="text-white-50 small mb-3">El pedido quedará marcado como "Rechazado" y deberás devolverlo al almacén.</p>
                    
                    <form action="index.php?ruta=portal/logic" method="POST">
                        <input type="hidden" name="action" value="marcar_rechazado">
                        <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="text-white small mb-1">Motivo:</label>
                            <select name="motivo" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Cliente no estaba">Cliente no estaba</option>
                                <option value="Sin dinero">Cliente no tiene dinero</option>
                                <option value="Rechazó producto">Cliente rechazó el producto</option>
                                <option value="Dirección incorrecta">Dirección incorrecta</option>
                                <option value="Teléfono apagado">Teléfono apagado / No contesta</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="text-white small mb-1">Nota adicional (Opcional):</label>
                            <textarea name="comentario" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Ej: Fui 2 veces y no salió..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            CONFIRMAR RECHAZO
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>