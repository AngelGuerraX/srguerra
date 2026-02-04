<?php
// modules/shopify/webhook_shopify.php
// RECEPTOR DE PEDIDOS SHOPIFY (SaaS Ready - V2.0)

// 1. CONFIGURACIÓN DE RUTAS
require_once '../../includes/db.php';

// Activar log
ini_set('log_errors', 1);
ini_set('error_log', 'webhook_shopify_errors.log');

// Capturar Headers de Shopify
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
$shop_domain = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? ''; // Ej: tienda.myshopify.com

// 2. CAPTURAR UID DESDE URL (?uid=1)
$empresa_id = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

if ($empresa_id <= 0) {
    http_response_code(400);
    die("Error: URL incompleta.");
}

try {
    // 3. BUSCAR CREDENCIALES
    // Agregamos 'shopify_url' para validar que el pedido viene de la tienda correcta
    $stmt = $pdo->prepare("SELECT shopify_secret, shopify_url FROM empresas WHERE id = ? LIMIT 1");
    $stmt->execute([$empresa_id]);
    $empresa = $stmt->fetch();

    if (!$empresa || empty($empresa['shopify_secret'])) {
        error_log("Error Config: Empresa $empresa_id sin secreto.");
        http_response_code(403);    
        die();
    }

    // Validación extra de dominio (Opcional pero recomendada)
    // if ($shop_domain && $empresa['shopify_url'] && $shop_domain !== $empresa['shopify_url']) {
    //     error_log("Error Seguridad: Dominio $shop_domain no coincide con registrado.");
    //     http_response_code(401); die();
    // }

    $SHARED_SECRET = $empresa['shopify_secret'];

    // 4. VALIDAR FIRMA HMAC
    $data = file_get_contents('php://input');
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $SHARED_SECRET, true));

    if (!hash_equals($hmac_header, $calculated_hmac)) {
        error_log("HMAC Inválido para empresa $empresa_id");
        http_response_code(401);
        die();
    }

    // ---------------------------------------------------------
    // CONEXIÓN SEGURA VERIFICADA
    // ---------------------------------------------------------

    $order = json_decode($data, true);
    
    // Si es un ping de prueba de Shopify (sin ID)
    if (!isset($order['id'])) {
        http_response_code(200);
        exit();
    }

    $shopify_order_id = $order['id'];

    // 5. EVITAR DUPLICADOS (IDEMPOTENCIA)
    $stmt_check = $pdo->prepare("SELECT id FROM pedidos WHERE shopify_order_id = ? AND empresa_id = ?");
    $stmt_check->execute([$shopify_order_id, $empresa_id]);
    if ($stmt_check->fetch()) {
        http_response_code(200); // Ya existe, respondemos OK
        exit();
    }

    $pdo->beginTransaction();

    // --- A. GESTIÓN DE CLIENTE ---
    $email = $order['email'] ?? '';
    // Lógica robusta para encontrar teléfono
    $phone = $order['shipping_address']['phone'] 
             ?? $order['billing_address']['phone'] 
             ?? $order['customer']['phone'] 
             ?? $order['phone'] 
             ?? '';
    
    // Normalizar datos de dirección
    $shipping = $order['shipping_address'] ?? [];
    $nombre = trim(($shipping['first_name'] ?? 'Cliente') . ' ' . ($shipping['last_name'] ?? 'Shopify'));
    $address1 = $shipping['address1'] ?? '';
    $address2 = $shipping['address2'] ?? '';
    $full_address = trim($address1 . ' ' . $address2);
    $city = $shipping['city'] ?? '';
    $province = $shipping['province'] ?? '';
    $shopify_customer_id = $order['customer']['id'] ?? NULL;

    // Buscar cliente (Prioridad: ID Shopify > Email > Teléfono)
    $sql_find = "SELECT id FROM clientes WHERE empresa_id = ? AND (
                    (shopify_customer_id = ? AND shopify_customer_id IS NOT NULL) OR 
                    (email = ? AND email != '') OR 
                    (telefono = ? AND telefono != '')
                 ) LIMIT 1";
    
    $stmt_cli = $pdo->prepare($sql_find);
    $stmt_cli->execute([$empresa_id, $shopify_customer_id, $email, $phone]);
    $cliente = $stmt_cli->fetch();

    if ($cliente) {
        $cliente_id = $cliente['id'];
        // Opcional: Actualizar dirección si viene diferente
    } else {
        $sql_new = "INSERT INTO clientes (empresa_id, nombre, telefono, email, provincia, ciudad, direccion, shopify_customer_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_new = $pdo->prepare($sql_new);
        $stmt_new->execute([$empresa_id, $nombre, $phone, $email, $province, $city, $full_address, $shopify_customer_id]);
        $cliente_id = $pdo->lastInsertId();
    }

    // --- B. CREAR PEDIDO ---
    $numero_orden = $order['name']; // #1001
    $total_venta = $order['total_price'];
    $nota = $order['note'] ?? '';

    // Mapeo de estado financiero (Opcional)
    // $financial_status = $order['financial_status']; // paid, pending...

    $sql_ped = "INSERT INTO pedidos 
                (empresa_id, cliente_id, shopify_order_id, numero_orden, estado_interno, total_venta, origen, notas_internas, fecha_creacion, direccion, ciudad, nombre_cliente, telefono) 
                VALUES (?, ?, ?, ?, 'Nuevo', ?, 'Shopify', ?, NOW(), ?, ?, ?, ?)";
    
    $stmt_p = $pdo->prepare($sql_ped);
    $stmt_p->execute([
        $empresa_id, $cliente_id, $shopify_order_id, $numero_orden, $total_venta, $nota, $full_address, $city, $nombre, $phone
    ]);
    $pedido_id = $pdo->lastInsertId();

    // --- C. GUARDAR DETALLES (PRODUCTOS) ---
    foreach ($order['line_items'] as $item) {
        $sku = $item['sku'] ?? ''; 
        $qty = $item['quantity'];
        $price = $item['price'];
        $name = $item['name']; 
        $variant_id = $item['variant_id'] ?? '';

        $producto_id = NULL;

        // Estrategia de búsqueda de producto:
        // 1. Por SKU (Exacto)
        // 2. Por Nombre (Aproximado, si no hay SKU)
        
        if (!empty($sku)) {
            $stmt_sku = $pdo->prepare("SELECT id FROM productos WHERE sku = ? AND empresa_id = ? LIMIT 1");
            $stmt_sku->execute([$sku, $empresa_id]);
            $prod = $stmt_sku->fetch();
            if ($prod) $producto_id = $prod['id'];
        } 
        
        if (!$producto_id) {
            // Intento secundario por nombre (Cuidado con falsos positivos)
            $stmt_name = $pdo->prepare("SELECT id FROM productos WHERE nombre LIKE ? AND empresa_id = ? LIMIT 1");
            $stmt_name->execute([$name, $empresa_id]);
            $prod = $stmt_name->fetch();
            if ($prod) $producto_id = $prod['id'];
        }

        // Insertar detalle
        $sql_det = "INSERT INTO pedidos_detalle (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)";
        $stmt_det = $pdo->prepare($sql_det);
        $stmt_det->execute([$pedido_id, $producto_id, $name, $qty, $price]);

        // Descontar inventario (Solo si encontramos el producto local)
        if ($producto_id) {
            $stmt_upd = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
            $stmt_upd->execute([$qty, $producto_id]);
        }
    }

    $pdo->commit();
    http_response_code(200);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Loguear el error y el payload para debug
    error_log("ERROR SHOPIFY: " . $e->getMessage());
    file_put_contents('shopify_error_' . time() . '.json', $data); // Guardar JSON fallido para revisión
    http_response_code(500);
}
?>