<?php
// modules/finanzas/index.php
require 'modules/finanzas/logic.php';
?>

<div class="container-fluid">
    
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body py-3">
            <form action="index.php" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="ruta" value="finanzas">
                
                <div class="col-md-2">
                    <label class="text-muted small">Desde</label>
                    <input type="date" name="f_inicio" value="<?php echo $f_inicio; ?>" class="form-control form-control-sm bg-secondary text-white border-0">
                </div>
                
                <div class="col-md-2">
                    <label class="text-muted small">Hasta</label>
                    <input type="date" name="f_fin" value="<?php echo $f_fin; ?>" class="form-control form-control-sm bg-secondary text-white border-0">
                </div>

                <div class="col-md-3">
                    <label class="text-muted small">Transportadora</label>
                    <select name="id_trans" class="form-select form-select-sm bg-secondary text-white border-0">
                        <option value="">Todas</option>
                        <?php foreach($transportadoras as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo $id_trans == $t['id'] ? 'selected' : ''; ?>>
                                <?php echo $t['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="text-muted small">Almacén</label>
                    <select name="id_alm" class="form-select form-select-sm bg-secondary text-white border-0">
                        <option value="">Todos</option>
                        <?php foreach($almacenes as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo $id_alm == $a['id'] ? 'selected' : ''; ?>>
                                <?php echo $a['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        
        <div class="col-md-3">
            <div class="card bg-dark text-white border-secondary h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Ingresos (Cobradas)</h6>
                    <h3 class="fw-bold text-success">RD$ <?php echo number_format($total_ingresos, 2); ?></h3>
                    <small class="text-muted">Pedidos Entregados</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-dark text-white border-secondary h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Costos Logísticos</h6>
                    <h3 class="fw-bold text-warning">RD$ <?php echo number_format($total_envios, 2); ?></h3>
                    <small class="text-muted">Envíos + Empaques</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-dark text-white border-secondary h-100">
                <div class="card-body">
                    <h6 class="text-info text-uppercase small"><i class="fas fa-boxes me-1"></i> Valor Inventario</h6>
                    <h3 class="fw-bold text-white">RD$ <?php echo number_format($valor_inventario, 2); ?></h3>
                    <small class="text-muted">Dinero invertido en stock</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card <?php echo $resultado_operativo >= 0 ? 'bg-success' : 'bg-danger'; ?> text-white h-100">
                <div class="card-body">
                    <h6 class="text-white-50 text-uppercase small"><?php echo $titulo_resultado; ?></h6>
                    <h3 class="fw-bold">RD$ <?php echo number_format($resultado_operativo, 2); ?></h3>
                    <?php if($es_filtro_especifico): ?>
                        <small class="text-white-50">*Sin descontar Ads globales</small>
                    <?php else: ?>
                        <small class="text-white-50">Ganancia Neta Real</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary d-flex justify-content-between">
                    <h5 class="mb-0">Estado de Resultados Detallado</h5>
                    <?php if($es_filtro_especifico): ?>
                        <span class="badge bg-info text-dark">Filtrado Activado</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="ps-4"> (+) Ventas Totales (Entregadas)</td>
                                <td class="text-end pe-4 text-success">RD$ <?php echo number_format($total_ingresos, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="ps-4"> (-) Costo de Mercancía (COGS)</td>
                                <td class="text-end pe-4 text-danger">- RD$ <?php echo number_format($total_cogs, 2); ?></td>
                            </tr>
                            <tr class="fw-bold bg-secondary" style="--bs-bg-opacity: .1;">
                                <td class="ps-4"> = Ganancia Bruta (Producto)</td>
                                <td class="text-end pe-4">RD$ <?php echo number_format($ganancia_bruta, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="ps-4"> (-) Envíos y Logística</td>
                                <td class="text-end pe-4 text-danger">- RD$ <?php echo number_format($total_envios, 2); ?></td>
                            </tr>
                            
                            <?php if(!$es_filtro_especifico): ?>
                                <tr>
                                    <td class="ps-4"> (-) Publicidad y Gastos Fijos</td>
                                    <td class="text-end pe-4 text-danger">- RD$ <?php echo number_format($total_gastos, 2); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td class="ps-4 text-muted fst-italic"> (i) Publicidad Global (No restada en filtro)</td>
                                    <td class="text-end pe-4 text-muted fst-italic">RD$ <?php echo number_format($total_gastos, 2); ?></td>
                                </tr>
                            <?php endif; ?>

                            <tr class="fw-bold border-top border-white fa-lg">
                                <td class="ps-4 py-3"> = <?php echo $titulo_resultado; ?></td>
                                <td class="text-end pe-4 py-3 <?php echo $resultado_operativo >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    RD$ <?php echo number_format($resultado_operativo, 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-dark text-white border-secondary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title text-info">Gestión de Gastos</h5>
                    <p class="card-text small text-muted">Registra Facebook Ads, Pagos de Nómina y Servicios.</p>
                    <a href="index.php?ruta=finanzas/gastos" class="btn btn-outline-info w-100">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Registrar Nuevo Gasto
                    </a>
                </div>
            </div>
            
            <div class="alert alert-dark border-secondary d-flex align-items-center" role="alert">
                <i class="fas fa-info-circle fa-2x me-3 text-secondary"></i>
                <div class="small text-muted">
                    El "Valor Inventario" muestra el costo total de compra de todos los productos que tienes actualmente en tus almacenes (no depende del rango de fechas).
                </div>
            </div>
        </div>
    </div>
</div>