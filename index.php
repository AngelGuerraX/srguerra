<?php
// index.php
// ROUTER CON LOGIN EN VIEWS

date_default_timezone_set('America/Santo_Domingo');

// 1. Configuración
if (file_exists('config/db.php')) require_once 'config/db.php';
if (file_exists('config/security.php')) require_once 'config/security.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) session_start();

$ruta = isset($_GET['ruta']) ? $_GET['ruta'] : 'dashboard';

// 2. Rutas sin diseño (Procesos invisibles)
// Agregamos 'validar-login' a la lista negra
$rutas_sin_layout = [
    'login', 'logout', 'validar-login', 
    'pedidos/logic', 'guardar-pedido', 'exportar-pedidos', 'imprimir-etiqueta',
    'inventario/logic', 'transportadoras/logic', 'almacenes/logic',
    'clientes/logic', 'configuracion/logic'
];

// 3. Control de Acceso
if (!in_array($ruta, $rutas_sin_layout)) {
    if (!isset($_SESSION['usuario_id']) && $ruta != 'login') {
        header("Location: index.php?ruta=login");
        exit();
    }
    if (file_exists('includes/header.php')) include 'includes/header.php';
}

// 4. SWITCH DE RUTAS
switch ($ruta) {

    // --- LOGIN (VISTA) ---
    case 'login':
        if (file_exists('views/login/login.php')) {
            include 'views/login/login.php';
        } else {
            die("Error: No se encuentra views/login/login.php");
        }
        break;

    // --- VALIDAR LOGIN (LÓGICA - AHORA EN VIEWS) ---
    case 'validar-login':
        if (file_exists('views/login/validar.php')) {
            include 'views/login/validar.php';
        } else {
            die("Error: No se encuentra views/login/validar.php");
        }
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php?ruta=login");
        break;

    // --- RESTO DEL SISTEMA ---
    case 'dashboard':
        $file = file_exists('modules/dashboard/index.php') ? 'modules/dashboard/index.php' : 'modules/pedidos/index.php';
        include $file;
        break;

    case 'pedidos': include 'modules/pedidos/index.php'; break;
    case 'pedidos/nuevo': include 'modules/pedidos/nuevo.php'; break;
    case 'pedidos/ver': include 'modules/pedidos/ver.php'; break;
    case 'pedidos/logic': 
    case 'guardar-pedido': include 'modules/pedidos/logic.php'; break;
    case 'exportar-pedidos': include 'modules/pedidos/exportar.php'; break;
    case 'imprimir-etiqueta': include 'modules/pedidos/imprimir.php'; break;

    case 'inventario': include 'modules/inventario/index.php'; break;
    case 'inventario/nuevo': include 'modules/inventario/nuevo.php'; break;
    case 'inventario/editar': include 'modules/inventario/editar.php'; break;
    case 'inventario/logic': include 'modules/inventario/logic.php'; break;

    case 'transportadoras': include 'modules/transportadoras/index.php'; break;
    case 'transportadoras/nuevo': include 'modules/transportadoras/nuevo.php'; break;
    case 'transportadoras/ver': include 'modules/transportadoras/ver.php'; break;
    case 'transportadoras/logic': include 'modules/transportadoras/logic.php'; break;
    
    case 'almacenes': include 'modules/almacenes/index.php'; break;
    case 'almacenes/nuevo': include 'modules/almacenes/nuevo.php'; break;
    case 'almacenes/ver': include 'modules/almacenes/ver.php'; break;
    case 'almacenes/logic': include 'modules/almacenes/logic.php'; break;

    case 'clientes': include 'modules/clientes/index.php'; break;
    case 'clientes/ver': include 'modules/clientes/ver.php'; break;
    case 'clientes/editar': include 'modules/clientes/editar.php'; break;
    case 'clientes/logic': include 'modules/clientes/logic.php'; break;

    case 'configuracion': include 'modules/configuracion/index.php'; break;
    case 'configuracion/logic': include 'modules/configuracion/logic.php'; break;

    default:
        echo "<h1 style='color:white; text-align:center; margin-top:50px;'>Error 404</h1>";
        break;
}

if (!in_array($ruta, $rutas_sin_layout)) {
    if (file_exists('includes/footer.php')) include 'includes/footer.php';
}
?>