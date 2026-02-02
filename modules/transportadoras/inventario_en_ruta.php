<?php
// modules/transportadoras/inventario_en_ruta.php
// REPORTE DE MERCANCÍA EN PODER DE TRANSPORTADORAS

$empresa_id = $_SESSION['empresa_id'] ?? 1;

// Obtener lista de transportadoras para filtrar (Opcional, aquí mostramos todas agrupadas)
// Consulta MAESTRA: Agrupa por Transportadora y Producto
$sql = "SELECT 
            t.nombre as transportadora,
            p.nombre_producto,
            SUM(d.cantidad) as cantidad_total,
            COUNT(DISTINCT ped.id) as total_pedidos
        FROM pedidos_detalle d
        JOIN pedidos ped ON d.pedido_id = ped.id
        JOIN transportadoras t ON ped.transportadora_id = t.id
        WHERE ped.empresa_id = ? 
        AND ped.estado_interno = 'En Ruta' -- Solo lo que está en la calle
        GROUP BY t.id, d.nombre_producto
        ORDER BY t.nombre ASC, p.nombre_producto ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);
$resultados = $stmt->fetchAll(PDO::FETCH_GROUP); // Agrupa por el primer campo (Transportadora)
?>

<div class="container-fluid">
    <h3 class="text-white mb-4"><i class="fas fa-boxes me-2"></i> Inventario en Tránsito (En Ruta)</h3>

    <div class="row">
        <?php if(empty($resultados)): ?>
            <div class="col-12 text-center text-muted py-5">
                <h4><i class="fas fa-road fa-2x mb-3"></i></h4>
                <p>No hay mercancía en ruta actualmente.</p>
            </div>
        <?php endif; ?>

        <?php foreach($resultados as $transportadora => $productos): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card bg-dark border-info h-100">
                    <div class="card-header border-info bg-info bg-opacity-10 text-info fw-bold d-flex justify-content-between">
                        <span><i class="fas fa-truck me-2"></i> <?php echo $transportadora; ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-dark table-striped mb-0 small">
                            <thead>
                                <tr>
                                    <th class="ps-3">Producto</th>
                                    <th class="text-end pe-3">Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_items = 0;
                                foreach($productos as $prod): 
                                    $total_items += $prod['cantidad_total'];
                                ?>
                                <tr>
                                    <td class="ps-3"><?php echo $prod['nombre_producto']; ?></td>
                                    <td class="text-end pe-3 fw-bold text-white"><?php echo $prod['cantidad_total']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <td class="ps-3 text-dark fw-bold">TOTAL UNIDADES</td>
                                    <td class="text-end pe-3 text-dark fw-bold"><?php echo $total_items; ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>