<?php
// modules/pedidos/imprimir_hoja_ruta.php
// MANIFIESTO DE ENTREGA (Print Friendly)

if (empty($_GET['ids']) || empty($_GET['trans'])) die("Datos insuficientes.");

$ids_array = explode(',', $_GET['ids']);
$id_trans  = $_GET['trans'];

// 1. Datos Transportadora
$stmt_t = $pdo->prepare("SELECT * FROM transportadoras WHERE id = ?");
$stmt_t->execute([$id_trans]);
$trans = $stmt_t->fetch();

// 2. Datos Pedidos
// Truco para usar "IN (...)" con array seguro
$in  = str_repeat('?,', count($ids_array) - 1) . '?';
$sql = "SELECT p.*, c.nombre as cliente, c.ciudad, c.direccion, c.telefono 
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        WHERE p.id IN ($in)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids_array);
$lista = $stmt->fetchAll();

$total_dinero = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Ruta - <?php echo date('d-m-Y'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .info-box { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .total-row { font-weight: bold; background-color: #eee; }
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
        .sign-box { width: 40%; border-top: 1px solid #000; padding-top: 10px; text-align: center; }
        
        /* Solo imprimir */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <button class="no-print" onclick="window.print()" style="padding: 10px 20px; font-size: 16px; margin-bottom: 20px; cursor: pointer;">🖨️ IMPRIMIR AHORA</button>
    <button class="no-print" onclick="window.close()" style="padding: 10px 20px; font-size: 16px; margin-bottom: 20px; cursor: pointer;">CERRAR</button>

    <div class="header">
        <h2>HOJA DE RUTA / MANIFIESTO</h2>
        <h3><?php echo strtoupper($trans['nombre']); ?></h3>
        <p>Fecha: <?php echo date('d/m/Y H:i'); ?> | Despachado por: El Clavito Logistics</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Orden</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 35%;">Dirección / Ciudad</th>
                <th style="width: 15%; text-align: right;">A Cobrar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($lista as $i => $p): 
                $total_dinero += $p['total_venta'];
            ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><b><?php echo $p['numero_orden']; ?></b></td>
                <td>
                    <?php echo $p['cliente']; ?><br>
                    Tel: <?php echo $p['telefono']; ?>
                </td>
                <td>
                    <?php echo $p['ciudad']; ?><br>
                    <small><?php echo $p['direccion']; ?></small>
                </td>
                <td style="text-align: right;">
                    RD$ <?php echo number_format($p['total_venta'], 0); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL A RECAUDAR EN RUTA:</td>
                <td style="text-align: right;">RD$ <?php echo number_format($total_dinero, 0); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <div class="sign-box">
            Firma Despachador (Entregué)
        </div>
        <div class="sign-box">
            Firma Conductor (Recibí <?php echo count($lista); ?> Paquetes)
        </div>
    </div>

</body>
</html>