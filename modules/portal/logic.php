<?php
// modules/portal/logic.php
// LÓGICA DEL PORTAL DE CONDUCTORES (APP MÓVIL)

if (session_status() === PHP_SESSION_NONE) session_start();

// Conexión a la BDD (Ajusta la ruta si tu archivo db.php está en otro lado)
if (!isset($pdo)) {
    if (file_exists('../../config/db.php')) require_once '../../config/db.php';
}

// 1. LOGIN DE CONDUCTOR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'login_conductor') {
    $codigo = strtoupper(trim($_POST['codigo']));
    $pin    = trim($_POST['pin']);

    $stmt = $pdo->prepare("SELECT * FROM transportadoras WHERE codigo_acceso = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$codigo]);
    $chofer = $stmt->fetch();

    if ($chofer && password_verify($pin, $chofer['pin_acceso'])) {
        $_SESSION['usuario_id'] = 'CHOFER-' . $chofer['id'];
        $_SESSION['transportadora_id'] = $chofer['id'];
        $_SESSION['rol'] = 'Conductor';
        $_SESSION['nombre'] = $chofer['nombre'];
        $_SESSION['empresa_id'] = $chofer['empresa_id'];
        header("Location: index.php?ruta=portal/dashboard");
    } else {
        header("Location: index.php?ruta=portal/login&error=true");
    }
    exit();
}

// VALIDAR SESIÓN PARA EL RESTO
if (!isset($_SESSION['transportadora_id'])) {
    header("Location: index.php?ruta=portal/login");
    exit();
}

// 2. LOGOUT
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php?ruta=portal/login");
    exit();
}

// 3. MARCAR ENTREGADO
if ($_POST['action'] == 'marcar_entregado') {
    $pedido_id = $_POST['pedido_id'];
    $comentario = $_POST['comentario'] ?? '';
    
    // Guardamos fecha de entrega y comentario
    $stmt = $pdo->prepare("UPDATE pedidos SET estado_interno = 'Entregado', fecha_entrega = NOW(), notas_internas = CONCAT(COALESCE(notas_internas, ''), ' | ', ?) WHERE id = ?");
    $stmt->execute([$comentario, $pedido_id]);
    
    header("Location: index.php?ruta=portal/dashboard&msg=entregado");
    exit();
}

// 4. MARCAR RECHAZADO (SOLO CAMBIA ESTADO, EL STOCK SE QUEDA CON EL CHOFER)
if ($_POST['action'] == 'marcar_rechazado') {
    $pedido_id = $_POST['pedido_id'];
    $motivo = $_POST['motivo'];
    $comentario = $_POST['comentario'] ?? '';

    try {
        // Solo cambiamos el estado a 'Rechazado' y guardamos el motivo.
        // NO movemos inventario aquí. El inventario se mueve cuando el Admin lo marca como 'Devuelto'.
        $sql = "UPDATE pedidos 
                SET estado_interno = 'Rechazado', 
                    motivo_rechazo = ?, 
                    fecha_actualizacion = NOW(),
                    notas_internas = CONCAT(COALESCE(notas_internas, ''), ' | ', ?) 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motivo, $comentario, $pedido_id]);

        header("Location: index.php?ruta=portal/dashboard&msg=rechazado");
        exit();

    } catch (Exception $e) {
        die("Error al reportar rechazo: " . $e->getMessage());
    }
}

// Default
header("Location: index.php?ruta=portal/dashboard");
?>