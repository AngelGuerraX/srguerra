<?php

// 1. VALIDAR ID
if (!isset($_GET['id'])) {
    echo "<script>window.location='pedidos';</script>";
    exit;
}

$pedido_id = $_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// 2. OBTENER DATOS DEL PEDIDO + CLIENTE + TRANSPORTADORA + ALMACEN
$sql = "SELECT p.*, 
               c.nombre as cli_nombre, c.telefono as cli_telefono, c.provincia as cli_provincia, c.ciudad as cli_ciudad, c.direccion as cli_direccion,
               t.nombre as trans_nombre,
               a.nombre as alm_nombre
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN transportadoras t ON p.transportadora_id = t.id
        LEFT JOIN almacenes a ON p.almacen_id = a.id
        WHERE p.id = ? AND p.empresa_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido_id, $empresa_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    echo "<div class='alert alert-danger'>Pedido no encontrado o no tienes permiso.</div>";
    include 'includes/footer.php';
    exit;
}

// 3. OBTENER ITEMS DEL PEDIDO
$stmt = $pdo->prepare("SELECT * FROM pedidos_detalle WHERE pedido_id = ?");
$stmt->execute([$pedido_id]);
$items = $stmt->fetchAll();

// Helper para Mensaje de WhatsApp
$mensaje_ws = "Hola {$pedido['cli_nombre']}, le saludamos de SRGUERRA. Para confirmar su pedido #{$pedido['numero_orden']} de un total de RD$" . number_format($pedido['total_venta']) . ". ¿Sus datos de entrega son correctos?";
$link_ws = "https://wa.me/1" . preg_replace('/[^0-9]/', '', $pedido['cli_telefono']) . "?text=" . urlencode($mensaje_ws);

