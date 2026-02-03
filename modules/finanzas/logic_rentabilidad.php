<?php
// modules/finanzas/logic_rentabilidad.php
// CEREBRO DE INTELIGENCIA DE NEGOCIOS (BI)

$empresa_id = $_SESSION['empresa_id'];

// 1. FILTROS DE FECHA
$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-d');

// -------------------------------------------------------------
// 2. OBTENER GASTO DE PUBLICIDAD (ADS)
// -------------------------------------------------------------
$sql_ads = "SELECT COALESCE(SUM(monto), 0) FROM marketing_gasto WHERE empresa_id = ? AND fecha BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_ads);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$G_ads = $stmt->fetchColumn(); // Inversión Total en Marketing

// -------------------------------------------------------------
// 3. ANÁLISIS DEL EMBUDO DE PEDIDOS (TODOS LOS ESTADOS)
// -------------------------------------------------------------
$sql_funnel = "SELECT estado_interno, COUNT(*) as cantidad, 
               COALESCE(SUM(costo_envio_real), 0) as costo_envio,
               COALESCE(SUM(costo_empaque_real), 0) as costo_empaque,
               COALESCE(SUM(total_venta), 0) as venta_bruta
               FROM pedidos 
               WHERE empresa_id = ? AND DATE(fecha_creacion) BETWEEN ? AND ?
               GROUP BY estado_interno";
$stmt = $pdo->prepare($sql_funnel);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar contadores
$stats = [
    'Total' => 0, 'Nuevo' => 0, 'Confirmado' => 0, 'En Ruta' => 0, 
    'Entregado' => 0, 'Devuelto' => 0, 'Rechazado' => 0, 'Cancelado' => 0
];
$costos_logisticos = 0; // Suma de envíos de TODOS los pedidos (incluso fallidos)
$ventas_cobradas = 0; // Solo Entregados

foreach ($filas as $fila) {
    $est = $fila['estado_interno'];
    $stats[$est] = $fila['cantidad'];
    $stats['Total'] += $fila['cantidad'];
    
    // Sumamos costos logísticos de TODO lo que se movió (En Ruta, Entregado, Devuelto, Rechazado)
    // Nota: Cancelados y Nuevos normalmente no tienen costo de envío real, pero si lo tuvieran, se suma.
    $costos_logisticos += ($fila['costo_envio'] + $fila['costo_empaque']);

    if ($est == 'Entregado') {
        $ventas_cobradas += $fila['venta_bruta'];
    }
}

// -------------------------------------------------------------
// 4. COSTO DE MERCANCÍA VENDIDA (SOLO ENTREGADOS)
// -------------------------------------------------------------
// Solo contamos el costo del producto de lo que realmente se vendió.
// Lo devuelto vuelve al stock, así que no es costo financiero (es costo de oportunidad, pero no pérdida contable directa de inventario).
$sql_cogs = "SELECT COALESCE(SUM(d.cantidad * prod.costo_compra), 0) 
             FROM pedidos p
             JOIN pedidos_detalle d ON p.id = d.pedido_id
             JOIN productos prod ON d.producto_id = prod.id
             WHERE p.empresa_id = ? AND p.estado_interno = 'Entregado'
             AND DATE(p.fecha_creacion) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_cogs);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$COGS_real = $stmt->fetchColumn();

// -------------------------------------------------------------
// 5. MATEMÁTICA CERRADA (RESULTADOS)
// -------------------------------------------------------------

// A. CPAs
$pedidos_totales = max($stats['Total'], 1); // Evitar división por cero
$pedidos_entregados = max($stats['Entregado'], 1);

$CPA_marketing = $G_ads / $pedidos_totales; // Costo por Lead (lo que te dice FB)
$CPA_real      = $G_ads / $pedidos_entregados; // Costo por Cliente Real (La verdad dolorosa)

// B. DESPERDICIO (DINERO QUEMADO)
// Pedidos que gastaron Ads pero no generaron ingreso
$pedidos_fallidos = $stats['Total'] - $stats['Entregado'];
$desperdicio_ads  = $pedidos_fallidos * $CPA_marketing; 

// Costo Logístico de Devoluciones (Devueltos + Rechazados)
// Hacemos una query especifica para ser precisos con el costo logistico de los fallidos
$sql_log_fail = "SELECT COALESCE(SUM(costo_envio_real + costo_empaque_real), 0) 
                 FROM pedidos WHERE empresa_id = ? 
                 AND estado_interno IN ('Devuelto', 'Rechazado')
                 AND DATE(fecha_creacion) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_log_fail);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$desperdicio_logistica = $stmt->fetchColumn();

$total_quemado = $desperdicio_ads + $desperdicio_logistica;

// C. GANANCIA NETA REAL (BOLSILLO)
// Venta - Costo Producto - Gasto Ads Total - Gasto Logística Total
$ganancia_neta = $ventas_cobradas - $COGS_real - $G_ads - $costos_logisticos;

// Tasa de Efectividad
$tasa_entrega = ($stats['Entregado'] / $pedidos_totales) * 100;
 

// GUARDAR NUEVO AD SPEND (Si viene por POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_ads'])) {
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $plat  = $_POST['plataforma'];
    
    $ins = $pdo->prepare("INSERT INTO marketing_gasto (empresa_id, fecha, monto, plataforma) VALUES (?, ?, ?, ?)");
    $ins->execute([$empresa_id, $fecha, $monto, $plat]);
    
    // --- SOLUCIÓN DEL ERROR ---
    // En lugar de header(), usamos JavaScript para redirigir sin conflictos
    echo "<script>window.location='index.php?ruta=finanzas/rentabilidad&f_ini=$f_ini&f_fin=$f_fin';</script>";
    exit();
}
?>