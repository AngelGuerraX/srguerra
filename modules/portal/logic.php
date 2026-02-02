<?php
// modules/portal/logic.php
// LÓGICA DEL PORTAL DE CONDUCTORES

// Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) session_start();

// Conexión BDD (Si se llama directo)
if (!isset($pdo)) require_once 'config/db.php'; 

// =============================================================================
// LOGIN DE CONDUCTOR
// =============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'login_conductor') {
    
    $codigo = strtoupper(trim($_POST['codigo']));
    $pin    = trim($_POST['pin']);

    // Buscar transportadora por CÓDIGO
    $stmt = $pdo->prepare("SELECT * FROM transportadoras WHERE codigo_acceso = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$codigo]);
    $chofer = $stmt->fetch();

    if ($chofer && password_verify($pin, $chofer['pin_acceso'])) {
        
        // ¡LOGIN EXITOSO!
        // Creamos una sesión especial, diferente a la del Admin
        $_SESSION['usuario_id'] = 'CHOFER-' . $chofer['id']; // ID ficticio para pasar filtros
        $_SESSION['transportadora_id'] = $chofer['id'];
        $_SESSION['rol'] = 'Conductor';
        $_SESSION['nombre'] = $chofer['nombre'];
        $_SESSION['empresa_id'] = $chofer['empresa_id']; // Vinculado a la empresa dueña

        header("Location: index.php?ruta=portal/dashboard");
        exit();

    } else {
        // Falló
        header("Location: index.php?ruta=portal/login&error=true");
        exit();
    }
}

// LOGOUT
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php?ruta=portal/login");
    exit();
}

// =============================================================================
// ACTUALIZAR ESTADO DEL PEDIDO (Desde la App Conductor)
// =============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'actualizar_estado_ruta') {
    
    // Verificamos sesión por seguridad
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Conductor') {
        die("Acceso denegado");
    }

    $id_pedido = $_POST['id'];
    $nuevo_estado = $_POST['nuevo_estado']; // 'Entregado' o 'Devuelto'
    $comentario = trim($_POST['comentario'] ?? '');
    $motivo = $_POST['motivo'] ?? '';
    
    // Combinar motivo y comentario si es devolución
    if (!empty($motivo)) {
        $comentario = "Motivo: $motivo. " . $comentario;
    }

    $transportadora_id = $_SESSION['transportadora_id'];

    // Validar que el pedido pertenezca a esta transportadora (Evitar hackeos)
    $check = $pdo->prepare("SELECT id FROM pedidos WHERE id = ? AND transportadora_id = ?");
    $check->execute([$id_pedido, $transportadora_id]);
    
    if ($check->fetch()) {
        
        // 1. Actualizamos el Estado
        // Si es entregado, ponemos la fecha de entrega AHORA
        if ($nuevo_estado == 'Entregado') {
            $sql = "UPDATE pedidos SET estado_interno = ?, fecha_entrega = NOW(), notas_internas = CONCAT(COALESCE(notas_internas, ''), ' | Chofer: ', ?) WHERE id = ?";
        } else {
            // Si es devuelto, no ponemos fecha de entrega
            $sql = "UPDATE pedidos SET estado_interno = ?, notas_internas = CONCAT(COALESCE(notas_internas, ''), ' | Devolución: ', ?) WHERE id = ?";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevo_estado, $comentario, $id_pedido]);

        // Redirigir al dashboard con éxito
        header("Location: index.php?ruta=portal/dashboard&msg=actualizado");
        exit();

    } else {
        die("Error: Este pedido no te corresponde.");
    }
}

?>