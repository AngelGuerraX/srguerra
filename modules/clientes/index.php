<?php
// modules/clientes/index.php
// LISTADO DE CLIENTES (RANKING LTV)

$empresa_id = $_SESSION['empresa_id'];
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// CONSULTA INTELIGENTE:
// Calculamos al vuelo cuántos pedidos tiene cada uno y cuánto dinero ha dejado (LTV).
$sql = "SELECT c.*,
        (SELECT COUNT(*) FROM pedidos WHERE cliente_id = c.id AND estado_interno != 'Cancelado') as total_pedidos,
        (SELECT SUM(total_venta) FROM pedidos WHERE cliente_id = c.id AND estado_interno != 'Cancelado') as total_gastado,
        (SELECT MAX(fecha_creacion) FROM pedidos WHERE cliente_id = c.id) as ultima_compra
        FROM clientes c
        WHERE c.empresa_id = ?";

$params = [$empresa_id];

if (!empty($busqueda)) {
    $sql .= " AND (c.nombre LIKE ? OR c.telefono LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// Ordenamos por "Quien más gasta" (VIPs primero)
$sql .= " ORDER BY total_gastado DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="h-label">CRM</span>
        <h2 class="fw-bold text-white">Cartera de Clientes</h2>
    </div>
    </div>

<div class="card-glass p-3 mb-4">
    <form action="index.php" method="GET" class="row g-2">
        <input type="hidden" name="ruta" value="clientes">
        <div class="col-md-10">
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="q" value="<?php echo $busqueda; ?>" class="form-control bg-dark text-white border-secondary" placeholder="Buscar por Nombre o Teléfono...">
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100 fw-bold">Buscar</button>
        </div>
    </form>
</div>

<div class="card-glass p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark-custom align-middle mb-0">
            <thead>
                <tr class="small text-muted text-uppercase">
                    <th>Cliente</th>
                    <th>Ubicación</th>
                    <th class="text-center">Pedidos</th>
                    <th class="text-end">Total Gastado (LTV)</th>
                    <th class="text-end">Última Compra</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($clientes) > 0): ?>
                    <?php foreach($clientes as $c): ?>
                        <tr class="group-hover">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                        <?php echo strtoupper(substr($c['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-white"><?php echo $c['nombre']; ?></div>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 10px;"></i> <?php echo $c['telefono']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted">
                                <?php echo $c['ciudad']; ?>, <?php echo $c['provincia']; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark border border-secondary rounded-pill px-3"><?php echo $c['total_pedidos']; ?></span>
                            </td>
                            <td class="text-end fw-bold text-neon">
                                RD$ <?php echo number_format($c['total_gastado'], 0); ?>
                            </td>
                            <td class="text-end small text-muted">
                                <?php echo $c['ultima_compra'] ? date('d/m/Y', strtotime($c['ultima_compra'])) : '-'; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?ruta=clientes/ver&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-light rounded-circle shadow" title="Ver Perfil">
                                    <i class="fas fa-user"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron clientes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>