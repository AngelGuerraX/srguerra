<?php
// modules/finanzas/productos.php
// REPORTE DE RENTABILIDAD POR PRODUCTO (INCLUYE FULFILLMENT Y ADS)

$empresa_id = $_SESSION['empresa_id'];
$f_inicio = $_GET['f_ini'] ?? date('Y-m-01');
$f_fin    = $_GET['f_fin'] ?? date('Y-m-d');

// 1. OBTENER VENTAS Y COSTOS LOGÍSTICOS (Solo Entregados)
// Nota: Distribuimos el costo de envío/empaque del pedido proporcionalmente a los items.
// Si un pedido tiene 1 item, ese item absorbe el 100% del costo de envío.
$sql_ventas = "
    SELECT 
        pd.producto_id,
        p.nombre as nombre_producto,
        p.sku,
        p.imagen,
        SUM(pd.cantidad) as unidades_vendidas,
        SUM(pd.cantidad * pd.precio_unitario) as venta_total,
        
        -- Si el costo histórico es 0, usamos el costo actual del producto
        SUM(pd.cantidad * CASE WHEN pd.costo_unitario > 0 THEN pd.costo_unitario ELSE p.costo_compra END) as cogs_total,

        -- Logística: Sumamos el (Envío + Empaque) de los pedidos donde aparece este producto
        -- Dividido por la cantidad de items en el pedido para ser justos
        SUM( (ped.costo_envio_real + ped.costo_empaque_real) / (SELECT SUM(cantidad) FROM pedidos_detalle WHERE pedido_id = ped.id) * pd.cantidad ) as logistica_total

    FROM pedidos_detalle pd
    JOIN pedidos ped ON pd.pedido_id = ped.id
    LEFT JOIN productos p ON pd.producto_id = p.id
    WHERE ped.empresa_id = ? 
    AND ped.estado_interno = 'Entregado'
    AND DATE(ped.fecha_creacion) BETWEEN ? AND ?
    GROUP BY pd.producto_id
";

$stmt = $pdo->prepare($sql_ventas);
$stmt->execute([$empresa_id, $f_inicio, $f_fin]);
$data_ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. OBTENER GASTOS DE ADS (Agrupado por Producto)
$sql_ads = "
    SELECT c.producto_id, SUM(g.monto) as gasto_ads
    FROM marketing_gasto g
    JOIN marketing_campanas c ON g.campana_id = c.id
    WHERE c.empresa_id = ? 
    AND g.fecha BETWEEN ? AND ?
    GROUP BY c.producto_id
";
$stmt_ads = $pdo->prepare($sql_ads);
$stmt_ads->execute([$empresa_id, $f_inicio, $f_fin]);
$data_ads = [];
while ($row = $stmt_ads->fetch(PDO::FETCH_ASSOC)) {
    $data_ads[$row['producto_id']] = $row['gasto_ads'];
}

// 3. FUSIONAR Y CALCULAR
$reporte = [];
foreach ($data_ventas as $v) {
    $pid = $v['producto_id'];
    $ads = isset($data_ads[$pid]) ? $data_ads[$pid] : 0;
    
    $ganancia_neta = $v['venta_total'] - $v['cogs_total'] - $v['logistica_total'] - $ads;
    $roi = ($v['venta_total'] > 0) ? ($ganancia_neta / $v['venta_total']) * 100 : 0;
    $cpa = ($v['unidades_vendidas'] > 0) ? ($ads / $v['unidades_vendidas']) : 0;

    $v['ads'] = $ads;
    $v['ganancia_neta'] = $ganancia_neta;
    $v['roi'] = $roi;
    $v['cpa'] = $cpa;
    $reporte[] = $v;
}

// Ordenar por Ganancia Neta (Mayor a Menor)
usort($reporte, function($a, $b) {
    return $b['ganancia_neta'] <=> $a['ganancia_neta'];
});
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="h-label">FINANZAS</span>
            <h2 class="fw-bold text-white">Rentabilidad por Producto</h2>
        </div>
        <form class="d-flex gap-2 bg-dark p-2 rounded border border-secondary">
            <input type="hidden" name="ruta" value="finanzas/productos">
            <input type="date" name="f_ini" value="<?php echo $f_inicio; ?>" class="form-control form-control-sm bg-secondary text-white border-0">
            <input type="date" name="f_fin" value="<?php echo $f_fin; ?>" class="form-control form-control-sm bg-secondary text-white border-0">
            <button class="btn btn-sm btn-primary"><i class="fas fa-filter"></i></button>
        </form>
    </div>

    <div class="card bg-dark border-secondary">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="bg-secondary text-uppercase small text-muted">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">U. Vendidas</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Costo Prod.</th>
                        <th class="text-end" title="Envío + Fulfillment (Empaque)">Logística <i class="fas fa-info-circle"></i></th>
                        <th class="text-end text-info">Ads (FB)</th>
                        <th class="text-center">CPA</th>
                        <th class="text-end">Ganancia Neta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($reporte)): ?>
                        <tr><td colspan="8" class="text-center py-5">No hay ventas entregadas en este periodo.</td></tr>
                    <?php else: ?>
                        <?php foreach($reporte as $r): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($r['imagen']): ?>
                                        <img src="uploads/<?php echo $r['imagen']; ?>" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-box"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-white"><?php echo $r['nombre_producto']; ?></div>
                                        <div class="small text-muted"><?php echo $r['sku']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bold"><?php echo $r['unidades_vendidas']; ?></td>
                            <td class="text-end text-success">RD$ <?php echo number_format($r['venta_total'], 0); ?></td>
                            <td class="text-end text-muted">- <?php echo number_format($r['cogs_total'], 0); ?></td>
                            <td class="text-end text-warning">- <?php echo number_format($r['logistica_total'], 0); ?></td>
                            <td class="text-end text-info fw-bold">
                                <?php echo ($r['ads'] > 0) ? '- '.number_format($r['ads'], 0) : '-'; ?>
                            </td>
                            <td class="text-center small">
                                <?php echo ($r['cpa'] > 0) ? 'RD$ '.number_format($r['cpa'], 0) : '-'; ?>
                            </td>
                            <td class="text-end">
                                <?php if($r['ganancia_neta'] >= 0): ?>
                                    <h5 class="mb-0 text-success fw-bold">RD$ <?php echo number_format($r['ganancia_neta'], 0); ?></h5>
                                    <small class="text-success bg-success bg-opacity-10 px-2 rounded">ROI: <?php echo number_format($r['roi'], 1); ?>%</small>
                                <?php else: ?>
                                    <h5 class="mb-0 text-danger fw-bold">RD$ <?php echo number_format($r['ganancia_neta'], 0); ?></h5>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>