<?php
// modules/portal/detalle.php
if (!isset($_SESSION['rol'])) exit;

$id = $_GET['id'];
$trans_id = $_SESSION['transportadora_id'];

$stmt = $pdo->prepare("SELECT p.*, c.nombre as cli_nom, c.telefono, c.direccion, c.ciudad, a.nombre as nombre_almacen 
                       FROM pedidos p 
                       JOIN clientes c ON p.cliente_id = c.id 
                       LEFT JOIN almacenes a ON p.almacen_id = a.id
                       WHERE p.id = ? AND p.transportadora_id = ?");
$stmt->execute([$id, $trans_id]);
$p = $stmt->fetch();

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
    <style>body { background-color: #000; color: #fff; font-family: sans-serif; padding-bottom: 30px; }</style>
</head>
<body>
    <div class="container py-3">
        <a href="index.php?ruta=portal/dashboard" class="text-muted text-decoration-none mb-3 d-block"><i class="fas fa-arrow-left"></i> Volver</a>
        
        <div class="card bg-dark border-secondary mb-3 text-center">
            <div class="card-body">
                <small class="text-muted">TOTAL A COBRAR</small>
                <h1 class="text-white fw-bold">RD$ <?php echo number_format($p['total_venta']); ?></h1>
            </div>
        </div>

        <div class="card bg-dark border-secondary mb-4">
            <div class="card-body">
                <h5 class="fw-bold"><?php echo $p['cli_nom']; ?></h5>
                <p class="text-info small mb-3"><i class="fas fa-warehouse"></i> Retorno a: <?php echo $p['nombre_almacen']; ?></p>
                
                <div class="bg-black bg-opacity-25 p-3 rounded mb-3 border border-secondary">
                    <?php echo $p['direccion']; ?><br>
                    <span class="text-muted"><?php echo $p['ciudad']; ?></span>
                </div>

                <div class="d-flex gap-2">
                    <a href="tel:<?php echo $p['telefono']; ?>" class="btn btn-outline-success flex-fill"><i class="fas fa-phone"></i> Llamar</a>
                    <a href="https://wa.me/1<?php echo preg_replace('/[^0-9]/','',$p['telefono']); ?>" class="btn btn-outline-success flex-fill"><i class="fab fa-whatsapp"></i> Chat</a>
                </div>
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($p['direccion'].' '.$p['ciudad']); ?>" class="btn btn-outline-info w-100 mt-2">Abrir GPS</a>
            </div>
        </div>

        <h6 class="text-muted text-center mb-3 small">GESTIÓN DE ENTREGA</h6>
        
        <form action="index.php?ruta=portal/logic" method="POST" class="mb-3">
            <input type="hidden" name="action" value="marcar_entregado">
            <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
            <button class="btn btn-success w-100 py-3 fw-bold shadow">
                <i class="fas fa-check-circle me-2"></i> CONFIRMAR ENTREGA
            </button>
        </form>

        <button class="btn btn-danger w-100 py-2 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRechazo">
            <i class="fas fa-times-circle me-2"></i> RECHAZADO / NO ENTREGADO
        </button>

        <div class="collapse mt-3" id="collapseRechazo">
            <div class="card card-body bg-danger bg-opacity-10 border-danger">
                <p class="text-center small mb-2">Esto marcará el pedido para retorno.</p>
                <form action="index.php?ruta=portal/logic" method="POST">
                    <input type="hidden" name="action" value="marcar_rechazado">
                    <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                    
                    <select name="motivo" class="form-select bg-dark text-white border-secondary mb-3" required>
                        <option value="">-- Motivo del Rechazo --</option>
                        <option value="Cliente no estaba">Cliente no estaba</option>
                        <option value="Sin dinero">No tiene dinero</option>
                        <option value="Rechazó producto">Rechazó producto</option>
                        <option value="Dirección incorrecta">Dirección incorrecta</option>
                    </select>
                    
                    <button class="btn btn-danger w-100 fw-bold">CONFIRMAR RECHAZO</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>