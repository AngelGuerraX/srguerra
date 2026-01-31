<?php
// webhook_shopify.php
// RECEPTOR DE PEDIDOS SAAS (MULTI-EMPRESA)

// Incluir conexión (Asegúrate que db.php no tenga session_start() obligatorio o fallará el webhook)
require_once 'config/db.php';

// 1. CAPTURAR EL ID DE LA EMPRESA DESDE LA URL
$empresa_id = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

if ($empresa_id <= 0) {
    http_response_code(400);
    die("Error: URL incompleta. Falta el UID.");
}

// 2. BUSCAR EL SECRETO DE ESA EMPRESA ESPECÍFICA
$stmt = $pdo->prepare("SELECT shopify_secret FROM empresas WHERE id = ? AND estado = 'Activo' LIMIT 1");
$stmt->execute([$empresa_id]);
$empresa = $stmt->fetch();

if (!$empresa || empty($empresa['shopify_secret'])) {
    http_response_code(403);
    die("Error: Empresa no encontrada o sin integración configurada.");
}

$SHARED_SECRET = $empresa['shopify_secret'];

// 3. VALIDAR LA FIRMA DE SHOPIFY (SEGURIDAD)
$data = file_get_contents('php://input');
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
$calculated_hmac = base64_encode(hash_hmac('sha256', $data, $SHARED_SECRET, true));

if (!hash_equals($hmac_header, $calculated_hmac)) {
    http_response_code(401);
    die("Error: Firma inválida. Verifica tu clave secreta.");
}

// 4. PROCESAR EL JSON
$order = json_decode($data, true);
$shopify_order_id = $order['id'];

// Evitar duplicados (Verificar si ya existe el pedido para ESTA empresa)
$stmt_check = $pdo->prepare("SELECT id FROM pedidos WHERE shopify_order_id = ? AND empresa_id = ?");
$stmt_check->execute([$shopify_order_id, $empresa_id]);
if ($stmt_check->fetch()) {
    http_response_code(200); // Ya lo tenemos, todo ok.
    exit();
}

try {
    $pdo->beginTransaction();

    // --- A. GESTIÓN DEL CLIENTE ---
    // Datos del JSON de Shopify
    $email = $order['email'] ?? '';
    $phone = $order['shipping_address']['phone'] ?? $order['phone'] ?? '';
    $nombre = ($order['shipping_address']['first_name'] ?? '') . ' ' . ($order['shipping_address']['last_name'] ?? '');
    $address = ($order['shipping_address']['address1'] ?? '') . ' ' . ($order['shipping_address']['address2'] ?? '');
    $city = $order['shipping_address']['city'] ?? '';
    $province = $order['shipping_address']['province'] ?? '';
    $shopify_customer_id = $order['customer']['id'] ?? NULL;

    // Buscar cliente existente EN ESTA EMPRESA
    $stmt_cli = $pdo->prepare("SELECT id FROM clientes WHERE (shopify_customer_id = ? OR email = ? OR telefono = ?) AND empresa_id = ? LIMIT 1");
    $stmt_cli->execute([$shopify_customer_id, $email, $phone, $empresa_id]);
    $cliente_existente = $stmt_cli->fetch();

    if ($cliente_existente) {
        $cliente_id = $cliente_existente['id'];
        // Opcional: Actualizar dirección si viene nueva
    } else {
        // Crear nuevo cliente
        $sql_new_cli = "INSERT INTO clientes (empresa_id, nombre, telefono, email, provincia, ciudad, direccion, shopify_customer_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_new = $pdo->prepare($sql_new_cli);
        $stmt_new->execute([$empresa_id, $nombre, $phone, $email, $province, $city, $address, $shopify_customer_id]);
        $cliente_id = $pdo->lastInsertId();
    }

    // --- B. CREAR PEDIDO ---
    $numero_orden = $order['name']; // Ej: #1001
    $total_venta = $order['total_price'];
    $nota = $order['note'] ?? '';

    // Insertar en tu tabla PEDIDOS (Mapeo exacto a tu BD)
    $sql_ped = "INSERT INTO pedidos 
                (empresa_id, cliente_id, shopify_order_id, numero_orden, estado_interno, total_venta, origen, notas_internas, fecha_creacion) 
                VALUES (?, ?, ?, ?, 'Nuevo', ?, 'Shopify', ?, NOW())";
    
    $stmt_p = $pdo->prepare($sql_ped);
    $stmt_p->execute([$empresa_id, $cliente_id, $shopify_order_id, $numero_orden, $total_venta, $nota]);
    $pedido_id = $pdo->lastInsertId();

    // --- C. GUARDAR DETALLES Y DESCONTAR STOCK ---
    foreach ($order['line_items'] as $item) {
        $sku = $item['sku']; // El SKU es la clave para conectar con tu inventario
        $qty = $item['quantity'];
        $price = $item['price'];
        $name = $item['title'];

        // Buscar producto local por SKU y EMPRESA
        $producto_id = NULL;
        if (!empty($sku)) {
            $stmt_prod = $pdo->prepare("SELECT id FROM productos WHERE sku = ? AND empresa_id = ? LIMIT 1");
            $stmt_prod->execute([$sku, $empresa_id]);
            $prod_local = $stmt_prod->fetch();
            if ($prod_local) {
                $producto_id = $prod_local['id'];
            }
        }

        // Insertar detalle
        $sql_det = "INSERT INTO pedidos_detalle (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt_det = $pdo->prepare($sql_det);
        $stmt_det->execute([$pedido_id, $producto_id, $name, $qty, $price]);

        // Descontar inventario GLOBAL si el producto existe
        if ($producto_id) {
            $stmt_upd = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
            $stmt_upd->execute([$qty, $producto_id]);
            
            // OJO: Si quisieras descontar de un almacén específico, 
            // tendrías que definir lógica de cuál almacén usar. 
            // Por ahora descontamos del global.
        }
    }

    $pdo->commit();
    http_response_code(200); // OK

} catch (Exception $e) {
    $pdo->rollBack();
    // Guardar error en log para depurar
    file_put_contents('webhook_error.log', date('Y-m-d H:i:s') . " - Error Empresa $empresa_id: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
}
?>