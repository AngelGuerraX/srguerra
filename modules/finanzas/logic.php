<?php
// modules/finanzas/logic.php
verificar_sesion();
$empresa_id = $_SESSION['empresa_id'];

// --- 1. CAPTURAR FILTROS ---
$f_inicio = $_GET['f_inicio'] ?? date('Y-m-01'); // Por defecto: Inicio de mes
$f_fin    = $_GET['f_fin']    ?? date('Y-m-d');  // Por defecto: Hoy
$id_trans = $_GET['id_trans'] ?? '';             // Filtro Transportadora
$id_alm   = $_GET['id_alm']   ?? '';             // Filtro Almacén

// --- 2. CONSTRUIR FILTROS SQL (WHERE DINÁMICO) ---
$condicion_pedidos = "WHERE p.empresa_id = ? AND DATE(p.fecha_creacion) BETWEEN ? AND ?";
$params_pedidos = [$empresa_id, $f_inicio, $f_fin];

// Agregar filtro de Transportadora si existe
if (!empty($id_trans)) {
    $condicion_pedidos .= " AND p.transportadora_id = ?";
    $params_pedidos[] = $id_trans;
}

// Agregar filtro de Almacén si existe
if (!empty($id_alm)) {
    $condicion_pedidos .= " AND p.almacen_id = ?";
    $params_pedidos[] = $id_alm;
}

// --- 3. CÁLCULOS OPERATIVOS (Afectados por todos los filtros) ---

// A) Ingresos (Ventas Entregadas)
// Usamos alias 'p' porque haremos JOINS implícitos en las consultas
$sql = "SELECT SUM(p.total_venta) FROM pedidos p $condicion_pedidos AND p.estado_interno = 'Entregado'";
$stmt = $pdo->prepare($sql);
$stmt->execute($params_pedidos);
$total_ingresos = $stmt->fetchColumn() ?: 0;

// B) Costos Logísticos (Envío + Empaque)
// Se suman si están En Ruta, Entregado o Devuelto
$sql = "SELECT SUM(p.costo_envio_real + p.costo_empaque_real) FROM pedidos p $condicion_pedidos AND p.estado_interno IN ('En Ruta', 'Entregado', 'Devuelto')";
$stmt = $pdo->prepare($sql);
$stmt->execute($params_pedidos);
$total_envios = $stmt->fetchColumn() ?: 0;

// C) Costo de Mercancía (COGS)
// Costo de los productos vendidos en pedidos entregados
$sql = "SELECT SUM(d.costo_unitario * d.cantidad) 
        FROM pedidos_detalle d 
        JOIN pedidos p ON d.pedido_id = p.id 
        $condicion_pedidos AND p.estado_interno = 'Entregado'";
$stmt = $pdo->prepare($sql);
$stmt->execute($params_pedidos);
$total_cogs = $stmt->fetchColumn() ?: 0;

// --- 4. GASTOS GENERALES (Ads, Nómina) ---
// OJO: Los gastos generales NO tienen transportadora ni almacén. 
// Solo se filtran por FECHA. Si filtras por transportadora, estos gastos se muestran informativos pero no se restan del margen operativo específico.
$params_gastos = [$empresa_id, $f_inicio, $f_fin];
$sql_gastos = "SELECT SUM(monto) FROM finanzas_gastos WHERE empresa_id = ? AND fecha BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_gastos);
$stmt->execute($params_gastos);
$total_gastos = $stmt->fetchColumn() ?: 0;

// --- 5. VALOR DEL INVENTARIO (ACTIVO) ---
// Calculamos cuánto dinero hay en stock AHORA MISMO (independiente de las fechas, pero dependiente del almacén)
if (!empty($id_alm)) {
    // Si seleccionó almacén, usamos la tabla inventario_almacen
    $sql_inv = "SELECT SUM(p.costo_compra * i.cantidad) 
                FROM inventario_almacen i 
                JOIN productos p ON i.producto_id = p.id 
                WHERE p.empresa_id = ? AND i.almacen_id = ?";
    $stmt = $pdo->prepare($sql_inv);
    $stmt->execute([$empresa_id, $id_alm]);
} else {
    // Si es global, usamos el stock total de la tabla productos
    $sql_inv = "SELECT SUM(costo_compra * stock_actual) FROM productos WHERE empresa_id = ?";
    $stmt = $pdo->prepare($sql_inv);
    $stmt->execute([$empresa_id]);
}
$valor_inventario = $stmt->fetchColumn() ?: 0;

// --- 6. RESULTADOS FINALES ---
$ganancia_bruta = $total_ingresos - $total_cogs; // Margen de producto

// Si estamos filtrando específicamente por transportadora o almacén, calculamos el "Margen de Contribución"
// Si es vista general, calculamos "Ganancia Neta" restando los Ads.
$es_filtro_especifico = (!empty($id_trans) || !empty($id_alm));

if ($es_filtro_especifico) {
    $resultado_operativo = $ganancia_bruta - $total_envios; // No restamos Ads aquí porque no sabemos de qué transportadora son
    $titulo_resultado = "Margen Operativo (Contribución)";
} else {
    $resultado_operativo = $ganancia_bruta - $total_envios - $total_gastos;
    $titulo_resultado = "Ganancia Neta (Bolsillo)";
}

// Listas para los selectores del filtro
$transportadoras = $pdo->query("SELECT id, nombre FROM transportadoras WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
$almacenes = $pdo->query("SELECT id, nombre FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
?>