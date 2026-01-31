<?php
// config/security.php
// SISTEMA DE SEGURIDAD ROBUSTO

// 1. INICIAR SESIÓN (Si no está iniciada ya)
if (session_status() === PHP_SESSION_NONE) {
    // Configuración para que la sesión dure más tiempo (24 horas) y no se cierre sola
    ini_set('session.gc_maxlifetime', 86400);
    session_set_cookie_params(86400);
    session_start();
}

// 2. GENERAR TOKEN (Solo si no existe, para no invalidar el formulario actual)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función auxiliar para imprimir el input en los formularios
function generar_csrf_token()
{
    return $_SESSION['csrf_token'];
}

// 3. VALIDAR TOKEN
function validar_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // A) Verificar si viene el token en el formulario
        if (!isset($_POST['csrf_token'])) {
            die("Error de Seguridad: El formulario no envió el token de seguridad (CSRF).");
        }

        // B) Verificar si existe token en la sesión
        if (!isset($_SESSION['csrf_token'])) {
            die("Error de Seguridad: La sesión ha caducado. Por favor recarga la página.");
        }

        // C) Comparar (Deben ser idénticos)
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            // Debug: Mostrar esto solo si necesitas ver qué pasó
            // echo "Recibido: " . $_POST['csrf_token'] . "<br>";
            // echo "Esperado: " . $_SESSION['csrf_token'] . "<br>";
            die("Error de Seguridad: Token inválido. Es posible que tengas dos pestañas abiertas. Recarga e intenta de nuevo.");
        }
    }
}

// 4. PROTEGER RUTAS
function verificar_sesion()
{
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php?ruta=login");
        exit();
    }
}
