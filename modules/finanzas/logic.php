<?php
// modules/finanzas/logic.php
// CEREBRO FINANCIERO: CÁLCULO DE P&L (PÉRDIDAS Y GANANCIAS)

// 1. Configuración Inicial
$empresa_id = $_SESSION['empresa_id'];

// Fechas por defecto: Desde el 1ro del mes actual hasta hoy
$f_inicio = isset($_GET['f_inicio']) ? $_GET['f_inicio'] : date('Y-m-01');
$f_fin    = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-d');

// Filtros Opcionales
$id_trans = isset($_GET['id_trans']) ? $_GET['id_trans'] : '';
$id_alm   = isset($_GET['id_alm']) ? $_GET['id_alm'] : '';

// Bandera: ¿Estamos filtrando específicamente?
// Si se filtra por almacén o transporte, NO debemos restar los gastos fijos globales (luz, internet, ads generales)
$es_filtro_especifico = (!empty($id_trans) || !empty($id_alm));
$titulo_resultado = $es_filtro_especifico ? "Margen Operativo (Filtro)" : "Ganancia Neta";

// =========================================================
// 2. OBTENER LISTAS PARA LOS FILTROS (Dropdowns)
// =========================================================
$transportadoras = $pdo->query("SELECT id, nombre FROM transportadoras WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();
$almacenes       = $pdo->query("SELECT id, nombre FROM almacenes WHERE empresa_id = $empresa_id AND activo = 1")->fetchAll();

// =========================================================
// 3. CONSTRUCCIÓN DE LA CONSULTA PRINCIPAL (PEDIDOS)
// =========================================================
// Solo consideramos pedidos ENTREGADOS para finanzas reales.
// Usamos fecha_entrega si existe, sino fecha_creacion.

$sql_base_where = " WHERE p.empresa_id = ? 
                    AND p.estado_interno = 'Entregado' 
                    AND (DATE(p.fecha_entrega) BETWEEN ? AND ? OR (p.fecha_entrega IS NULL AND DATE(p.fecha_creacion) BETWEEN ? AND ?))";

$params = [$empresa_id, $f_inicio, $f_fin, $f_inicio, $f_fin];

// Aplicar Filtros Dinámicos
if (!empty($id_trans)) {
    $sql_base_where .= " AND p.transportadora_id = ?";
    $params[] = $id_trans;
}

if (!empty($id_alm)) {
    $sql_base_where .= " AND p.almacen_id = ?";
    $params[] = $id_alm;
}

// =========================================================
// 4. CÁLCULOS KPI
// =========================================================

// A) INGRESOS Y COSTOS LOGÍSTICOS
// Sumamos ventas, costos de envío reales y costos de empaque reales
$sql_ventas = "SELECT 
                COALESCE(SUM(p.total_venta), 0) as total_ventas,
                COALESCE(SUM(p.costo_envio_real), 0) as total_envios,
                COALESCE(SUM(p.costo_empaque_real), 0) as total_empaques
               FROM pedidos p" . $sql_base_where;

$stmt = $pdo->prepare($sql_ventas);
$stmt->execute($params);
$result_ventas = $stmt->fetch();

$total_ingresos = $result_ventas['total_ventas'];
$total_logistica = $result_ventas['total_envios'] + $result_ventas['total_empaques'];

// B) COSTO DE MERCANCÍA VENDIDA (COGS)
// Necesitamos saber cuánto nos costaron los productos de esos pedidos entregados.
// JOIN: Pedidos -> Detalles -> Productos (para sacar el costo_compra)
$sql_cogs = "SELECT 
                COALESCE(SUM(d.cantidad * prod.costo_compra), 0) as total_cogs
             FROM pedidos p
             JOIN pedidos_detalle d ON p.id = d.pedido_id
             JOIN productos prod ON d.producto_id = prod.id
             " . $sql_base_where; // Usamos el mismo WHERE filtrado

$stmt = $pdo->prepare($sql_cogs);
$stmt->execute($params);
$total_cogs = $stmt->fetchColumn();

// C) GASTOS FIJOS Y ADS (Tabla 'gastos')
// Solo los calculamos si NO hay filtros específicos (o los mostramos informativos)
$total_gastos = 0;
// NOTA: Si no creaste la tabla gastos, esto dará error. Asegúrate de crearla o comentar esto.
try {
    $sql_gastos = "SELECT COALESCE(SUM(monto), 0) FROM gastos WHERE empresa_id = ? AND fecha BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql_gastos);
    $stmt->execute([$empresa_id, $f_inicio, $f_fin]);
    $total_gastos = $stmt->fetchColumn();
} catch (Exception $e) {
    $total_gastos = 0; // Si la tabla no existe, asumimos 0 para no romper
}

// =========================================================
// 5. CÁLCULO DE RESULTADOS FINALES
// =========================================================

// 1. Ganancia Bruta (Ventas - Costo del Producto)
$ganancia_bruta = $total_ingresos - $total_cogs;

// 2. Costos Logísticos Totales (Variable para la vista)
$total_envios = $total_logistica; // Renombramos para coincidir con tu vista

// 3. Resultado Operativo
if ($es_filtro_especifico) {
    // Si filtramos por almacén, NO restamos la luz ni los ads globales, porque distorsiona el dato del almacén
    $resultado_operativo = $ganancia_bruta - $total_logistica;
} else {
    // Si vemos el global, restamos todo
    $resultado_operativo = $ganancia_bruta - $total_logistica - $total_gastos;
}

// =========================================================
// 6. VALOR DE INVENTARIO (SNAPSHOT)
// =========================================================
// Esto no depende de las fechas, es el valor HOY.
$sql_inv = "SELECT COALESCE(SUM(stock_actual * costo_compra), 0) FROM productos WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_inv);
$stmt->execute([$empresa_id]);
$valor_inventario = $stmt->fetchColumn();

?>