<?php
// modules/saas/index.php
verificar_sesion();

// Solo permitir acceso si eres SuperAdmin
if ($_SESSION['rol'] !== 'SuperAdmin') {
    echo "<script>window.location='index.php?ruta=dashboard';</script>";
    exit();
}

// Consultar todas las empresas con conteo de usuarios y pedidos
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM usuarios WHERE empresa_id = e.id) as num_usuarios,
        (SELECT COUNT(*) FROM pedidos WHERE empresa_id = e.id) as num_pedidos,
        (SELECT nombre_completo FROM usuarios WHERE empresa_id = e.id AND rol='Admin' LIMIT 1) as dueno
        FROM empresas e 
        ORDER BY e.id DESC";
$empresas = $pdo->query($sql)->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="h-label text-warning">ADMINISTRACIÓN GLOBAL</span>
            <h2 class="text-white fw-bold">Gestión de Empresas (SaaS)</h2>
        </div>
        <button class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalNuevaEmpresa">
            <i class="fas fa-building me-2"></i> Alta Nueva Empresa
        </button>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 bg-success text-white bg-opacity-75 fade show">Empresa creada y configurada correctamente.</div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger border-0 bg-danger text-white bg-opacity-75 fade show"><?php echo $_GET['error']; ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h6 class="text-muted">Total Empresas</h6>
                    <h2 class="text-white fw-bold"><?php echo count($empresas); ?></h2>
                </div>
            </div>
        </div>
        </div>

    <div class="card bg-dark border-secondary">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="bg-secondary text-uppercase small text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>Dueño (Admin)</th>
                        <th>Plan</th>
                        <th class="text-center">Usuarios</th>
                        <th class="text-center">Pedidos</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($empresas as $e): ?>
                    <tr>
                        <td class="text-muted">#<?php echo $e['id']; ?></td>
                        <td>
                            <div class="fw-bold text-white"><?php echo $e['nombre_comercial']; ?></div>
                            <small class="text-muted">Reg: <?php echo date('d/m/Y', strtotime($e['fecha_registro'])); ?></small>
                        </td>
                        <td class="text-info">
                            <i class="fas fa-user-tie me-1"></i> <?php echo $e['dueno']; ?>
                        </td>
                        <td>
                            <span class="badge bg-dark border border-secondary"><?php echo $e['plan_suscripcion']; ?></span>
                        </td>
                        <td class="text-center"><?php echo $e['num_usuarios']; ?></td>
                        <td class="text-center"><?php echo $e['num_pedidos']; ?></td>
                        <td>
                            <?php if($e['estado'] == 'Activo'): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Suspendido</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if($e['id'] != 1): // No puedes suspender tu propia empresa principal ?>
                                <a href="index.php?ruta=saas/logic&action=toggle_estado&id=<?php echo $e['id']; ?>" 
                                   class="btn btn-sm btn-outline-light" 
                                   title="<?php echo ($e['estado']=='Activo') ? 'Suspender Servicio' : 'Activar Servicio'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaEmpresa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-warning"><i class="fas fa-rocket me-2"></i>Alta de Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?ruta=saas/logic" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear_empresa">
                    
                    <div class="row g-3">
                        <div class="col-md-6 border-end border-secondary">
                            <h6 class="text-muted mb-3">1. Datos del Negocio</h6>
                            
                            <div class="mb-3">
                                <label>Nombre Comercial</label>
                                <input type="text" name="nombre_empresa" class="form-control bg-dark text-white border-secondary" required placeholder="Ej: Tienda XYZ">
                            </div>
                            
                            <div class="mb-3">
                                <label>Plan de Suscripción</label>
                                <select name="plan" class="form-select bg-dark text-white border-secondary">
                                    <option value="Gratis">Gratis (Prueba)</option>
                                    <option value="Pro">Pro (Mensual)</option>
                                    <option value="Enterprise">Enterprise (Anual)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-muted mb-3">2. Datos del Administrador (Dueño)</h6>
                            
                            <div class="mb-3">
                                <label>Nombre Completo</label>
                                <input type="text" name="nombre_admin" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Correo Electrónico (Login)</label>
                                <input type="email" name="email_admin" class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="mb-3">
                                <label>Contraseña Temporal</label>
                                <input type="text" name="password" class="form-control bg-dark text-white border-secondary" required value="123456">
                                <small class="text-muted">El usuario podrá cambiarla después.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Crear Empresa y Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>