<?php
// modules/marketing/index.php
// GESTIÓN DE CAMPAÑAS Y GASTOS (ADS)

$empresa_id = $_SESSION['empresa_id'];
$mensaje = '';

// --- 1. PROCESAR FORMULARIOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // A) CREAR CAMPAÑA
    if ($action === 'crear_campana') {
        $nombre = $_POST['nombre'];
        $prod_id = $_POST['producto_id'];
        $plat = $_POST['plataforma'];
        
        $sql = "INSERT INTO marketing_campanas (empresa_id, producto_id, nombre, plataforma) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$empresa_id, $prod_id, $nombre, $plat]);
        $mensaje = 'Campaña creada correctamente.';
    }

    // B) REGISTRAR GASTO DIARIO
    if ($action === 'registrar_gasto') {
        $camp_id = $_POST['campana_id'];
        $fecha = $_POST['fecha'];
        $monto = $_POST['monto'];

        $sql = "INSERT INTO marketing_gasto (campana_id, fecha, monto) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$camp_id, $fecha, $monto]);
        $mensaje = 'Gasto registrado.';
    }
}

// --- 2. CONSULTAS ---
// Lista de Productos (para el select)
$productos = $pdo->query("SELECT id, nombre, sku FROM productos WHERE empresa_id = $empresa_id")->fetchAll();

// Lista de Campañas Activas (con total gastado)
$sql_campanas = "SELECT c.*, p.nombre as nombre_producto, 
                (SELECT SUM(monto) FROM marketing_gasto WHERE campana_id = c.id) as total_gastado
                FROM marketing_campanas c 
                LEFT JOIN productos p ON c.producto_id = p.id 
                WHERE c.empresa_id = $empresa_id ORDER BY c.id DESC";
$lista_campanas = $pdo->query($sql_campanas)->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="h-label">MARKETING</span>
            <h2 class="fw-bold text-white">Gestión de Publicidad (Ads)</h2>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCampana">
            <i class="fas fa-plus me-2"></i> Nueva Campaña
        </button>
    </div>

    <?php if($mensaje): ?>
        <div class="alert alert-success border-0 bg-success text-white bg-opacity-75 fade show"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h5 class="mb-0 text-info"><i class="fas fa-file-invoice-dollar me-2"></i>Registrar Gasto Diario</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="registrar_gasto">
                        
                        <div class="mb-3">
                            <label class="text-muted small">Campaña</label>
                            <select name="campana_id" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Selecciona campaña...</option>
                                <?php foreach($lista_campanas as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['nombre']; ?> (<?php echo $c['plataforma']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted small">Fecha</label>
                            <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control bg-dark text-white border-secondary">
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Monto Gastado (RD$)</label>
                            <input type="number" step="0.01" name="monto" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 500.00" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Guardar Gasto</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card bg-dark border-secondary">
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-secondary text-uppercase small">
                            <tr>
                                <th>Campaña</th>
                                <th>Producto Asociado</th>
                                <th>Plataforma</th>
                                <th class="text-end">Gasto Total</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista_campanas as $c): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $c['nombre']; ?></td>
                                <td class="text-info"><?php echo $c['nombre_producto'] ?: 'Sin asignar'; ?></td>
                                <td><span class="badge bg-secondary"><?php echo $c['plataforma']; ?></span></td>
                                <td class="text-end text-danger">RD$ <?php echo number_format($c['total_gastado'], 2); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-25 text-success">Activa</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCampana" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Crear Nueva Campaña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear_campana">
                    
                    <div class="mb-3">
                        <label>Nombre de la Campaña</label>
                        <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Escalado Reloj Marzo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Producto a Promocionar</label>
                        <select name="producto_id" class="form-select bg-dark text-white border-secondary" required>
                            <?php foreach($productos as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?> (<?php echo $p['sku']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Esto vinculará el gasto al producto en Finanzas.</small>
                    </div>

                    <div class="mb-3">
                        <label>Plataforma</label>
                        <select name="plataforma" class="form-select bg-dark text-white border-secondary">
                            <option value="Facebook Ads">Facebook Ads</option>
                            <option value="TikTok Ads">TikTok Ads</option>
                            <option value="Google Ads">Google Ads</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" class="btn btn-primary">Crear Campaña</button>
                </div>
            </form>
        </div>
    </div>
</div>