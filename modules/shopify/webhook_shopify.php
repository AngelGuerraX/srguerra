<?php
// modules/shopify/webhook_shopify.php
// RECEPTOR DE PEDIDOS SHOPIFY (SaaS Ready)

// 1. CONFIGURACIÓN DE RUTAS
// Subimos 2 niveles para llegar a includes/db.php desde modules/shopify/
require_once '../../includes/db.php';

// Activar log para depuración (Crea un archivo webhook_log.txt)
ini_set('log_errors', 1);
ini_set('error_log', 'webhook_log.txt');

// 2. CAPTURAR EL ID DE LA EMPRESA DESDE LA URL (?uid=1)
$empresa_id = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

if ($empresa_id <= 0) {
    http_response_code(400);
    die("Error: URL incompleta. Debes usar ?uid=ID_EMPRESA");
}

try {
    // 3. BUSCAR EL SECRETO DE ESA EMPRESA
    $stmt = $pdo->prepare("SELECT shopify_secret FROM empresas WHERE id = ? LIMIT 1");
    $stmt->execute([$empresa_id]);
    $empresa = $stmt->fetch();

    // Si no hay secreto configurado, rechazamos por seguridad
    if (!$empresa || empty($empresa['shopify_secret'])) {
        error_log("Error: Empresa ID $empresa_id no tiene shopify_secret configurado en la BD.");
        http_response_code(403);    
        die("Error de configuración interna.");
    }

    $SHARED_SECRET = $empresa['shopify_secret'];

    // 4. VALIDAR LA FIRMA DE SHOPIFY (SEGURIDAD HMAC)
    $data = file_get_contents('php://input');
    $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $SHARED_SECRET, true));

    if (!hash_equals($hmac_header, $calculated_hmac)) {
        error_log("Error de seguridad: HMAC inválido para empresa $empresa_id");
        http_response_code(401);
        die("Acceso denegado: Firma inválida.");
    }

    // ---------------------------------------------------------
    // SI LLEGAMOS AQUÍ, LA CONEXIÓN ES SEGURA Y REAL
    // ---------------------------------------------------------

    $order = json_decode($data, true);
    
    // Verificar si es una orden válida
    if (!isset($order['id'])) {
        http_response_code(200); // Respondemos OK para que Shopify no reintente pings de prueba
        exit();
    }

    $shopify_order_id = $order['id'];

    // 5. EVITAR DUPLICADOS
    $stmt_check = $pdo->prepare("SELECT id FROM pedidos WHERE shopify_order_id = ? AND empresa_id = ?");
    $stmt_check->execute([$shopify_order_id, $empresa_id]);
    if ($stmt_check->fetch()) {
        error_log("Orden duplicada omitida: #$shopify_order_id");
        http_response_code(200);
        exit();
    }

    $pdo->beginTransaction();

    // --- A. GESTIÓN DEL CLIENTE ---
    $email = $order['email'] ?? '';
    // Intentar obtener teléfono de envío, si no, del cliente
    $phone = $order['shipping_address']['phone'] ?? $order['customer']['phone'] ?? $order['phone'] ?? '';
    
    $nombre = ($order['shipping_address']['first_name'] ?? 'Cliente') . ' ' . ($order['shipping_address']['last_name'] ?? 'Shopify');
    $address1 = $order['shipping_address']['address1'] ?? '';
    $address2 = $order['shipping_address']['address2'] ?? '';
    $full_address = trim($address1 . ' ' . $address2);
    
    $city = $order['shipping_address']['city'] ?? '';
    $province = $order['shipping_address']['province'] ?? '';
    $shopify_customer_id = $order['customer']['id'] ?? NULL;

    // Buscar cliente existente (Por ID de Shopify, o Email, o Teléfono)
    // Usamos lógica estricta para no mezclar clientes vacíos
    $sql_find_cli = "SELECT id FROM clientes WHERE empresa_id = ? AND (
                        (shopify_customer_id = ? AND shopify_customer_id IS NOT NULL) OR 
                        (email = ? AND email != '') OR 
                        (telefono = ? AND telefono != '')
                     ) LIMIT 1";
    
    $stmt_cli = $pdo->prepare($sql_find_cli);
    $stmt_cli->execute([$empresa_id, $shopify_customer_id, $email, $phone]);
    $cliente_existente = $stmt_cli->fetch();

    if ($cliente_existente) {
        $cliente_id = $cliente_existente['id'];
    } else {
        // Crear nuevo cliente
        $sql_new_cli = "INSERT INTO clientes (empresa_id, nombre, telefono, email, provincia, ciudad, direccion, shopify_customer_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_new = $pdo->prepare($sql_new_cli);
        $stmt_new->execute([$empresa_id, $nombre, $phone, $email, $province, $city, $full_address, $shopify_customer_id]);
        $cliente_id = $pdo->lastInsertId();
    }

    // --- B. CREAR PEDIDO ---
    $numero_orden = $order['name']; // Ej: #1001
    $total_venta = $order['total_price'];
    $nota = $order['note'] ?? '';

    // Insertar Pedido
    $sql_ped = "INSERT INTO pedidos 
                (empresa_id, cliente_id, shopify_order_id, numero_orden, estado_interno, total_venta, origen, notas_internas, fecha_creacion, direccion, ciudad, nombre_cliente, telefono) 
                VALUES (?, ?, ?, ?, 'Nuevo', ?, 'Shopify', ?, NOW(), ?, ?, ?, ?)";
    
    $stmt_p = $pdo->prepare($sql_ped);
    $stmt_p->execute([
        $empresa_id, $cliente_id, $shopify_order_id, $numero_orden, $total_venta, $nota, $full_address, $city, $nombre, $phone
    ]);
    $pedido_id = $pdo->lastInsertId();

    // --- C. GUARDAR DETALLES Y DESCONTAR STOCK ---
    foreach ($order['line_items'] as $item) {
        $sku = $item['sku'] ?? ''; 
        $qty = $item['quantity'];
        $price = $item['price'];
        $name = $item['name']; // Nombre completo con variante

        $producto_id = NULL;

        // Buscar producto local por SKU si existe
        if (!empty($sku)) {
            $stmt_prod = $pdo->prepare("SELECT id FROM productos WHERE sku = ? AND empresa_id = ? LIMIT 1");
            $stmt_prod->execute([$sku, $empresa_id]);
            $prod_local = $stmt_prod->fetch();
            if ($prod_local) {
                $producto_id = $prod_local['id'];
            }
        }

        // Insertar detalle del pedido
        $sql_det = "INSERT INTO pedidos_detalle (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt_det = $pdo->prepare($sql_det);
        $stmt_det->execute([$pedido_id, $producto_id, $name, $qty, $price]);

        // Descontar inventario GLOBAL si el producto fue encontrado
        if ($producto_id) {
            $stmt_upd = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
            $stmt_upd->execute([$qty, $producto_id]);
        }
    }

    $pdo->commit();
    error_log("Pedido Shopify $numero_orden procesado con éxito.");
    http_response_code(200);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("ERROR CRÍTICO SHOPIFY: " . $e->getMessage());
    http_response_code(500);
}
?>