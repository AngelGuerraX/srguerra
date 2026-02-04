<?php
// index.php
// ROUTER PRINCIPAL DEL SISTEMA (SAAS LOGÍSTICO)

date_default_timezone_set('America/Santo_Domingo');

// 1. CONFIGURACIÓN E INICIO
if (file_exists('config/db.php')) require_once 'config/db.php';
if (file_exists('config/security.php')) require_once 'config/security.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) session_start();

// Capturar la ruta solicitada
$ruta = isset($_GET['ruta']) ? $_GET['ruta'] : 'dashboard';

// 2. RUTAS SIN DISEÑO (Vistas "invisibles", procesos, impresiones, login)
$rutas_sin_layout = [
    'login', 'logout', 'validar-login', 
    'pedidos/logic', 'guardar-pedido', 'exportar-pedidos', 'imprimir-etiqueta',
    'inventario/logic', 'transportadoras/logic', 'almacenes/logic',
    'clientes/logic', 'configuracion/logic',
    'productos/logic', 'guardar-producto', 
    'actualizar-estado-pedido', 
    'portal/login', 'portal/dashboard', 'portal/logic', 'portal/detalle', 'portal/historial',
    'pedidos/imprimir_hoja_ruta', 'pedidos/imprimir_etiqueta',
    'saas/logic', 'finanzas/logic', 'finanzas/logic_rentabilidad' // <--- AGREGADO
];

// 3. CONTROL DE ACCESO (Seguridad)
if (!in_array($ruta, $rutas_sin_layout)) {
    // Si no es ruta pública y no hay usuario, mandar al login
    if (!isset($_SESSION['usuario_id']) && $ruta != 'login') {
        header("Location: index.php?ruta=login");
        exit();
    }
    // Cargar Header (Menú Lateral)
    if (file_exists('includes/header.php')) include 'includes/header.php';
}