// Helper para colores de estado
function getStatusColor($status)
{
    switch ($status) {
        case 'Nuevo':
            return 'primary';
        case 'Confirmado':
            return 'info';
        case 'En Ruta':
            return 'warning';
        case 'Entregado':
            return 'success';
        case 'Devuelto':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
<?php
// modules/pedidos/ver.php
// VISTA DETALLADA DEL PEDIDO CON EDICIÓN LOGÍSTICA

// 1. Validar ID
$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresa_id = $_SESSION['empresa_id'];

if ($pedido_id == 0) {
    echo "<div class='alert alert-danger m-4'>Error: ID de pedido no válido.</div>";
    return;
}

// 2. Obtener Datos del Pedido
$sql = "SELECT p.*, 
               c.nombre as cli_nombre, c.telefono as cli_telefono, c.direccion as cli_direccion, 
               c.ciudad as cli_ciudad, c.provincia as cli_provincia,
               t.nombre as trans_nombre,
               a.nombre as almacen_nombre
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN transportadoras t ON p.transportadora_id = t.id
        LEFT JOIN almacenes a ON p.almacen_id = a.id
        WHERE p.id = ? AND p.empresa_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido_id, $empresa_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    echo "<div class='container p-5 text-center'><h2 class='text-danger'>Pedido no encontrado</h2><a href='index.php?ruta=pedidos' class='btn btn-outline-light'>Volver</a></div>";
    return;
}

// 3. Obtener Listas para los Dropdowns (NUEVO)
$lista_trans = $pdo->query("SELECT * FROM transportadoras WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
$lista_alm = $pdo->query("SELECT * FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();

// 4. Obtener Detalles
$stmt_det = $pdo->prepare("SELECT * FROM pedidos_detalle WHERE pedido_id = ?");
$stmt_det->execute([$pedido_id]);
$detalles = $stmt_det->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6 d-flex align-items-center">
        <a href="index.php?ruta=pedidos" class="btn btn-outline-light btn-sm me-3 rounded-circle shadow"><i class="fas fa-arrow-left"></i></a>
        <div>
            <span class="h-label text-muted">DETALLE DE ORDEN</span>
            <h2 class="fw-bold text-white mb-0">Orden #<?php echo htmlspecialchars($pedido['numero_orden']); ?></h2>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <a href="index.php?ruta=imprimir-etiqueta&id=<?php echo $pedido['id']; ?>" target="_blank" class="btn btn-outline-warning rounded-pill px-4 btn-glow">
            <i class="fas fa-print me-2"></i> Imprimir Etiqueta
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-glass p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="h-label text-neon">ESTADO ACTUAL</span>
                <span class="badge bg-primary fs-6 px-3 rounded-pill"><?php echo $pedido['estado_interno']; ?></span>
            </div>
            <form action="index.php?ruta=actualizar-estado-pedido" method="POST" class="d-flex gap-2">
                <input type="hidden" name="action" value="actualizar_estado">
                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                <div class="flex-grow-1">
                    <select name="nuevo_estado" class="form-select bg-dark text-white border-secondary">
                        <option value="Nuevo" <?php echo $pedido['estado_interno'] == 'Nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="Confirmado" <?php echo $pedido['estado_interno'] == 'Confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                        <option value="En Ruta" <?php echo $pedido['estado_interno'] == 'En Ruta' ? 'selected' : ''; ?>>🚚 En Ruta</option>
                        <option value="Entregado" <?php echo $pedido['estado_interno'] == 'Entregado' ? 'selected' : ''; ?>>✅ Entregado</option>
                        <option value="Devuelto" <?php echo $pedido['estado_interno'] == 'Devuelto' ? 'selected' : ''; ?>>↩️ Devuelto</option>
                        <option value="Cancelado" <?php echo $pedido['estado_interno'] == 'Cancelado' ? 'selected' : ''; ?>>❌ Cancelado</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow">Actualizar</button>
            </form>
        </div>

        <div class="card-glass p-4">
            <span class="h-label text-neon mb-3">PRODUCTOS</span>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $d): ?>
                            <tr>
                                <td><i class="fas fa-box me-2 text-muted"></i><?php echo $d['nombre_producto']; ?></td>
                                <td class="text-center fw-bold text-white"><?php echo $d['cantidad']; ?></td>
                                <td class="text-end fw-bold text-neon">RD$ <?php echo number_format($d['precio_unitario'] * $d['cantidad'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-top border-secondary">
                        <tr>
                            <td colspan="2" class="text-end text-white fs-4 fw-bold pt-3">TOTAL A COBRAR</td>
                            <td class="text-end text-white fs-4 fw-bold pt-3">RD$ <?php echo number_format($pedido['total_venta'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-glass p-4 mb-4">
            <span class="h-label text-neon mb-3">CLIENTE</span>
            <?php if ($pedido['cli_nombre']): ?>
                <h5 class="text-white fw-bold"><?php echo $pedido['cli_nombre']; ?></h5>
                <p class="text-primary mb-3"><i class="fas fa-phone me-2"></i><?php echo $pedido['cli_telefono']; ?></p>
                <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary small">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo $pedido['cli_direccion']; ?><br>
                    <span class="text-muted ms-4"><?php echo $pedido['cli_ciudad']; ?>, <?php echo $pedido['cli_provincia']; ?></span>
                </div>
            <?php else: ?>
                <div class="alert alert-warning small">Cliente eliminado.</div>
            <?php endif; ?>
        </div>

        <div class="card-glass p-4">
            <span class="h-label text-neon mb-3">ASIGNACIÓN LOGÍSTICA</span>

            <form action="index.php?ruta=guardar-pedido" method="POST">
                <input type="hidden" name="action" value="asignar_logistica">
                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">

                <div class="mb-3">
                    <label class="text-muted small mb-1">Transportadora</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-warning"><i class="fas fa-shipping-fast"></i></span>
                        <select name="transportadora_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Sin Asignar --</option>
                            <?php foreach ($lista_trans as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo ($pedido['transportadora_id'] == $t['id']) ? 'selected' : ''; ?>>
                                    <?php echo $t['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small mb-1">Origen Inventario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-info"><i class="fas fa-warehouse"></i></span>
                        <select name="almacen_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Sin Asignar --</option>
                            <?php foreach ($lista_alm as $a): ?>
                                <option value="<?php echo $a['id']; ?>" <?php echo ($pedido['almacen_id'] == $a['id']) ? 'selected' : ''; ?>>
                                    <?php echo $a['nombre']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-outline-light w-100 btn-sm">
                    <i class="fas fa-save me-2"></i> Guardar Logística
                </button>
            </form>

            <hr class="border-secondary opacity-25 my-4">

            <div class="p-3 bg-dark rounded border border-secondary">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small text-muted">Costo Envío:</span>
                    <span class="small text-danger fw-bold">RD$ <?php echo number_format($pedido['costo_envio_real'], 0); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small text-muted">Costo Empaque:</span>
                    <span class="small text-danger fw-bold">RD$ <?php echo number_format($pedido['costo_empaque_real'], 0); ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>