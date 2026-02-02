<?php
// modules/pedidos/despacho.php
// PANTALLA DE DESPACHO MASIVO Y CONTROL DE INVENTARIO

// 1. OBTENER ID DE EMPRESA (Seguridad)
if (session_status() === PHP_SESSION_NONE) session_start();
$empresa_id = $_SESSION['empresa_id'] ?? 0;

// 2. CONSULTAR TRANSPORTADORAS ACTIVAS
$stmt_t = $pdo->prepare("SELECT * FROM transportadoras WHERE activo = 1 AND empresa_id = ?");
$stmt_t->execute([$empresa_id]);
$trans = $stmt_t->fetchAll();

// 3. CONSULTAR ALMACENES (Para saber de dónde sale la mercancía)
$stmt_alm = $pdo->prepare("SELECT * FROM almacenes WHERE empresa_id = ? AND activo = 1");
$stmt_alm->execute([$empresa_id]);
$almacenes = $stmt_alm->fetchAll();

// 4. CONSULTAR PEDIDOS PENDIENTES DE ASIGNAR
// Filtros: De esta empresa + Estado Nuevo/Confirmado + Sin Transportadora asignada
$sql = "SELECT p.*, c.nombre as cliente, c.ciudad 
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        WHERE p.empresa_id = ? 
        AND p.estado_interno IN ('Nuevo', 'Confirmado') 
        AND (p.transportadora_id IS NULL OR p.transportadora_id = 0)
        ORDER BY p.fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);
$pedidos = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h3 class="text-white mb-4"><i class="fas fa-dolly me-2"></i> Centro de Despacho</h3>

    <form action="index.php?ruta=pedidos/logic" method="POST" target="_blank" id="formDespacho">
        <input type="hidden" name="action" value="asignar_masivo">

        <div class="card bg-dark border-secondary mb-4 sticky-top" style="top: 20px; z-index: 100; box-shadow: 0 4px 20px rgba(0,0,0,0.6);">
            <div class="card-header border-secondary bg-secondary bg-opacity-10 py-2">
                <small class="text-white-50 fw-bold text-uppercase ls-1">Configuración del Envío</small>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    
                    <div class="col-md-4">
                        <label class="text-info fw-bold small mb-1"><i class="fas fa-truck me-1"></i> Transportadora (Responsable)</label>
                        <select name="transportadora_id" class="form-select bg-dark text-white border-info" required>
                            <option value="">-- Selecciona quién entrega --</option>
                            <?php foreach($trans as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo $t['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="text-warning fw-bold small mb-1"><i class="fas fa-warehouse me-1"></i> Almacén de Origen (Descontar)</label>
                        <select name="almacen_origen_id" class="form-select bg-dark text-white border-warning" required>
                            <?php if(empty($almacenes)): ?>
                                <option value="">Error: Crea un almacén primero</option>
                            <?php else: ?>
                                <?php foreach($almacenes as $a): ?>
                                    <option value="<?php echo $a['id']; ?>"><?php echo $a['nombre']; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> DESPACHAR Y GENERAR HOJA
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="card bg-dark border-secondary">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h6 class="m-0 text-white"><i class="fas fa-list me-2"></i> Pedidos Pendientes</h6>
                <span class="badge bg-secondary"><?php echo count($pedidos); ?> Disponibles</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black bg-opacity-25 text-uppercase small text-muted">
                            <tr>
                                <th class="ps-3 text-center" style="width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="checkAll" style="cursor: pointer;">
                                </th>
                                <th>Orden / Fecha</th>
                                <th>Destino</th>
                                <th>Nota Interna</th>
                                <th class="text-end pe-4">Valor a Cobrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($pedidos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="opacity-50">
                                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                            <h5 class="text-white">¡Todo despachado!</h5>
                                            <p class="text-muted small">No hay pedidos nuevos pendientes de asignar.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach($pedidos as $p): ?>
                            <tr>
                                <td class="ps-3 text-center">
                                    <input type="checkbox" name="pedidos[]" value="<?php echo $p['id']; ?>" class="form-check-input check-item border-secondary fs-5" style="cursor: pointer;">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-25 text-primary rounded p-2 me-3 fw-bold">
                                            #<?php echo $p['numero_orden']; ?>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block"><?php echo date('d/m/Y', strtotime($p['fecha_creacion'])); ?></small>
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25"><?php echo $p['estado_interno']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-info"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $p['ciudad']; ?></div>
                                    <small class="text-white-50"><?php echo $p['cliente']; ?></small>
                                </td>
                                <td>
                                    <?php if(!empty($p['notas_internas'])): ?>
                                        <small class="text-warning"><i class="fas fa-sticky-note me-1"></i> <?php echo substr($p['notas_internas'], 0, 50); ?>...</small>
                                    <?php else: ?>
                                        <span class="text-muted opacity-25 small">---</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold text-success fs-5">RD$ <?php echo number_format($p['total_venta'], 0); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if(!empty($pedidos)): ?>
            <div class="card-footer border-secondary text-muted small">
                <i class="fas fa-info-circle me-1"></i> Selecciona los pedidos y haz clic en "Despachar" para moverlos a estado "En Ruta" y descontar el inventario.
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    document.getElementById('checkAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.check-item');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>