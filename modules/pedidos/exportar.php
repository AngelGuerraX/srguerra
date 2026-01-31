<?php
// modules/pedidos/exportar.php
// GENERADOR DE EXCEL PERSONALIZADO

// Verificar si se enviaron pedidos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedidos']) && !empty($_POST['pedidos'])) {
    
    $ids = $_POST['pedidos'];
    
    // Convertir array de IDs a string seguro para SQL (ej: 1,2,5)
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Consulta Maestra: Pedidos + Clientes
    $sql = "SELECT p.id, p.total_venta, p.notas_internas, p.costo_envio_real,
                   c.nombre, c.telefono, c.direccion, c.provincia
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id IN ($placeholders)
            ORDER BY p.id DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparar headers para descarga de Excel (.xls)
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=reporte_pedidos_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // INICIO DEL EXCEL (HTML TABLE)
    echo '<table border="1">';
    
    // FILA 1: ENCABEZADOS (A-I)
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<td>Nombre</td>';      // A
    echo '<td>Telefono</td>';    // B
    echo '<td>Direccion</td>';   // C
    echo '<td>Provincia</td>';   // D
    echo '<td>Articulo</td>';    // E
    echo '<td>Cantidad</td>';    // F
    echo '<td>Valor</td>';       // G
    echo '<td>Cobrar</td>';      // H
    echo '<td>Notas</td>';       // I
    echo '</tr>';

    // FILA 2 en adelante: DATOS
    foreach ($pedidos as $p) {
        
        // Obtener productos de este pedido (concatenados)
        $stmt_det = $pdo->prepare("SELECT nombre_producto, cantidad, precio_unitario FROM pedidos_detalle WHERE pedido_id = ?");
        $stmt_det->execute([$p['id']]);
        $detalles = $stmt_det->fetchAll();

        // Procesar productos para que quepan en una celda
        $nombres_prod = [];
        $cantidades_prod = [];
        
        foreach($detalles as $d) {
            $nombres_prod[] = $d['nombre_producto'];
            $cantidades_prod[] = $d['cantidad'];
        }

        $str_articulos = implode(" + ", $nombres_prod);
        $str_cantidades = implode(" + ", $cantidades_prod);
        
        // Calcular valores
        // Valor = Total venta (o precio base, según prefieras). Usaré Total Venta.
        // Cobrar = Total Venta (Generalmente es lo mismo en COD).
        
        echo '<tr>';
        echo '<td>' . mb_convert_encoding($p['nombre'], 'UTF-16LE', 'UTF-8') . '</td>';
        echo '<td>' . $p['telefono'] . '</td>';
        echo '<td>' . mb_convert_encoding($p['direccion'], 'UTF-16LE', 'UTF-8') . '</td>';
        echo '<td>' . mb_convert_encoding($p['provincia'], 'UTF-16LE', 'UTF-8') . '</td>';
        echo '<td>' . mb_convert_encoding($str_articulos, 'UTF-16LE', 'UTF-8') . '</td>';
        echo '<td>' . $str_cantidades . '</td>';
        echo '<td>' . number_format($p['total_venta'], 2) . '</td>';
        echo '<td>' . number_format($p['total_venta'], 2) . '</td>';
        echo '<td>' . mb_convert_encoding($p['notas_internas'], 'UTF-16LE', 'UTF-8') . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    exit;

} else {
    // Si entró sin seleccionar nada
    echo "<script>alert('Por favor selecciona al menos un pedido.'); window.close();</script>";
}
?>