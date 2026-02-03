<?php
// modules/finanzas/gastos.php
$empresa_id = $_SESSION['empresa_id'];

// Obtener últimos 10 gastos
$gastos = $pdo->query("SELECT * FROM gastos WHERE empresa_id = $empresa_id ORDER BY fecha DESC LIMIT 10")->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card bg-dark border-secondary shadow-lg mb-4">
            <div class="card-header border-secondary text-white fw-bold">
                <i class="fas fa-file-invoice-dollar me-2 text-warning"></i> Registrar Nuevo Gasto
            </div>
            <div class="card-body">
                <form action="index.php?ruta=finanzas/logic" method="POST">
                    <input type="hidden" name="action" value="guardar_gasto">
                    
                    <div class="mb-3">
                        <label class="text-white small">Descripción</label>
                        <input type="text" name="descripcion" class="form-control bg-black text-white border-secondary" required placeholder="Ej: Pago de Internet Claro">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="text-white small">Monto (RD$)</label>
                            <input type="number" step="0.01" name="monto" class="form-control bg-black text-white border-secondary text-end fw-bold" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-white small">Fecha</label>
                            <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control bg-black text-white border-secondary">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-white small">Categoría</label>
                        <select name="categoria" class="form-select bg-dark text-white border-secondary">
                            <option value="General">General</option>
                            <option value="Publicidad">Publicidad (Ads)</option>
                            <option value="Nomina">Nómina / Empleados</option>
                            <option value="Servicios">Servicios (Luz/Agua/Net)</option>
                            <option value="Transporte">Gasolina / Transporte</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold">Guardar Gasto</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card bg-dark border-secondary">
            <div class="card-header border-secondary text-white">Últimos Gastos Registrados</div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 small">
                    <thead>
                        <tr class="text-muted">
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th class="text-end">Monto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($gastos as $g): ?>
                        <tr>
                            <td><?php echo date('d/m', strtotime($g['fecha'])); ?></td>
                            <td><?php echo $g['descripcion']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $g['categoria']; ?></span></td>
                            <td class="text-end text-danger fw-bold">- RD$ <?php echo number_format($g['monto'], 2); ?></td>
                            <td class="text-end">
                                <a href="index.php?ruta=finanzas/logic&action=borrar_gasto&id=<?php echo $g['id']; ?>" class="text-muted hover-danger" onclick="return confirm('¿Borrar?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="index.php?ruta=finanzas" class="btn btn-outline-light btn-sm">Ver Reporte Financiero Completo</a>
        </div>
    </div>
</div>