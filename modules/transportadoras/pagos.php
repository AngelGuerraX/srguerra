<?php
// modules/transportadoras/pagos.php
// PANEL DE LIQUIDACIONES (SOPORTE PARA PÚBLICAS Y PRIVADAS)

// 1. LÓGICA DE PAGO (PROCESAR FORMULARIO)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'pagar') {
    $trans_id = $_POST['transportadora_id'];
    $monto    = $_POST['monto'];
    $ref      = $_POST['referencia'];
    $nota     = $_POST['nota'];
    $mi_empresa_id = $_SESSION['empresa_id']; // Importante para saber quién paga

    // Validar que el monto sea positivo
    if ($monto > 0) {
        // Insertamos el pago registrando la empresa_id origen
        $stmt = $pdo->prepare("INSERT INTO transportadoras_pagos (transportadora_id, empresa_id, monto, referencia, nota, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$trans_id, $mi_empresa_id, $monto, $ref, $nota]);
        $msg = "Pago registrado correctamente.";
    }
}

// 2. CONSULTA MAESTRA (CALCULADORA DE DEUDA POR EMPRESA)
// Calcula cuánto le debe MI EMPRESA a cada chofer (Privado o Público)
$sql = "SELECT t.id, t.nombre, t.es_publica,
        
        -- A. Cuánto ha ganado entregando MIS paquetes (Solo mi empresa_id)
        COALESCE((SELECT COUNT(*) * t.costo_envio_fijo 
         FROM pedidos 
         WHERE transportadora_id = t.id 
         AND empresa_id = ?  /* <--- FILTRO CLAVE: Solo mis pedidos */
         AND estado_interno = 'Entregado'), 0) as total_ganado,
        
        -- B. Cuánto le he pagado YO (Solo mi empresa_id)
        COALESCE((SELECT SUM(monto) 
         FROM transportadoras_pagos 
         WHERE transportadora_id = t.id 
         AND empresa_id = ? /* <--- FILTRO CLAVE: Solo mis pagos */
        ), 0) as total_pagado

        FROM transportadoras t
        WHERE (t.empresa_id = ? OR t.es_publica = 1) 
        AND t.activo = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['empresa_id'], $_SESSION['empresa_id'], $_SESSION['empresa_id']]);
$choferes = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="h-label text-success">FINANZAS</span>
            <h2 class="text-white fw-bold">Liquidación de Choferes</h2>
            <p class="text-muted small">Gestiona pagos a flotas propias y externas.</p>
        </div>
        
        <?php 
            $deuda_total_global = 0;
            foreach($choferes as $c) {
                // Solo sumar si hay deuda positiva
                $d = $c['total_ganado'] - $c['total_pagado'];
                if($d > 0) $deuda_total_global += $d;
            }
        ?>
        <div class="card bg-danger bg-opacity-25 border-danger text-center px-4 py-2">
            <small class="text-danger fw-bold">DEUDA TOTAL</small>
            <h3 class="text-white fw-bold m-0">RD$ <?php echo number_format($deuda_total_global, 2); ?></h3>
        </div>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success fw-bold"><i class="fas fa-check-circle me-2"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php foreach($choferes as $c): ?>
            <?php 
                $pendiente = $c['total_ganado'] - $c['total_pagado']; 
                
                // Si no hay actividad con este chofer público, lo saltamos para limpiar la vista
                if ($c['es_publica'] == 1 && $c['total_ganado'] == 0 && $pendiente == 0) continue;

                $color = ($pendiente > 0) ? 'text-warning' : 'text-success';
                $borde = ($pendiente > 0) ? 'border-warning' : 'border-success';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card bg-dark <?php echo $borde; ?> h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-white mb-0">
                                    <?php if($c['es_publica']): ?>
                                        <span class="badge bg-info text-dark me-1" title="Transportadora Pública"><i class="fas fa-globe"></i></span>
                                    <?php endif; ?>
                                    <?php echo $c['nombre']; ?>
                                </h5>
                                <small class="text-muted">ID: #<?php echo $c['id']; ?></small>
                            </div>
                            <div class="bg-black bg-opacity-50 p-2 rounded text-center" style="min-width: 80px;">
                                <small class="text-muted d-block" style="font-size: 10px;">SALDO PENDIENTE</small>
                                <span class="<?php echo $color; ?> fw-bold fs-5">RD$ <?php echo number_format($pendiente); ?></span>
                            </div>
                        </div>

                        <div class="progress bg-black mb-3" style="height: 6px;">
                            <?php 
                                $porcentaje_pagado = ($c['total_ganado'] > 0) ? ($c['total_pagado'] / $c['total_ganado']) * 100 : 100; 
                            ?>
                            <div class="progress-bar bg-success" style="width: <?php echo $porcentaje_pagado; ?>%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted small mb-3">
                            <span>Ganado: <strong><?php echo number_format($c['total_ganado']); ?></strong></span>
                            <span>Pagado: <strong><?php echo number_format($c['total_pagado']); ?></strong></span>
                        </div>

                        <?php if($pendiente > 0): ?>
                            <button class="btn btn-warning w-100 fw-bold text-dark" 
                                    onclick="abrirModalPago(<?php echo $c['id']; ?>, '<?php echo $c['nombre']; ?>', <?php echo $pendiente; ?>)">
                                <i class="fas fa-money-bill-wave me-2"></i> REGISTRAR PAGO
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-success w-100" disabled>
                                <i class="fas fa-check me-2"></i> AL DÍA
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Liquidar a <span id="nombreChofer" class="text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="pagar">
                    <input type="hidden" name="transportadora_id" id="idChofer">
                    
                    <div class="alert alert-info bg-dark border-info text-info small">
                        Deuda actual: <strong id="montoDeuda"></strong>
                    </div>

                    <div class="mb-3">
                        <label>Monto a Pagar (RD$)</label>
                        <input type="number" name="monto" id="inputMonto" class="form-control bg-black text-white border-secondary fw-bold text-center fs-4" required>
                    </div>

                    <div class="mb-3">
                        <label>Referencia (Opcional)</label>
                        <input type="text" name="referencia" class="form-control bg-black text-white border-secondary" placeholder="Ej: Efectivo, Transferencia BHD...">
                    </div>

                    <div class="mb-3">
                        <label>Nota Privada</label>
                        <textarea name="nota" class="form-control bg-black text-white border-secondary" placeholder="Detalle del pago..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">CONFIRMAR PAGO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalPago(id, nombre, deuda) {
    document.getElementById('idChofer').value = id;
    document.getElementById('nombreChofer').innerText = nombre;
    document.getElementById('montoDeuda').innerText = 'RD$ ' + new Intl.NumberFormat().format(deuda);
    document.getElementById('inputMonto').value = deuda; // Sugerir pagar todo
    
    var myModal = new bootstrap.Modal(document.getElementById('modalPago'));
    myModal.show();
}
</script>