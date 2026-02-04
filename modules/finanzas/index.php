<?php
// modules/finanzas/index.php
// REPORTE GENERAL DE ESTADO DE RESULTADOS + VALORACIÓN DE INVENTARIO

// 1. SEGURIDAD Y CONTEXTO
if (!isset($_SESSION['empresa_id'])) return;
$empresa_id = $_SESSION['empresa_id'];

// 2. FILTROS DE FECHA (Por defecto: Mes Actual)
$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-d');

// =========================================================
// 3. CONSULTAS SQL (MATEMÁTICA FINANCIERA)
// =========================================================

// A. INGRESOS POR VENTAS (Solo lo cobrado/entregado)
$sql_ventas = "SELECT COALESCE(SUM(total_venta), 0) 
               FROM pedidos 
               WHERE empresa_id = ? AND estado_interno = 'Entregado' 
               AND DATE(fecha_entrega) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_ventas);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$total_ventas = $stmt->fetchColumn();

// B. COSTO DE VENTA (COGS - Costo del producto entregado)
$sql_cogs = "SELECT COALESCE(SUM(d.cantidad * prod.costo_compra), 0) 
             FROM pedidos p
             JOIN pedidos_detalle d ON p.id = d.pedido_id
             JOIN productos prod ON d.producto_id = prod.id
             WHERE p.empresa_id = ? AND p.estado_interno = 'Entregado'
             AND DATE(p.fecha_entrega) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_cogs);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$total_cogs = $stmt->fetchColumn();

// C. COSTOS LOGÍSTICOS (Envíos + Empaque de TODOS los pedidos procesados)
$sql_logistica = "SELECT COALESCE(SUM(costo_envio_real + costo_empaque_real), 0) 
                  FROM pedidos 
                  WHERE empresa_id = ? 
                  AND estado_interno IN ('Entregado', 'Devuelto', 'Rechazado', 'En Ruta')
                  AND DATE(fecha_creacion) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_logistica);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$total_logistica = $stmt->fetchColumn();

// D. GASTOS DE MARKETING (ADS)
$sql_ads = "SELECT COALESCE(SUM(monto), 0) 
            FROM marketing_gasto 
            WHERE empresa_id = ? AND fecha BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_ads);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$total_ads = $stmt->fetchColumn();

// E. GASTOS OPERATIVOS (Nómina, Luz, etc.)
$sql_opex = "SELECT COALESCE(SUM(monto), 0) 
             FROM gastos 
             WHERE empresa_id = ? AND fecha BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_opex);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$total_opex = $stmt->fetchColumn();

// F. [NUEVO] VALOR DEL INVENTARIO ACTUAL (ACTIVOS)
// Multiplica el stock actual por el costo de compra de cada producto
$sql_stock = "SELECT COALESCE(SUM(stock_actual * costo_compra), 0) 
              FROM productos 
              WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_stock);
$stmt->execute([$empresa_id]);
$valor_inventario = $stmt->fetchColumn();

// =========================================================
// 4. CÁLCULOS FINALES
// =========================================================

$ganancia_bruta = $total_ventas - $total_cogs;
$total_gastos   = $total_logistica + $total_ads + $total_opex;
$ganancia_neta  = $ganancia_bruta - $total_gastos;

