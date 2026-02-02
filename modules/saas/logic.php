<?php
// modules/saas/logic.php
// GESTIÓN GLOBAL - VERSIÓN COMPATIBLE CON SIDEBAR

verificar_sesion();

// Seguridad
if ($_SESSION['rol'] !== 'SuperAdmin') {
    die("ACCESO DENEGADO.");
}

// --- PROCESAR FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'crear_empresa') {

    $nombre_empresa = trim($_POST['nombre_empresa']);
    $plan           = $_POST['plan'];
    $nombre_admin   = trim($_POST['nombre_admin']);
    $email_admin    = trim($_POST['email_admin']);
    $password       = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        // 1. Verificar Email
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email_admin]);
        if ($stmt->fetch()) {
            throw new Exception("El correo $email_admin ya existe.");
        }

        // 2. Crear Empresa
        $sql_emp = "INSERT INTO empresas (nombre_comercial, plan_suscripcion, estado, fecha_registro) VALUES (?, ?, 'Activo', NOW())";
        $pdo->prepare($sql_emp)->execute([$nombre_empresa, $plan]);
        $empresa_id = $pdo->lastInsertId();

        // 3. Crear Dueño
        $sql_usr = "INSERT INTO usuarios (empresa_id, nombre_completo, email, password_hash, rol, activo) VALUES (?, ?, ?, ?, 'Admin', 1)";
        $pdo->prepare($sql_usr)->execute([$empresa_id, $nombre_admin, $email_admin, $password]);

        // 4. Crear Almacén
        $pdo->prepare("INSERT INTO almacenes (empresa_id, nombre, activo) VALUES (?, 'Almacén Principal', 1)")->execute([$empresa_id]);

        // 5. Crear Transportadoras Básicas
        $sql_trans = "INSERT INTO transportadoras (empresa_id, nombre, costo_envio_fijo, activo) VALUES (?, 'Metro Pac', 250, 1), (?, 'Vimenpaq', 200, 1)";
        $pdo->prepare($sql_trans)->execute([$empresa_id, $empresa_id]);

        $pdo->commit();

        // ✅ ÉXITO: Redirigir con JS
        redirigir("index.php?ruta=saas&msg=empresa_creada");

    } catch (Exception $e) {
        $pdo->rollBack();
        // ❌ ERROR: Redirigir con JS
        redirigir("index.php?ruta=saas&error=" . urlencode($e->getMessage()));
    }
}

// --- ACCIÓN SUSPENDER ---
if (isset($_GET['action']) && $_GET['action'] == 'toggle_estado') {
    $id_emp = $_GET['id'];
    $pdo->prepare("UPDATE empresas SET estado = IF(estado='Activo','Suspendido','Activo') WHERE id = ?")->execute([$id_emp]);
    
    // ✅ Redirigir con JS
    redirigir("index.php?ruta=saas");
}

// ==========================================
// FUNCIÓN MÁGICA PARA REDIRIGIR CON HTML
// ==========================================
function redirigir($url) {
    // Si ya se enviaron headers (el menú ya salió), usamos JS.
    echo '<script type="text/javascript">';
    echo 'window.location.href="' . $url . '";';
    echo '</script>';
    echo '<noscript>';
    echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
    echo '</noscript>';
    exit();
}
?>