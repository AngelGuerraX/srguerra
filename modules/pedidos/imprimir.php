<?php
// modules/pedidos/imprimir.php
// ETIQUETA DE ENVÍO PROFESIONAL

// 1. OBTENER DATOS
$id = (int)$_GET['id'];
$empresa_id = $_SESSION['empresa_id'];

// Datos del Pedido + Cliente + Transporte
$sql = "SELECT p.*, 
        c.nombre as cliente, c.telefono, c.direccion, c.ciudad, c.provincia,
        t.nombre as transporte
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN transportadoras t ON p.transportadora_id = t.id
        WHERE p.id = ? AND p.empresa_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id, $empresa_id]);
$pedido = $stmt->fetch();

if (!$pedido) die("Pedido no encontrado");

// Datos de la Empresa (Remitente)
$stmt_emp = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt_emp->execute([$empresa_id]);
$empresa = $stmt_emp->fetch();

// Detalles del pedido (Productos)
$stmt_det = $pdo->prepare("SELECT d.*, p.nombre FROM pedidos_detalle d JOIN productos p ON d.producto_id = p.id WHERE d.pedido_id = ?");
$stmt_det->execute([$id]);
$items = $stmt_det->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta #<?php echo $pedido['numero_orden']; ?></title>
    <style>
        /* ESTILOS DE IMPRESIÓN */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #ccc; /* Fondo gris en pantalla para distinguir el papel */
        }
        .etiqueta {
            background: white;
            width: 100mm; /* Ancho estándar etiqueta térmica (aprox 4 pulgadas) */
            min-height: 150mm; /* Alto estándar (aprox 6 pulgadas) */
            margin: 0 auto;
            padding: 15px;
            box-sizing: border-box;
            border: 1px solid #000;
            position: relative;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
            text-align: center;
        }
        .logo {
            max-height: 50px;
            display: block;
            margin: 0 auto 5px auto;
        }
        .remitente {
            font-size: 10px;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px dashed #999;
            padding-bottom: 5px;
        }
        .destinatario {
            margin-bottom: 15px;
            border: 2px solid #000;
            padding: 10px;
            border-radius: 5px;
        }
        .label-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
        }
        .big-text {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-top: 2px;
        }
        .cod-box {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px dashed #999;
            padding-bottom: 5px;
        }
        .items-list {
            font-size: 11px;
            margin-bottom: 15px;
        }
        .items-list table { width: 100%; border-collapse: collapse; }
        .items-list th { text-align: left; border-bottom: 1px solid #000; }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .barcode {
            height: 40px;
            background: repeating-linear-gradient(
                to right,
                #000,
                #000 2px,
                #fff 2px,
                #fff 4px
            );
            width: 80%;
            margin: 10px auto;
        }

        /* SOLO AL IMPRIMIR */
        @media print {
            body { background: white; padding: 0; }
            .etiqueta { border: none; width: 100%; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: white; border: none; cursor: pointer; font-weight: bold; font-size: 16px;">
            🖨️ IMPRIMIR AHORA
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #ccc; border: none; cursor: pointer; margin-left: 10px;">
            CERRAR
        </button>
    </div>

    <div class="etiqueta">
        
        <div class="header">
            <?php if(!empty($empresa['logo'])): ?>
                <img src="uploads/logos/<?php echo $empresa['logo']; ?>" class="logo">
            <?php else: ?>
                <h2><?php echo strtoupper($empresa['nombre_comercial']); ?></h2>
            <?php endif; ?>
            
            <div style="font-size: 12px; font-weight: bold;">ORDEN #<?php echo $pedido['numero_orden']; ?></div>
        </div>

        <div class="remitente">
            <strong>DE (REMITENTE):</strong> <?php echo strtoupper($empresa['nombre_comercial']); ?><br>
            <?php echo $empresa['direccion']; ?><br>
            Tel: <?php echo $empresa['telefono_contacto']; ?>
        </div>

        <div class="destinatario">
            <span class="label-title">PARA (DESTINATARIO):</span>
            <div class="big-text" style="font-size: 18px; margin-bottom: 5px;"><?php echo strtoupper($pedido['cliente']); ?></div>
            <div style="font-size: 14px;">
                <?php echo $pedido['direccion']; ?><br>
                <strong><?php echo $pedido['ciudad']; ?>, <?php echo $pedido['provincia']; ?></strong><br>
                Tel: <strong><?php echo $pedido['telefono']; ?></strong>
            </div>
        </div>

        <?php if($pedido['total_venta'] > 0): ?>
            <div class="cod-box">
                COBRAR: RD$ <?php echo number_format($pedido['total_venta'], 0); ?>
            </div>
        <?php else: ?>
            <div class="cod-box" style="background: #eee; color: #333; border: 2px solid #000;">
                YA PAGADO (NO COBRAR)
            </div>
        <?php endif; ?>

        <div class="info-row">
            <div>
                <span class="label-title">TRANSPORTADORA:</span><br>
                <strong><?php echo strtoupper($pedido['transporte'] ?: 'PENDIENTE'); ?></strong>
            </div>
            <div style="text-align: right;">
                <span class="label-title">FECHA:</span><br>
                <?php echo date('d/m/Y', strtotime($pedido['fecha_creacion'])); ?>
            </div>
        </div>

        <div class="items-list">
            <span class="label-title">CONTIENE:</span>
            <table>
                <?php foreach($items as $i): ?>
                <tr>
                    <td><?php echo $i['cantidad']; ?> x <?php echo substr($i['nombre'], 0, 25); ?>...</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="footer">
            <div class="label-title">FIRMA DE RECIBIDO</div>
            <div style="border-bottom: 1px solid #000; margin-top: 20px; width: 80%; margin-left: auto; margin-right: auto;"></div>
            
            <div class="barcode"></div>
            <small style="font-size: 9px;"><?php echo $pedido['numero_orden']; ?> - Generado por Sistema SRGUERRA</small>
        </div>

    </div>

</body>
</html>