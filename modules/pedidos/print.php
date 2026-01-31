<?php
// modules/pedidos/print.php
// NOTA: No incluimos db.php ni security.php porque index.php ya los cargó.

if (!isset($_GET['id'])) {
    die("Error: Falta el ID del pedido.");
}

$pedido_id = $_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// 1. OBTENER DATOS
$sql = "SELECT p.*, 
               c.nombre as cli_nombre, c.telefono as cli_telefono, 
               c.provincia as cli_provincia, c.ciudad as cli_ciudad, c.direccion as cli_direccion,
               t.nombre as trans_nombre,
               a.nombre as alm_nombre
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN transportadoras t ON p.transportadora_id = t.id
        LEFT JOIN almacenes a ON p.almacen_id = a.id
        WHERE p.id = ? AND p.empresa_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$pedido_id, $empresa_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die("Pedido no encontrado o acceso denegado.");
}

// 2. OBTENER PRODUCTOS
$stmt = $pdo->prepare("SELECT cantidad, nombre_producto FROM pedidos_detalle WHERE pedido_id = ?");
$stmt->execute([$pedido_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Etiqueta #<?php echo $pedido['numero_orden']; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 5mm;
            font-family: 'Arial', sans-serif;
            width: 90mm;
            color: #000;
        }

        .box {
            border: 2px solid #000;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 4px;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .fs-2 {
            font-size: 18px;
        }

        .fs-3 {
            font-size: 14px;
        }

        .small {
            font-size: 11px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .cod-box {
            background: #000;
            color: #fff;
            padding: 5px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 5px;
            border-radius: 4px;
        }

        .items-list {
            margin-top: 5px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            font-size: 12px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        .btn-print {
            background: #000;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            position: fixed;
            bottom: 20px;
            right: 20px;
        }
    </style>
</head>

<body onload="generarBarcode()">

    <button onclick="window.print()" class="btn-print no-print">🖨️ IMPRIMIR</button>

    <div class="header">
        <div>
            <div class="small fw-bold">TRANSPORTE:</div>
            <div class="fs-2 fw-bold"><?php echo strtoupper($pedido['trans_nombre'] ?: 'PROPIO'); ?></div>
        </div>
        <div class="text-center">
            <div class="small">FECHA</div>
            <div class="fw-bold"><?php echo date('d/m/Y', strtotime($pedido['fecha_creacion'])); ?></div>
        </div>
    </div>

    <div class="box">
        <div class="small fw-bold">ENTREGAR A:</div>
        <div class="fs-2 fw-bold"><?php echo $pedido['cli_nombre']; ?></div>
        <div class="fs-3"><?php echo $pedido['cli_telefono']; ?></div>
        <div class="fs-3 mt-1"><?php echo $pedido['cli_direccion']; ?></div>
        <div class="fs-2 fw-bold mt-1"><?php echo $pedido['cli_ciudad']; ?>, <?php echo $pedido['cli_provincia']; ?></div>
    </div>

    <div class="cod-box">
        COBRAR: RD$ <?php echo number_format($pedido['total_venta'], 0); ?>
    </div>

    <div class="text-center mt-2">
        <svg id="barcode"></svg>
    </div>

    <div class="items-list">
        <div class="small fw-bold mb-1">CONTENIDO (<?php echo $pedido['alm_nombre']; ?>):</div>
        <?php foreach ($items as $item): ?>
            <div>[ ] <strong>x<?php echo $item['cantidad']; ?></strong> <?php echo $item['nombre_producto']; ?></div>
        <?php endforeach; ?>
        <?php if (!empty($pedido['notas_internas'])): ?>
            <div class="mt-2 small"><strong>NOTA:</strong> <?php echo $pedido['notas_internas']; ?></div>
        <?php endif; ?>
    </div>

    <div class="text-center small mt-3">SRGUERRA LOGISTICS - #<?php echo $pedido['numero_orden']; ?></div>

    <script>
        function generarBarcode() {
            JsBarcode("#barcode", "<?php echo $pedido['numero_orden']; ?>", {
                format: "CODE128",
                width: 2,
                height: 50,
                displayValue: true,
                fontSize: 14,
                margin: 5
            });
        }
    </script>
</body>

</html>