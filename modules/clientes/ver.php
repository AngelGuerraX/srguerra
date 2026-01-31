<?php
// modules/clientes/ver.php
// PERFIL 360 DEL CLIENTE

$id = (int)$_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// 1. DATOS DEL CLIENTE
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$c = $stmt->fetch();

if(!$c) die("Cliente no encontrado");

// 2. HISTORIAL DE PEDIDOS
$stmt_p = $pdo->prepare("SELECT p.*, t.nombre as trans_nombre 
                         FROM pedidos p 
                         LEFT JOIN transportadoras t ON p.transportadora_id = t.id
                         WHERE p.cliente_id = ? ORDER BY p.fecha_creacion DESC");
$stmt_p->execute([$id]);
$pedidos = $stmt_p->fetchAll();

// KPIs Personales
$total_gastado = 0;
$total_pedidos = count($pedidos);
foreach($pedidos as $p) {
    if($p['estado_interno'] != 'Cancelado') $total_gastado += $p['total_venta'];
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php?ruta=clientes" class="btn btn-outline-light btn-sm me-3 rounded-circle shadow"><i class="fas fa-arrow-left"></i></a>
    <div>
        <span class="h-label text-neon">PERFIL DE CLIENTE</span>
        <h2 class="fw-bold text-white mb-0"><?php echo $c['nombre']; ?></h2>
    </div>
    <div class="ms-auto">
        <a href="index.php?ruta=clientes/editar&id=<?php echo $c['id']; ?>" class="btn btn-outline-info btn-sm rounded-pill px-3">
            <i class="fas fa-edit me-2"></i> Editar Datos
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-glass p-4 h-100">
            <h5 class="text-white fw-bold mb-3">Datos de Contacto</h5>
            
            <div class="mb-3">
                <small class="text-muted d-block">Teléfono</small>
                <span class="text-white fs-5"><a href="tel:<?php echo $c['telefono']; ?>" class="text-white text-decoration-none"><?php echo $c['telefono']; ?></a></span>
            </div>
            
            <div class="mb-3">
                <small class="text-muted d-block">Dirección de Entrega</small>
                <span class="text-white">
                    <?php echo $c['direccion']; ?><br>
                    <?php echo $c['ciudad']; ?>, <?php echo $c['provincia']; ?>
                </span>
            </div>

            <div class="mt-4 pt-3 border-top border-secondary">
                <a href="https://wa.me/1<?php echo str_replace(['-',' '], '', $c['telefono']); ?>" target="_blank" class="btn btn-success w-100 fw-bold">
                    <i class="fab fa-whatsapp me-2"></i> Contactar por WhatsApp
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card-glass p-3 border-start border-4 border-primary">
                    <small class="text-primary text-uppercase fw-bold">Valor de Vida (LTV)</small>
                    <h2 class="text-white fw-bold mb-0 mt-1">RD$ <?php echo number_format($total_gastado, 0); ?></h2>
                    <small class="text-muted">Total comprado históricamente</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-glass p-3 border-start border-4 border-info">
                    <small class="text-info text-uppercase fw-bold">Frecuencia</small>
                    <h2 class="text-white fw-bold mb-0 mt-1"><?php echo $total_pedidos; ?> Pedidos</h2>
                    <small class="text-muted">En total</small>
                </div>
            </div>
        </div>

        <div class="card-glass p-0 overflow-hidden">
            <div class="p-3 border-bottom border-secondary bg-black bg-opacity-25">
                <h6 class="text-white fw-bold mb-0">Historial de Compras</h6>
            </div>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="small text-muted">
                            <th>Orden</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-end">Monto</th>
                            <th class="text-end">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pedidos as $p): ?>
                        <tr>
                            <td class="text-neon fw-bold"><?php echo $p['numero_orden']; ?></td>
                            <td class="small text-muted"><?php echo date('d/m/Y', strtotime($p['fecha_creacion'])); ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($p['estado_interno'] == 'Entregado') $badge = 'bg-success';
                                    if($p['estado_interno'] == 'En Ruta') $badge = 'bg-warning text-dark';
                                    if($p['estado_interno'] == 'Cancelado') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge; ?> rounded-pill" style="font-size: 10px;"><?php echo $p['estado_interno']; ?></span>
                            </td>
                            <td class="text-end text-white">RD$ <?php echo number_format($p['total_venta'], 0); ?></td>
                            <td class="text-end">
                                <a href="index.php?ruta=pedidos/ver&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-light rounded-circle">
                                    <i class="fas fa-eye" style="font-size:10px;"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>