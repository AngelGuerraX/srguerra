<?php
// modules/finanzas/logic_rentabilidad.php
// CEREBRO DE INTELIGENCIA DE NEGOCIOS (BI)

if (!isset($_SESSION['empresa_id'])) return; // Seguridad simple
$empresa_id = $_SESSION['empresa_id'];

// 1. FILTROS DE FECHA
$f_ini = isset($_GET['f_ini']) ? $_GET['f_ini'] : date('Y-m-01');
$f_fin = isset($_GET['f_fin']) ? $_GET['f_fin'] : date('Y-m-d');

// 2. OBTENER GASTO DE PUBLICIDAD (ADS)
$sql_ads = "SELECT COALESCE(SUM(monto), 0) FROM marketing_gasto WHERE empresa_id = ? AND fecha BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_ads);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$G_ads = $stmt->fetchColumn(); 

// 3. ANÁLISIS DEL EMBUDO DE PEDIDOS
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

// Inicializar contadores en 0 para evitar errores visuales
$stats = [
    'Total' => 0, 'Nuevo' => 0, 'Confirmado' => 0, 'En Ruta' => 0, 
    'Entregado' => 0, 'Devuelto' => 0, 'Rechazado' => 0, 'Cancelado' => 0
];
$costos_logisticos = 0; 
$ventas_cobradas = 0; 

foreach ($filas as $fila) {
    $est = $fila['estado_interno'];
    // Mapeo seguro para evitar error si el estado no está en el array inicial
    if(isset($stats[$est])) {
        $stats[$est] = $fila['cantidad'];
    }
    $stats['Total'] += $fila['cantidad'];
    
    // Sumamos costos logísticos de TODO
    $costos_logisticos += ($fila['costo_envio'] + $fila['costo_empaque']);

    if ($est == 'Entregado') {
        $ventas_cobradas += $fila['venta_bruta'];
    }
}

// 4. COSTO DE MERCANCÍA VENDIDA (SOLO ENTREGADOS)
$sql_cogs = "SELECT COALESCE(SUM(d.cantidad * prod.costo_compra), 0) 
             FROM pedidos p
             JOIN pedidos_detalle d ON p.id = d.pedido_id
             JOIN productos prod ON d.producto_id = prod.id
             WHERE p.empresa_id = ? AND p.estado_interno = 'Entregado'
             AND DATE(p.fecha_creacion) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_cogs);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$COGS_real = $stmt->fetchColumn();

// 5. CÁLCULOS MATEMÁTICOS
$pedidos_totales = max($stats['Total'], 1); 
$pedidos_entregados = max($stats['Entregado'], 1);

$CPA_marketing = $G_ads / $pedidos_totales; 
$CPA_real      = $G_ads / $pedidos_entregados; 

// Desperdicio (Dinero perdido en logística de devoluciones)
$sql_log_fail = "SELECT COALESCE(SUM(costo_envio_real + costo_empaque_real), 0) 
                 FROM pedidos WHERE empresa_id = ? 
                 AND estado_interno IN ('Devuelto', 'Rechazado')
                 AND DATE(fecha_creacion) BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql_log_fail);
$stmt->execute([$empresa_id, $f_ini, $f_fin]);
$desperdicio_logistica = $stmt->fetchColumn();

// Pedidos fallidos * CPA (Dinero de ads tirado a la basura)
$pedidos_fallidos = $stats['Total'] - $stats['Entregado'];
$desperdicio_ads  = $pedidos_fallidos * $CPA_marketing;
$total_quemado = $desperdicio_ads + $desperdicio_logistica;

// GANANCIA NETA
$ganancia_neta = $ventas_cobradas - $COGS_real - $G_ads - $costos_logisticos;
$tasa_entrega = ($stats['Entregado'] / $pedidos_totales) * 100;

// 6. PROCESAR FORMULARIO (SOLUCIÓN DEL ERROR DE HEADER)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_ads'])) {
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $plat  = $_POST['plataforma'];
    
    $ins = $pdo->prepare("INSERT INTO marketing_gasto (empresa_id, fecha, monto, plataforma) VALUES (?, ?, ?, ?)");
    $ins->execute([$empresa_id, $fecha, $monto, $plat]);
    
    // REDIRECCIÓN JS PARA EVITAR 'HEADERS ALREADY SENT'
    echo "<script>window.location='index.php?ruta=finanzas/rentabilidad&f_ini=$f_ini&f_fin=$f_fin';</script>";
    exit();
}
?>