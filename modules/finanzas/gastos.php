<?php
// modules/finanzas/gastos.php
$empresa_id = $_SESSION['empresa_id'];
$gastos = $pdo->query("SELECT * FROM gastos WHERE empresa_id = $empresa_id ORDER BY fecha DESC LIMIT 10")->fetchAll();
?>

<div class="container-fluid">
    <h3 class="text-white mb-4">Registro de Gastos Operativos</h3>
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <form action="index.php?ruta=finanzas/logic" method="POST">
                        <input type="hidden" name="action" value="guardar_gasto">
                        <div class="mb-3">
                            <label class="text-white small">Descripción</label>
                            <input type="text" name="descripcion" class="form-control bg-black text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label class="text-white small">Monto (RD$)</label>
                            <input type="number" step="0.01" name="monto" class="form-control bg-black text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label class="text-white small">Fecha</label>
                            <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control bg-black text-white border-secondary">
                        </div>
                        <div class="mb-3">
                            <label class="text-white small">Categoría</label>
                            <select name="categoria" class="form-select bg-dark text-white border-secondary">
                                <option value="General">General</option>
                                <option value="Nomina">Nómina</option>
                                <option value="Servicios">Servicios</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Guardar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card bg-dark border-secondary">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($gastos as $g): ?>
                        <tr>
                            <td><?php echo date('d/m', strtotime($g['fecha'])); ?></td>
                            <td><?php echo $g['descripcion']; ?></td>
                            <td class="text-danger">- RD$ <?php echo number_format($g['monto'], 2); ?></td>
                            <td>
                                <a href="index.php?ruta=finanzas/logic&action=borrar_gasto&id=<?php echo $g['id']; ?>" class="text-muted"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>