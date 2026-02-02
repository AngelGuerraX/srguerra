<?php
// modules/finanzas/gastos.php
require 'modules/finanzas/logic.php';

// Consultar últimos gastos
$stmt = $pdo->prepare("SELECT * FROM finanzas_gastos WHERE empresa_id = ? ORDER BY fecha DESC LIMIT 50");
$stmt->execute([$empresa_id]);
$lista_gastos = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white fw-bold">Gastos y Publicidad</h2>
            <a href="index.php?ruta=finanzas" class="text-muted text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Volver al Dashboard</a>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGasto">
            <i class="fas fa-plus me-2"></i>Nuevo Gasto
        </button>
    </div>

    <div class="card bg-dark border-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-secondary text-uppercase small">
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lista_gastos)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No hay gastos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($lista_gastos as $g): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($g['fecha'])); ?></td>
                                <td>
                                    <?php 
                                    $badge = 'bg-secondary';
                                    if($g['categoria']=='Publicidad') $badge='bg-info text-dark';
                                    if($g['categoria']=='Nomina') $badge='bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $g['categoria']; ?></span>
                                </td>
                                <td><?php echo $g['descripcion']; ?></td>
                                <td class="text-end fw-bold text-danger">RD$ <?php echo number_format($g['monto'], 2); ?></td>
                                <td class="text-center">
                                    <a href="index.php?ruta=finanzas/logic&action=eliminar_gasto&id=<?php echo $g['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('¿Eliminar este gasto?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Registrar Gasto / Inversión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?ruta=finanzas/logic" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="guardar_gasto">
                    
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select bg-dark text-white border-secondary" required>
                            <option value="Publicidad">Publicidad (FB/TikTok Ads)</option>
                            <option value="Nomina">Nómina / Sueldos</option>
                            <option value="Servicios">Servicios (Luz, Internet)</option>
                            <option value="Software">Software (Shopify, Apps)</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Campaña Black Friday" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Monto (RD$)</label>
                        <input type="number" step="0.01" name="monto" class="form-control bg-dark text-white border-secondary" placeholder="0.00" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Gasto</button>
                </div>
            </form>
        </div>
    </div>
</div>