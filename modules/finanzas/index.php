<?php
// modules/finanzas/index.php
// VISTA PRINCIPAL DE FINANZAS (P&L)

// Cargamos el cerebro matemático
require 'modules/finanzas/logic.php';
?>

<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="h-label text-neon">INTELIGENCIA FINANCIERA</span>
            <h2 class="fw-bold text-white">Estado de Resultados</h2>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">Periodo Seleccionado</small>
            <span class="text-white fw-bold"><?php echo date('d/m/Y', strtotime($f_inicio)); ?> - <?php echo date('d/m/Y', strtotime($f_fin)); ?></span>
        </div>
    </div>

    <div class="card bg-dark border-secondary mb-4 shadow-lg">
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
                        <option value="">-- Global --</option>
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
                        <option value="">-- Global --</option>
                        <?php foreach($almacenes as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo $id_alm == $a['id'] ? 'selected' : ''; ?>>
                                <?php echo $a['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-filter me-1"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        
        <div class="col-md-3">
            <div class="card bg-dark text-white border-secondary h-100 position-relative overflow-hidden">
                <div class="card-body position-relative z-1">
                    <h6 class="text-muted text-uppercase small">Ventas Totales (Cobradas)</h6>
                    <h3 class="fw-bold text-success mb-0">RD$ <?php echo number_format($total_ingresos, 0); ?></h3>
                    <small class="text-white-50" style="font-size: 11px;">Solo pedidos entregados</small>
                </div>
                <i class="fas fa-cash-register position-absolute top-50 end-0 translate-middle-y me-3 text-secondary opacity-10 fa-3x"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-dark text-white border-secondary h-100 position-relative overflow-hidden">
                <div class="card-body position-relative z-1">
                    <h6 class="text-muted text-uppercase small">Costos Logísticos</h6>
                    <h3 class="fw-bold text-warning mb-0">RD$ <?php echo number_format($total_envios, 0); ?></h3>
                    <small class="text-white-50" style="font-size: 11px;">Envíos + Empaques Realizados</small>
                </div>
                <i class="fas fa-truck position-absolute top-50 end-0 translate-middle-y me-3 text-secondary opacity-10 fa-3x"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-dark text-white border-secondary h-100 position-relative overflow-hidden">
                <div class="card-body position-relative z-1">
                    <h6 class="text-info text-uppercase small"><i class="fas fa-boxes me-1"></i> Valor Inventario (Hoy)</h6>
                    <h3 class="fw-bold text-white mb-0">RD$ <?php echo number_format($valor_inventario, 0); ?></h3>
                    <small class="text-white-50" style="font-size: 11px;">Dinero parado en almacén</small>
                </div>
                <i class="fas fa-warehouse position-absolute top-50 end-0 translate-middle-y me-3 text-secondary opacity-10 fa-3x"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card <?php echo $resultado_operativo >= 0 ? 'bg-success' : 'bg-danger'; ?> bg-opacity-10 text-white border-<?php echo $resultado_operativo >= 0 ? 'success' : 'danger'; ?> h-100 position-relative overflow-hidden">
                <div class="card-body position-relative z-1">
                    <h6 class="<?php echo $resultado_operativo >= 0 ? 'text-success' : 'text-danger'; ?> text-uppercase small fw-bold"><?php echo $titulo_resultado; ?></h6>
                    <h3 class="fw-bold mb-0">RD$ <?php echo number_format($resultado_operativo, 0); ?></h3>
                    <?php if($es_filtro_especifico): ?>
                        <small class="text-white-50" style="font-size: 11px;">*Sin descontar gastos fijos globales</small>
                    <?php else: ?>
                        <small class="text-white-50" style="font-size: 11px;">Bolsillo Real (Neta)</small>
                    <?php endif; ?>
                </div>
                <i class="fas fa-chart-line position-absolute top-50 end-0 translate-middle-y me-3 opacity-25 fa-3x"></i>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-md-8">
            <div class="card bg-dark text-white border-secondary mb-4">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center bg-black bg-opacity-25">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list-alt me-2"></i> Desglose Financiero</h5>
                    <?php if($es_filtro_especifico): ?>
                        <span class="badge bg-info text-dark">Vista Filtrada</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <tbody>
                            <tr>
                                <td class="ps-4 border-secondary py-3"> 
                                    <span class="text-success fw-bold">(+)</span> Ventas Brutas 
                                    <small class="text-muted d-block">Pedidos entregados y cobrados</small>
                                </td>
                                <td class="text-end pe-4 border-secondary fs-5 text-success">RD$ <?php echo number_format($total_ingresos, 2); ?></td>
                            </tr>

                            <tr>
                                <td class="ps-4 border-secondary py-3"> 
                                    <span class="text-danger fw-bold">(-)</span> Costo de Mercancía (COGS)
                                    <small class="text-muted d-block">Lo que te costó comprar esos productos</small>
                                </td>
                                <td class="text-end pe-4 border-secondary text-danger">- RD$ <?php echo number_format($total_cogs, 2); ?></td>
                            </tr>

                            <tr class="bg-secondary bg-opacity-10">
                                <td class="ps-4 border-secondary py-3 fw-bold text-white"> 
                                    (=) Ganancia Bruta (Producto)
                                </td>
                                <td class="text-end pe-4 border-secondary fw-bold text-white">RD$ <?php echo number_format($ganancia_bruta, 2); ?></td>
                            </tr>

                            <tr>
                                <td class="ps-4 border-secondary py-3"> 
                                    <span class="text-danger fw-bold">(-)</span> Logística y Envíos
                                    <small class="text-muted d-block">Pago a transportadoras + Empaque</small>
                                </td>
                                <td class="text-end pe-4 border-secondary text-danger">- RD$ <?php echo number_format($total_envios, 2); ?></td>
                            </tr>
                            
                            <?php if(!$es_filtro_especifico): ?>
                                <tr>
                                    <td class="ps-4 border-secondary py-3"> 
                                        <span class="text-danger fw-bold">(-)</span> Publicidad y Gastos Operativos
                                        <small class="text-muted d-block">Ads, Nómina, Luz, Internet (Prorrateado)</small>
                                    </td>
                                    <td class="text-end pe-4 border-secondary text-danger">- RD$ <?php echo number_format($total_gastos, 2); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td class="ps-4 border-secondary py-3 text-muted fst-italic"> 
                                        (i) Publicidad Global
                                        <small class="d-block">No se resta en vista filtrada</small>
                                    </td>
                                    <td class="text-end pe-4 border-secondary text-muted fst-italic">RD$ <?php echo number_format($total_gastos, 2); ?></td>
                                </tr>
                            <?php endif; ?>

                            <tr class="border-top border-white fa-lg bg-gradient <?php echo $resultado_operativo >= 0 ? 'bg-success' : 'bg-danger'; ?>" style="--bs-bg-opacity: .1;">
                                <td class="ps-4 py-4 fw-bold text-white"> 
                                    (=) <?php echo $titulo_resultado; ?>
                                </td>
                                <td class="text-end pe-4 py-4 fw-bold <?php echo $resultado_operativo >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    RD$ <?php echo number_format($resultado_operativo, 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            
            <div class="card bg-dark text-white border-secondary mb-4">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <span class="display-4 text-info"><i class="fas fa-file-invoice-dollar"></i></span>
                    </div>
                    <h5 class="card-title text-info fw-bold">Registro de Gastos</h5>
                    <p class="card-text small text-muted mb-4">
                        Para que el resultado sea real, debes registrar todos tus gastos operativos (Facebook Ads, Nómina, Servicios).
                    </p>
                    <a href="index.php?ruta=finanzas/gastos" class="btn btn-outline-info w-100 fw-bold rounded-pill">
                        <i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Gasto
                    </a>
                </div>
            </div>
            
            <div class="alert alert-dark border-secondary d-flex" role="alert">
                <i class="fas fa-info-circle fa-2x me-3 text-secondary mt-1"></i>
                <div class="small text-muted">
                    <strong class="text-white">Nota sobre el Inventario:</strong><br>
                    El "Valor Inventario" es una foto instantánea de lo que tienes en bodega HOY. Se calcula multiplicando tu 
                    <code>Stock Actual</code> x <code>Costo Compra</code>. No depende del rango de fechas seleccionado.
                </div>
            </div>

        </div>
    </div>
</div>