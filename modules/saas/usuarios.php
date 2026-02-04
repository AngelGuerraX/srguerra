<?php
// modules/saas/usuarios.php
verificar_sesion();

// Validar que venga el ID de la empresa
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location='index.php?ruta=saas/index';</script>";
    exit();
}

$empresa_id = $_GET['id'];

// 1. Obtener datos de la empresa (Para el título)
$stmt = $pdo->prepare("SELECT nombre_comercial FROM empresas WHERE id = ?");
$stmt->execute([$empresa_id]);
$empresa = $stmt->fetch();

// 2. Obtener usuarios de ESTA empresa
$stmt_users = $pdo->prepare("SELECT * FROM usuarios WHERE empresa_id = ? ORDER BY id DESC");
$stmt_users->execute([$empresa_id]);
$usuarios = $stmt_users->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="index.php?ruta=saas/index" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left"></i> Volver a Empresas</a>
            <h2 class="text-white fw-bold mt-1">Usuarios de: <span class="text-warning"><?php echo $empresa['nombre_comercial']; ?></span></h2>
        </div>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
            <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
        </button>
    </div>

    <div class="card bg-dark border-secondary">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="bg-secondary text-uppercase small text-muted">
                    <tr>
                        <th>Nombre</th>
                        <th>Email / Login</th>
                        <th>Rol / Nivel</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-white"><?php echo $u['nombre_completo']; ?></div>
                        </td>
                        <td class="text-muted"><?php echo $u['email']; ?></td>
                        <td>
                            <?php 
                                $badge = 'bg-secondary';
                                if($u['rol'] == 'Admin') $badge = 'bg-warning text-dark';
                                if($u['rol'] == 'Vendedor') $badge = 'bg-info text-dark';
                                if($u['rol'] == 'Almacen') $badge = 'bg-success';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo $u['rol']; ?></span>
                        </td>
                        <td>
                            <?php if($u['activo']): ?>
                                <span class="badge bg-success dot">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger dot">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="index.php?ruta=saas/logic&action=borrar_usuario&id=<?php echo $u['id']; ?>&empresa_id=<?php echo $empresa_id; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('¿Eliminar este usuario?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Agregar Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?ruta=saas/logic" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear_usuario_empresa">
                    <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
                    
                    <div class="mb-3">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" required>
                    </div>

                    <div class="mb-3">
                        <label>Email (Usuario)</label>
                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required>
                    </div>

                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                    </div>

                    <div class="mb-3">
                        <label class="text-warning">Nivel de Acceso (Rol)</label>
                        <select name="rol" class="form-select bg-dark text-white border-secondary">
                            <option value="Admin">Admin (Control Total)</option>
                            <option value="Vendedor">Vendedor (Solo Pedidos/Clientes)</option>
                            <option value="Almacen">Almacén (Solo Inventario/Logística)</option>
                            <option value="Mensajero">Mensajero (Solo Rutas)</option>
                        </select>
                        
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>