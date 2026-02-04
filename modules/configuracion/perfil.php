<?php
// modules/configuracion/perfil.php
// GESTIÓN DE PERFIL DE USUARIO Y DATOS DE EMPRESA

// 1. SEGURIDAD
if (!isset($_SESSION['usuario_id'])) return;

$usuario_id = $_SESSION['usuario_id'];
$empresa_id = $_SESSION['empresa_id'];
$msg = '';
$error = '';

// =========================================================
// 2. LÓGICA DE GUARDADO
// =========================================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- A. ACTUALIZAR USUARIO ---
    if ($_POST['action'] == 'update_usuario') {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $pass_new = $_POST['password_new'];

        try {
            // Si hay contraseña nueva, la encriptamos
            if (!empty($pass_new)) {
                $hash = password_hash($pass_new, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, password_hash = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$nombre, $email, $hash, $usuario_id]);
            } else {
                // Si no, solo actualizamos datos
                $sql = "UPDATE usuarios SET nombre_completo = ?, email = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$nombre, $email, $usuario_id]);
            }
            
            // Actualizar sesión
            $_SESSION['usuario_nombre'] = $nombre;
            $msg = "Usuario actualizado correctamente.";

        } catch (Exception $e) {
            $error = "Error al actualizar usuario: " . $e->getMessage();
        }
    }

    // --- B. ACTUALIZAR EMPRESA ---
    if ($_POST['action'] == 'update_empresa') {
        $nombre_com = $_POST['nombre_comercial'];
        $rnc = $_POST['rnc'];
        $tel = $_POST['telefono'];
        $dir = $_POST['direccion'];
        $shopify = $_POST['shopify_secret'];

        // Subida de Logo
        $logo_sql = "";
        $params = [$nombre_com, $rnc, $tel, $dir, $shopify];

        if (!empty($_FILES['logo']['name'])) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = "logo_" . $empresa_id . "_" . time() . "." . $ext;
            $ruta_destino = "assets/uploads/" . $nombre_archivo;
            
            // Crear carpeta si no existe
            if (!file_exists('assets/uploads')) mkdir('assets/uploads', 0777, true);

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_destino)) {
                // Agregar al SQL
                $logo_sql = ", logo = ?";
                $params[] = $nombre_archivo;
            }
        }

        // Agregar ID al final para el WHERE
        $params[] = $empresa_id;

        try {
            $sql = "UPDATE empresas SET nombre_comercial = ?, rnc = ?, telefono_contacto = ?, direccion = ?, shopify_secret = ? $logo_sql WHERE id = ?";
            $pdo->prepare($sql)->execute($params);
            $msg = "Datos de empresa actualizados.";
        } catch (Exception $e) {
            $error = "Error al actualizar empresa: " . $e->getMessage();
        }
    }
}

// =========================================================
// 3. OBTENER DATOS ACTUALES
// =========================================================
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usr = $stmt->fetch();

$stmt2 = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt2->execute([$empresa_id]);
$emp = $stmt2->fetch();
?>

<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold"><i class="fas fa-cog text-warning me-2"></i> Configuración</h2>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success border-success bg-success bg-opacity-25 text-white fw-bold mb-4">
            <i class="fas fa-check-circle me-2"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger border-danger bg-danger bg-opacity-25 text-white fw-bold mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <div class="col-md-5 mb-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header border-secondary fw-bold text-white">
                    <i class="fas fa-user-circle me-2 text-info"></i> Mi Perfil
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_usuario">
                        
                        <div class="mb-3">
                            <label class="text-white small">Nombre Completo</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usr['nombre_completo']); ?>" class="form-control bg-black text-white border-secondary" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-white small">Correo Electrónico</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($usr['email']); ?>" class="form-control bg-black text-white border-secondary" required>
                        </div>

                        <hr class="border-secondary my-4">
                        <h6 class="text-muted small text-uppercase">Cambiar Contraseña</h6>
                        <p class="text-white-50 small mb-3">Deja este campo vacío si no quieres cambiarla.</p>

                        <div class="mb-3">
                            <label class="text-white small">Nueva Contraseña</label>
                            <input type="password" name="password_new" class="form-control bg-black text-white border-secondary" placeholder="******">
                        </div>

                        <button class="btn btn-info w-100 fw-bold text-dark">Guardar Cambios de Usuario</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7 mb-4">
            <div class="card bg-dark border-secondary h-100">
                <div class="card-header border-secondary fw-bold text-white">
                    <i class="fas fa-building me-2 text-warning"></i> Datos de la Empresa
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_empresa">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="text-white small">Nombre Comercial</label>
                                    <input type="text" name="nombre_comercial" value="<?php echo htmlspecialchars($emp['nombre_comercial']); ?>" class="form-control bg-black text-white border-secondary" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-white small">RNC / ID Fiscal</label>
                                    <input type="text" name="rnc" value="<?php echo htmlspecialchars($emp['rnc'] ?? ''); ?>" class="form-control bg-black text-white border-secondary">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="text-white small">Dirección</label>
                            <input type="text" name="direccion" value="<?php echo htmlspecialchars($emp['direccion'] ?? ''); ?>" class="form-control bg-black text-white border-secondary">
                        </div>

                        <div class="mb-3">
                            <label class="text-white small">Teléfono de Contacto</label>
                            <input type="text" name="telefono" value="<?php echo htmlspecialchars($emp['telefono_contacto'] ?? ''); ?>" class="form-control bg-black text-white border-secondary">
                        </div>

                        <div class="mb-4">
                            <label class="text-white small">Logo de la Empresa</label>
                            <div class="d-flex align-items-center gap-3">
                                <?php if(!empty($emp['logo'])): ?>
                                    <img src="assets/uploads/<?php echo $emp['logo']; ?>" alt="Logo" class="rounded bg-white p-1" style="width: 50px; height: 50px; object-fit: contain;">
                                <?php endif; ?>
                                <input type="file" name="logo" class="form-control bg-black text-white border-secondary">
                            </div>
                        </div>

                        <hr class="border-secondary">

                        <h6 class="text-warning small text-uppercase fw-bold mb-3"><i class="fab fa-shopify me-2"></i> Integración Shopify (Opcional)</h6>
                        <div class="mb-3">
                            <label class="text-white small">Shopify Access Token (Admin API)</label>
                            <input type="password" name="shopify_secret" value="<?php echo htmlspecialchars($emp['shopify_secret'] ?? ''); ?>" class="form-control bg-black text-white border-secondary" placeholder="shpat_xxxxxxxxxxxx">
                            <small class="text-muted" style="font-size: 10px;">Necesario para sincronizar pedidos automáticamente.</small>
                        </div>

                        <button class="btn btn-warning w-100 fw-bold">Guardar Datos de Empresa</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>