// Margen Neto %
$margen_neto = ($total_ventas > 0) ? ($ganancia_neta / $total_ventas) * 100 : 0;
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold"><i class="fas fa-balance-scale text-warning me-2"></i> Finanzas & Inventario</h2>
        </div>
        <form class="d-flex gap-2 bg-dark p-2 rounded border border-secondary">
            <input type="hidden" name="ruta" value="finanzas">
            <input type="date" name="f_ini" value="<?php echo $f_ini; ?>" class="form-control form-control-sm bg-black text-white border-secondary">
            <input type="date" name="f_fin" value="<?php echo $f_fin; ?>" class="form-control form-control-sm bg-black text-white border-secondary">
            <button class="btn btn-warning btn-sm fw-bold"><i class="fas fa-filter"></i></button>
        </form>
    </div>

    <div class="alert alert-primary bg-primary bg-opacity-10 border-primary d-flex justify-content-between align-items-center mb-4 shadow-sm">
        <div>
            <h4 class="alert-heading fw-bold mb-1 text-primary"><i class="fas fa-boxes me-2"></i> Valor del Inventario Actual</h4>
            <p class="mb-0 text-white-50 small">Este es el dinero que tienes invertido en mercancía almacenada (Stock x Costo).</p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-white mb-0">RD$ <?php echo number_format($valor_inventario, 2); ?></h2>
            <small class="text-primary fw-bold">CAPITAL INVERTIDO</small>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-dark border-success h-100 shadow-sm">
                <div class="card-body">
                    <small class="text-success text-uppercase fw-bold ls-1">Ingresos Totales</small>
                    <h3 class="text-white fw-bold mt-2">RD$ <?php echo number_format($total_ventas, 2); ?></h3>
                    <small class="text-muted">Ventas Cobradas</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-bold ls-1">Costo Mercancía</small>
                    <h3 class="text-white fw-bold mt-2 text-danger">- RD$ <?php echo number_format($total_cogs, 2); ?></h3>
                    <small class="text-muted">De lo vendido</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-dark border-secondary h-100 shadow-sm">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-bold ls-1">Gastos Operativos</small>
                    <h3 class="text-white fw-bold mt-2 text-danger">- RD$ <?php echo number_format($total_gastos, 2); ?></h3>
                    <small class="text-muted">Ads + Envíos + Oficina</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-dark <?php echo $ganancia_neta >= 0 ? 'border-success' : 'border-danger'; ?> h-100 shadow-sm">
                <div class="card-body">
                    <small class="<?php echo $ganancia_neta >= 0 ? 'text-success' : 'text-danger'; ?> text-uppercase fw-bold ls-1">Ganancia Neta</small>
                    <h3 class="<?php echo $ganancia_neta >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold mt-2">
                        RD$ <?php echo number_format($ganancia_neta, 2); ?>
                    </h3>
                    <span class="badge <?php echo $ganancia_neta >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                        Margen: <?php echo number_format($margen_neto, 1); ?>%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary fw-bold text-white">
                    <i class="fas fa-list-alt me-2"></i> Desglose Detallado
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-striped mb-0 align-middle">
                        <tbody>
                            <tr>
                                <td class="ps-4 text-white"><i class="fas fa-plus text-success me-2"></i> Ventas Brutas (Entregadas)</td>
                                <td class="text-end pe-4 text-success fw-bold">RD$ <?php echo number_format($total_ventas, 2); ?></td>
                            </tr>
                            
                            <tr>
                                <td class="ps-4 text-white-50"><i class="fas fa-minus text-danger me-2"></i> Costo de Ventas (COGS)</td>
                                <td class="text-end pe-4 text-danger">RD$ <?php echo number_format($total_cogs, 2); ?></td>
                            </tr>
                            
                            <tr class="bg-secondary bg-opacity-25">
                                <td class="ps-4 fw-bold text-white">(=) UTILIDAD BRUTA</td>
                                <td class="text-end pe-4 fw-bold text-white">RD$ <?php echo number_format($ganancia_bruta, 2); ?></td>
                            </tr>

                            <tr>
                                <td class="ps-4 text-white-50"><i class="fas fa-truck text-warning me-2"></i> Logística (Envíos + Empaque)</td>
                                <td class="text-end pe-4 text-danger">RD$ <?php echo number_format($total_logistica, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-white-50"><i class="fab fa-facebook text-primary me-2"></i> Marketing (Ads)</td>
                                <td class="text-end pe-4 text-danger">RD$ <?php echo number_format($total_ads, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-white-50"><i class="fas fa-lightbulb text-warning me-2"></i> Gastos Fijos (Luz, Nómina, etc.)</td>
                                <td class="text-end pe-4 text-danger">RD$ <?php echo number_format($total_opex, 2); ?></td>
                            </tr>

                            <tr class="border-top border-white">
                                <td class="ps-4 py-3 fw-bold fs-5 text-white">(=) UTILIDAD NETA</td>
                                <td class="text-end pe-4 py-3 fw-bold fs-4 <?php echo $ganancia_neta >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    RD$ <?php echo number_format($ganancia_neta, 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-dark border-secondary mb-3">
                <div class="card-header border-secondary text-white">Acciones Rápidas</div>
                <div class="card-body d-grid gap-2">
                    <a href="index.php?ruta=finanzas/gastos" class="btn btn-outline-light text-start">
                        <i class="fas fa-receipt me-2 text-warning"></i> Registrar Gasto Operativo
                    </a>
                    <a href="index.php?ruta=finanzas/rentabilidad" class="btn btn-outline-light text-start">
                        <i class="fab fa-facebook me-2 text-primary"></i> Registrar Gasto Ads
                    </a>
                    <a href="index.php?ruta=pedidos" class="btn btn-outline-light text-start">
                        <i class="fas fa-box me-2 text-success"></i> Ver Pedidos
                    </a>
                </div>
            </div>
            
            <div class="alert alert-info bg-dark border-info text-info">
                <small><i class="fas fa-info-circle me-1"></i> El <strong>Valor de Inventario</strong> no se resta de la ganancia, es un activo (dinero en stock).</small>
            </div>
        </div>
    </div>
</div>