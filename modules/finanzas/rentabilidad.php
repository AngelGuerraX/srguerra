<?php
// modules/finanzas/rentabilidad.php
require 'modules/finanzas/logic_rentabilidad.php';
?>

<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold text-white"><i class="fas fa-chart-pie text-warning me-2"></i> Rentabilidad Real & CPA</h2>
        </div>
        
        <form class="d-flex gap-2 bg-dark p-2 rounded border border-secondary">
            <input type="hidden" name="ruta" value="finanzas/rentabilidad">
            <input type="date" name="f_ini" value="<?php echo $f_ini; ?>" class="form-control form-control-sm bg-black text-white border-secondary">
            <input type="date" name="f_fin" value="<?php echo $f_fin; ?>" class="form-control form-control-sm bg-black text-white border-secondary">
            <button class="btn btn-warning btn-sm fw-bold"><i class="fas fa-sync-alt"></i></button>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header border-secondary fw-bold text-info">
                    <i class="fab fa-facebook me-2"></i> Registrar Gasto Ads
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="guardar_ads" value="1">
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-black border-secondary text-muted">$</span>
                            <input type="number" step="0.01" name="monto" class="form-control bg-black text-white border-secondary" placeholder="Monto" required>
                        </div>
                        <div class="input-group mb-2">
                            <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control bg-black text-white border-secondary">
                            <select name="plataforma" class="form-select bg-black text-white border-secondary">
                                <option value="Facebook Ads">Facebook</option>
                                <option value="TikTok Ads">TikTok</option>
                                <option value="Google Ads">Google</option>
                            </select>
                        </div>
                        <button class="btn btn-outline-info w-100 btn-sm">Guardar</button>
                    </form>
                    <div class="mt-3 pt-3 border-top border-secondary text-center">
                        <small class="text-muted">Inversión Total Periodo</small>
                        <h3 class="text-white fw-bold">RD$ <?php echo number_format($G_ads, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-dark border-secondary h-100 text-center py-3">
                <h6 class="text-muted text-uppercase mb-3">Costo por Adquisición (CPA)</h6>
                <div class="row align-items-center h-100">
                    <div class="col-6 border-end border-secondary">
                        <div class="display-6 fw-bold text-white">RD$<?php echo number_format($CPA_marketing, 0); ?></div>
                        <span class="badge bg-secondary">Marketing</span>
                        <small class="d-block text-muted mt-2" style="font-size: 10px;">Facebook Dice</small>
                    </div>
                    <div class="col-6">
                        <div class="display-6 fw-bold text-warning">RD$<?php echo number_format($CPA_real, 0); ?></div>
                        <span class="badge bg-warning text-dark">Real</span>
                        <small class="d-block text-muted mt-2" style="font-size: 10px;">Tu Bolsillo Paga</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-danger bg-opacity-10 border-danger h-100 text-center py-3">
                <h6 class="text-danger text-uppercase mb-1">Dinero "Quemado"</h6>
                <small class="text-danger opacity-75">Ads en fallidos + Envíos perdidos</small>
                <div class="display-5 fw-bold text-danger mt-2">RD$ <?php echo number_format($total_quemado, 0); ?></div>
                <div class="progress mt-3 mx-4" style="height: 6px;">
                    <?php $porc_quemado = ($ventas_cobradas > 0) ? ($total_quemado / $ventas_cobradas) * 100 : 0; ?>
                    <div class="progress-bar bg-danger" style="width: <?php echo min(100, $porc_quemado); ?>%"></div>
                </div>
                <small class="text-muted mt-1"><?php echo number_format($porc_quemado, 1); ?>% de ineficiencia</small>
            </div>
        </div>
    </div>

    <div class="card bg-black border border-secondary mb-4">
        <div class="card-header border-secondary fw-bold text-white">
            <i class="fas fa-calculator me-2"></i> Resultado Neto (Matemática Cerrada)
        </div>
        <div class="card-body p-0">
            <table class="table table-dark table-striped mb-0 align-middle">
                <tbody>
                    <tr>
                        <td class="ps-4 text-success fw-bold">(+) Ventas Cobradas (Real en mano)</td>
                        <td class="text-end pe-4 text-success fs-5">RD$ <?php echo number_format($ventas_cobradas, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-4">(-) Costo de Mercancía (Solo de lo entregado)</td>
                        <td class="text-end pe-4 text-muted">- RD$ <?php echo number_format($COGS_real, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-4">(-) Gasto TOTAL en Ads (Facebook/TikTok)</td>
                        <td class="text-end pe-4 text-muted">- RD$ <?php echo number_format($G_ads, 2); ?></td>
                    </tr>
                    <tr>
                        <td class="ps-4">(-) Gasto TOTAL en Logística (Envíos exitosos + fallidos)</td>
                        <td class="text-end pe-4 text-muted">- RD$ <?php echo number_format($costos_logisticos, 2); ?></td>
                    </tr>
                    <tr class="border-top border-white">
                        <td class="ps-4 py-3 fw-bold fs-5 text-white">(=) GANANCIA NETA REAL</td>
                        <td class="text-end pe-4 py-3 fw-bold fs-4 <?php echo $ganancia_neta >= 0 ? 'text-success' : 'text-danger'; ?>">
                            RD$ <?php echo number_format($ganancia_neta, 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>