// 4. SWITCH DE RUTAS (ROUTER)
switch ($ruta) {

    // --- AUTENTICACIÓN ---
    case 'login':
        if (file_exists('views/login/login.php')) {
            include 'views/login/login.php';
        } else {
            die("Error: No se encuentra views/login/login.php");
        }
        break;

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

    // --- DASHBOARD ---
    case 'dashboard':
        $file = file_exists('modules/dashboard/index.php') ? 'modules/dashboard/index.php' : 'modules/pedidos/index.php';
        include $file;
        break;

    // --- PEDIDOS Y ENVÍOS ---
    case 'pedidos': include 'modules/pedidos/index.php'; break;
    case 'pedidos/nuevo': include 'modules/pedidos/nuevo.php'; break;
    case 'pedidos/ver': include 'modules/pedidos/ver.php'; break;
    case 'pedidos/logic': 
    case 'guardar-pedido': include 'modules/pedidos/logic.php'; break;
    case 'exportar-pedidos': include 'modules/pedidos/exportar.php'; break;
    
    // Impresiones
    case 'imprimir-etiqueta': 
    case 'pedidos/imprimir_etiqueta': include 'modules/pedidos/imprimir_etiqueta.php'; break;
    
    case 'actualizar-estado-pedido':
        $_GET['action'] = 'cambiar_estado'; 
        include 'modules/pedidos/logic.php';
        break;
    
    // Funciones avanzadas de Pedidos
    case 'pedidos/despacho': include 'modules/pedidos/despacho.php'; break;
    case 'pedidos/imprimir_hoja_ruta': include 'modules/pedidos/imprimir_hoja_ruta.php'; break;
    case 'pedidos/importar': include 'modules/pedidos/importar.php'; break;

    // --- INVENTARIO Y PRODUCTOS ---
    case 'inventario': include 'modules/inventario/index.php'; break;
    case 'inventario/nuevo': include 'modules/inventario/nuevo.php'; break;
    case 'inventario/editar': include 'modules/inventario/editar.php'; break;
    case 'inventario/logic': include 'modules/inventario/logic.php'; break;
    case 'inventario/comprar': include 'modules/inventario/comprar.php'; break;
    
    // Rutas específicas para guardar productos
    case 'guardar-producto': 
    case 'productos/logic': 
        include 'modules/inventario/logic.php'; 
        break;

    // --- TRANSPORTADORAS ---
    case 'transportadoras': include 'modules/transportadoras/index.php'; break;
    case 'transportadoras/nuevo': include 'modules/transportadoras/nuevo.php'; break;
    case 'transportadoras/ver': include 'modules/transportadoras/ver.php'; break;
    case 'transportadoras/editar': include 'modules/transportadoras/editar.php'; break;
    case 'transportadoras/logic': include 'modules/transportadoras/logic.php'; break;

    // --- ALMACENES ---
    case 'almacenes': include 'modules/almacenes/index.php'; break;
    case 'almacenes/nuevo': include 'modules/almacenes/nuevo.php'; break;
    case 'almacenes/editar': include 'modules/almacenes/editar.php'; break;
    case 'almacenes/ver': include 'modules/almacenes/ver.php'; break;
    case 'almacenes/logic': include 'modules/almacenes/logic.php'; break;

    // --- CLIENTES ---
    case 'clientes': include 'modules/clientes/index.php'; break;
    case 'clientes/ver': include 'modules/clientes/ver.php'; break;
    case 'clientes/editar': include 'modules/clientes/editar.php'; break;
    case 'clientes/logic': include 'modules/clientes/logic.php'; break;

    // --- FINANZAS (ACTUALIZADO) ---
    case 'finanzas': include 'modules/finanzas/index.php'; break;
    case 'finanzas/gastos': include 'modules/finanzas/gastos.php'; break;
    case 'finanzas/rentabilidad': include 'modules/finanzas/rentabilidad.php'; break; // <--- NUEVO
    case 'finanzas/logic': include 'modules/finanzas/logic.php'; break;

    // --- CONFIGURACIÓN ---
    case 'configuracion': 
    case 'configuracion/perfil': 
        include 'modules/configuracion/perfil.php'; 
        break;
    case 'configuracion/logic': include 'modules/configuracion/logic.php'; break;

    // --- SAAS (ADMINISTRACIÓN GLOBAL) ---
    case 'saas': 
    case 'saas/index': include 'modules/saas/index.php'; break;
    case 'saas/logic': include 'modules/saas/logic.php'; break;
    case 'saas/usuarios': include 'modules/saas/usuarios.php'; break;

    // --- PORTAL TRANSPORTISTAS (APP MÓVIL) ---
    case 'portal/login': include 'modules/portal/login.php'; break;
    case 'portal/dashboard': include 'modules/portal/dashboard.php'; break;
    case 'portal/logic': include 'modules/portal/logic.php'; break;
    case 'portal/detalle': include 'modules/portal/detalle.php'; break;
    case 'portal/historial': include 'modules/portal/historial.php'; break;

    // --- MARKETING (ADS) ---
    case 'marketing': include 'modules/marketing/index.php'; break;

    // En la sección switch($ruta):
    case 'transportadoras/pagos': include 'modules/transportadoras/pagos.php'; break;

    // --- ERROR 404 ---
    default:
        echo "<div class='d-flex justify-content-center align-items-center vh-100 text-white'>";
        echo "<div class='text-center'>";
        echo "<h1 class='display-1 fw-bold text-danger'>404</h1>";
        echo "<h3>Página no encontrada</h3>";
        echo "<p class='text-muted'>La ruta '<b>" . htmlspecialchars($ruta) . "</b>' no existe.</p>";
        echo "<a href='index.php' class='btn btn-outline-light mt-3'>Volver al Inicio</a>";
        echo "</div></div>";
        break;
}

// 5. CARGAR FOOTER
if (!in_array($ruta, $rutas_sin_layout)) {
    if (file_exists('includes/footer.php')) include 'includes/footer.php';
}
?>