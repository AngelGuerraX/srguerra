<?php
// modules/pedidos/logic.php
// MOTOR LÓGICO DEL SISTEMA (INTEGRADO CON INVENTARIO)

// 1. Verificar Sesión
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php?ruta=login");
    exit();
}

$empresa_id = $_SESSION['empresa_id'] ?? 1;

// Verificar si hay una acción POST o GET (para casos como actualizar_estado que a veces mandan param action)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($action)) {

    // =========================================================
    // CASO 1: ELIMINACIÓN MASIVA
    // =========================================================
    if ($action == 'eliminar_masivo') {
        if (isset($_POST['pedidos']) && is_array($_POST['pedidos'])) {
            $ids = $_POST['pedidos'];
            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ? AND empresa_id = ?");
            foreach ($ids as $id_pedido) {
                $stmt->execute([$id_pedido, $empresa_id]);
            }
            header("Location: index.php?ruta=pedidos&mensaje=eliminados");
            exit();
        } else {
            header("Location: index.php?ruta=pedidos&error=sin_seleccion");
            exit();
        }
    }

    // =========================================================
    // CASO 2: CREAR UN NUEVO PEDIDO
    // =========================================================
    elseif ($action == 'crear_pedido') {
        try {
            $pdo->beginTransaction();

            // 1. GESTIONAR CLIENTE
            $telefono = trim($_POST['cliente_telefono']);
            $nombre = trim($_POST['cliente_nombre']);
            $provincia = $_POST['cliente_provincia'];
            $ciudad = !empty($_POST['cliente_ciudad_final']) ? $_POST['cliente_ciudad_final'] : $_POST['cliente_ciudad'];
            $direccion = $_POST['cliente_direccion'];

            // Buscar o Crear Cliente
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE empresa_id = ? AND telefono = ? LIMIT 1");
            $stmt->execute([$empresa_id, $telefono]);
            $cliente = $stmt->fetch();

            if ($cliente) {
                $cliente_id = $cliente['id'];
                $upd = $pdo->prepare("UPDATE clientes SET nombre=?, provincia=?, ciudad=?, direccion=? WHERE id=?");
                $upd->execute([$nombre, $provincia, $ciudad, $direccion, $cliente_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO clientes (empresa_id, nombre, telefono, provincia, ciudad, direccion) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$empresa_id, $nombre, $telefono, $provincia, $ciudad, $direccion]);
                $cliente_id = $pdo->lastInsertId();
            }

            // 2. CREAR PEDIDO
            $num_orden = "MAN-" . date('dHi') . "-" . rand(10, 99);
            $shopify_fake_id = time() + rand(1, 1000);

            $trans_id = !empty($_POST['transportadora_id']) ? $_POST['transportadora_id'] : NULL;
            $alm_id = !empty($_POST['almacen_id']) ? $_POST['almacen_id'] : NULL;

            $sql_pedido = "INSERT INTO pedidos (
                empresa_id, cliente_id, shopify_order_id, numero_orden, estado_interno, 
                total_venta, transportadora_id, costo_envio_real, almacen_id, 
                costo_empaque_real, notas_internas, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql_pedido);
            $stmt->execute([
                $empresa_id, $cliente_id, $shopify_fake_id, $num_orden, 'Nuevo',
                $_POST['total_venta'], $trans_id, $_POST['costo_envio_real'], $alm_id,
                $_POST['costo_empaque_real'], isset($_POST['notas']) ? $_POST['notas'] : ''
            ]);

            $pedido_id = $pdo->lastInsertId();

            // 3. GUARDAR DETALLE
            $prod_id = !empty($_POST['producto_id']) ? $_POST['producto_id'] : NULL;
            $prod_nombre = !empty($_POST['nombre_producto_texto']) ? $_POST['nombre_producto_texto'] : 'Producto Manual';
            $cantidad = $_POST['cantidad'];
            $precio = $_POST['precio_unitario'];

            $stmt = $pdo->prepare("INSERT INTO pedidos_detalle (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pedido_id, $prod_id, $prod_nombre, $cantidad, $precio]);

            // 4. LÓGICA DE INVENTARIO (DESCONTAR AL CREAR)
            if ($prod_id && $alm_id) {
                // Verificar Stock
                $stmt = $pdo->prepare("SELECT cantidad FROM inventario_almacen WHERE producto_id = ? AND almacen_id = ?");
                $stmt->execute([$prod_id, $alm_id]);
                $stock_almacen = $stmt->fetchColumn();

                // Descontar
                $upd1 = $pdo->prepare("INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad) VALUES (?, ?, -?) ON DUPLICATE KEY UPDATE cantidad = cantidad - ?");
                $upd1->execute([$prod_id, $alm_id, $cantidad, $cantidad]);

                // Actualizar Global
                $upd2 = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
                $upd2->execute([$cantidad, $prod_id]);
            }

            $pdo->commit();
            header("Location: index.php?ruta=pedidos&msg=creado");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("ERROR CRÍTICO: " . $e->getMessage());
        }
    }

    // =========================================================
    // CASO 3: ACTUALIZAR ESTADO (CON DEVOLUCIÓN DE STOCK)
    // =========================================================
    elseif ($action == 'actualizar_estado' || $action == 'cambiar_estado') {
        $pedido_id = $_POST['pedido_id'];
        $nuevo_estado = $_POST['nuevo_estado'];

        try {
            $pdo->beginTransaction();

            // 1. Obtener estado anterior y almacén
            $stmt_check = $pdo->prepare("SELECT estado_interno, almacen_id FROM pedidos WHERE id = ?");
            $stmt_check->execute([$pedido_id]);
            $actual = $stmt_check->fetch();

            // 2. Lógica de DEVOLUCIÓN DE STOCK
            // Si cancelamos o devolvemos, y el pedido YA tenía un almacén asignado, devolvemos stock.
            $estados_devolucion = ['Cancelado', 'Devuelto'];
            
            if (in_array($nuevo_estado, $estados_devolucion) && !in_array($actual['estado_interno'], $estados_devolucion)) {
                if (!empty($actual['almacen_id'])) {
                    $items = $pdo->prepare("SELECT producto_id, cantidad FROM pedidos_detalle WHERE pedido_id = ?");
                    $items->execute([$pedido_id]);
                    
                    foreach ($items->fetchAll() as $item) {
                        if ($item['producto_id'] > 0) {
                            // Devolver al almacén
                            $pdo->prepare("UPDATE inventario_almacen SET cantidad = cantidad + ? WHERE producto_id = ? AND almacen_id = ?")
                                ->execute([$item['cantidad'], $item['producto_id'], $actual['almacen_id']]);

                            // Devolver al global
                            $pdo->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?")
                                ->execute([$item['cantidad'], $item['producto_id']]);
                        }
                    }
                }
            }

            // 3. Actualizar Estado
            $sql = "UPDATE pedidos SET estado_interno = ?, fecha_actualizacion = NOW() WHERE id = ? AND empresa_id = ?";
            $pdo->prepare($sql)->execute([$nuevo_estado, $pedido_id, $empresa_id]);

            // 4. Si es entregado, marcar fecha
            if ($nuevo_estado == 'Entregado') {
                $pdo->prepare("UPDATE pedidos SET fecha_entrega = NOW() WHERE id = ?")->execute([$pedido_id]);
            }

            $pdo->commit();
            header("Location: index.php?ruta=pedidos/ver&id=" . $pedido_id . "&msg=estado_actualizado");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error al cambiar estado: " . $e->getMessage());
        }
    }

    // =========================================================
    // CASO 4: ASIGNAR LOGÍSTICA (CON DESCUENTO DE STOCK)
    // =========================================================
    elseif ($action == 'asignar_logistica') {
        $pedido_id = $_POST['pedido_id'];
        $trans_id = !empty($_POST['transportadora_id']) ? $_POST['transportadora_id'] : NULL;
        $alm_id = !empty($_POST['almacen_id']) ? $_POST['almacen_id'] : NULL;

        try {
            $pdo->beginTransaction();

            // 1. Obtener datos anteriores
            $stmt_old = $pdo->prepare("SELECT almacen_id FROM pedidos WHERE id = ?");
            $stmt_old->execute([$pedido_id]);
            $old_data = $stmt_old->fetch();

            // 2. Lógica de DESCUENTO DE STOCK (Si antes no tenía almacén y ahora sí)
            if (empty($old_data['almacen_id']) && !empty($alm_id)) {
                $stmt_items = $pdo->prepare("SELECT producto_id, cantidad FROM pedidos_detalle WHERE pedido_id = ?");
                $stmt_items->execute([$pedido_id]);
                $items = $stmt_items->fetchAll();

                foreach ($items as $item) {
                    if ($item['producto_id'] > 0) {
                        // Restar del Almacén (Insertar negativo si no existe, o actualizar restando)
                        $sql_inv = "INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad) 
                                    VALUES (?, ?, -?) 
                                    ON DUPLICATE KEY UPDATE cantidad = cantidad - ?";
                        $pdo->prepare($sql_inv)->execute([$item['producto_id'], $alm_id, $item['cantidad'], $item['cantidad']]);

                        // Restar del Global
                        $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?")
                            ->execute([$item['cantidad'], $item['producto_id']]);
                    }
                }
            }

            // 3. Recalcular costos (Para guardar el histórico)
            $costo_envio = 0;
            $costo_empaque = 0;

            if ($trans_id) {
                $stmt = $pdo->prepare("SELECT costo_envio_fijo FROM transportadoras WHERE id = ?");
                $stmt->execute([$trans_id]);
                $costo_envio = $stmt->fetchColumn() ?: 0;
            }

            if ($alm_id) {
                $stmt = $pdo->prepare("SELECT costo_empaque FROM almacenes WHERE id = ?");
                $stmt->execute([$alm_id]);
                $costo_empaque = $stmt->fetchColumn() ?: 0;
            }

            // 4. Actualizar Pedido
            $sql = "UPDATE pedidos SET transportadora_id = ?, almacen_id = ?, costo_envio_real = ?, costo_empaque_real = ?, fecha_actualizacion = NOW() WHERE id = ? AND empresa_id = ?";
            $pdo->prepare($sql)->execute([$trans_id, $alm_id, $costo_envio, $costo_empaque, $pedido_id, $empresa_id]);

            $pdo->commit();
            header("Location: index.php?ruta=pedidos/ver&id=" . $pedido_id . "&msg=logistica_guardada");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error asignando logística: " . $e->getMessage());
        }
    }

    // =========================================================
    // CASO 5: DESPACHO MASIVO (MOVIDO DENTRO DEL FLUJO)
    // =========================================================
    elseif ($action == 'asignar_masivo') {
        
        if (empty($_POST['pedidos']) || empty($_POST['transportadora_id']) || empty($_POST['almacen_origen_id'])) {
            die("Error: Faltan datos (Pedidos, Transportadora o Almacén).");
        }

        $ids_pedidos = $_POST['pedidos'];
        $id_trans    = $_POST['transportadora_id'];
        $id_almacen  = $_POST['almacen_origen_id'];

        try {
            $pdo->beginTransaction();

            foreach ($ids_pedidos as $id_pedido) {
                // Verificar si ya tenía almacén para no descontar doble (opcional, pero seguro)
                $check = $pdo->query("SELECT almacen_id FROM pedidos WHERE id = $id_pedido")->fetch();
                if(!empty($check['almacen_id'])) continue; 

                // Buscar productos
                $stmt_det = $pdo->prepare("SELECT producto_id, cantidad FROM pedidos_detalle WHERE pedido_id = ?");
                $stmt_det->execute([$id_pedido]);
                $items = $stmt_det->fetchAll();

                foreach ($items as $item) {
                    if ($item['producto_id'] > 0) {
                        // Descontar inventario local
                        $sql_upd = "INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad) 
                                    VALUES (?, ?, -?) 
                                    ON DUPLICATE KEY UPDATE cantidad = cantidad - ?";
                        $pdo->prepare($sql_upd)->execute([$item['producto_id'], $id_almacen, $item['cantidad'], $item['cantidad']]);
                        
                        // Descontar inventario global
                        $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?")
                            ->execute([$item['cantidad'], $item['producto_id']]);
                    }
                }
            }

            // Actualizar Pedidos
            $placeholders = implode(',', array_fill(0, count($ids_pedidos), '?'));
            $sql = "UPDATE pedidos 
                    SET transportadora_id = ?, 
                        almacen_id = ?,
                        estado_interno = 'En Ruta', 
                        fecha_actualizacion = NOW() 
                    WHERE id IN ($placeholders) AND empresa_id = ?";
            
            $params = array_merge([$id_trans, $id_almacen], $ids_pedidos, [$empresa_id]);
            $pdo->prepare($sql)->execute($params);

            $pdo->commit();

            // Imprimir
            $ids_str = implode(',', $ids_pedidos);
            header("Location: index.php?ruta=pedidos/imprimir_hoja_ruta&ids=$ids_str&trans=$id_trans");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error en despacho masivo: " . $e->getMessage());
        }
    }

} else {
    // Si entran directo al archivo sin POST
    header("Location: index.php");
    exit();
}
?>