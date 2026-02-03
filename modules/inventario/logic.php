<?php
// modules/inventario/logic.php
// NOTA: Este archivo se carga desde index.php, así que ya tiene DB y Sesión.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    validar_csrf();
    $action = $_POST['action'];

    // ---------------------------------------------------------
    // CASO A: GUARDAR NUEVO PRODUCTO
    // ---------------------------------------------------------
    if ($action == 'guardar_producto') {
        try {
            $pdo->beginTransaction();

            // 1. PROCESAR IMAGEN
            $ruta_imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombre_archivo = uniqid() . "." . $ext;
                $destino = "uploads/productos/" . $nombre_archivo;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                    $ruta_imagen = $nombre_archivo;
                }
            }

            // 2. INSERTAR PRODUCTO BASE
            $sql = "INSERT INTO productos (empresa_id, sku, nombre, descripcion, imagen, precio_venta, costo_compra, stock_actual) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            // Calculamos stock total sumando los inputs de los almacenes
            $stock_total = 0;
            if (isset($_POST['stock_almacen'])) {
                foreach ($_POST['stock_almacen'] as $cant) {
                    $stock_total += intval($cant);
                }
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_SESSION['empresa_id'],
                $_POST['sku'],
                $_POST['nombre'],
                $_POST['descripcion'],
                $ruta_imagen,
                $_POST['precio_venta'],
                $_POST['costo_compra'],
                $stock_total // Guardamos el total para búsquedas rápidas
            ]);

            $producto_id = $pdo->lastInsertId();

            // 3. GUARDAR STOCK POR ALMACÉN
            if (isset($_POST['stock_almacen'])) {
                foreach ($_POST['stock_almacen'] as $almacen_id => $cantidad) {
                    if (intval($cantidad) > 0) {
                        $sql_inv = "INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad) VALUES (?, ?, ?)";
                        $stmt_inv = $pdo->prepare($sql_inv);
                        $stmt_inv->execute([$producto_id, $almacen_id, $cantidad]);
                    }
                }
            }

            $pdo->commit();
            header("Location: index.php?ruta=inventario&msg=guardado");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error al guardar producto: " . $e->getMessage());
        }
    }
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'actualizar_stock_almacenes') {
    
    // Validar Token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguridad: Token inválido");
    }

    $producto_id = (int)$_POST['producto_id'];
    $cantidades  = $_POST['cantidades']; // Array [almacen_id => cantidad]
    $empresa_id  = $_SESSION['empresa_id'];

    try {
        $pdo->beginTransaction();

        $nuevo_stock_global = 0;

        foreach ($cantidades as $almacen_id => $cantidad) {
            $cantidad = (int)$cantidad; // Asegurar número
            if ($cantidad < 0) $cantidad = 0; // Evitar negativos

            // 1. Actualizar o Insertar en tabla inventario_almacen
            // Usamos ON DUPLICATE KEY UPDATE para manejar ambos casos
            $sql = "INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE cantidad = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$producto_id, $almacen_id, $cantidad, $cantidad]);

            $nuevo_stock_global += $cantidad;
        }

        // 2. Actualizar el stock global en la tabla PRODUCTOS
        $stmt_prod = $pdo->prepare("UPDATE productos SET stock_actual = ? WHERE id = ? AND empresa_id = ?");
        $stmt_prod->execute([$nuevo_stock_global, $producto_id, $empresa_id]);

        $pdo->commit();

        // Redirigir
        header("Location: index.php?ruta=inventario/editar&id=$producto_id&msg=stock_actualizado");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al actualizar inventario: " . $e->getMessage());
    }
}
// =========================================================
    // CASO NUEVO: REGISTRAR COMPRA
    // =========================================================
    elseif ($action == 'registrar_compra') {
        
        // Validar CSRF (Recomendado)
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Error de seguridad.");
        }

        $producto_id = (int)$_POST['producto_id'];
        $almacen_id  = (int)$_POST['almacen_id'];
        $cantidad    = (int)$_POST['cantidad'];
        $costo       = (float)$_POST['costo_unitario'];
        $proveedor   = $_POST['proveedor'] ?? '';
        $empresa_id  = $_SESSION['empresa_id'];

        if ($cantidad <= 0) die("La cantidad debe ser mayor a 0.");

        try {
            $pdo->beginTransaction();

            // 1. Registrar en Historial de Compras
            $sql_hist = "INSERT INTO compras (empresa_id, producto_id, almacen_id, cantidad, costo_unitario, proveedor, fecha_compra) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $pdo->prepare($sql_hist)->execute([$empresa_id, $producto_id, $almacen_id, $cantidad, $costo, $proveedor]);

            // 2. Aumentar Stock en Almacén Específico
            // (Si existe suma, si no existe crea)
            $sql_inv = "INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE cantidad = cantidad + ?";
            $pdo->prepare($sql_inv)->execute([$producto_id, $almacen_id, $cantidad, $cantidad]);

            // 3. Actualizar Producto (Stock Global + Nuevo Costo)
            // Aquí actualizamos el costo_compra al nuevo precio que introdujiste
            $sql_prod = "UPDATE productos 
                         SET stock_actual = stock_actual + ?, 
                             costo_compra = ? 
                         WHERE id = ? AND empresa_id = ?";
            $pdo->prepare($sql_prod)->execute([$cantidad, $costo, $producto_id, $empresa_id]);

            $pdo->commit();
            
            // Redirigir al inventario
            header("Location: index.php?ruta=inventario&msg=compra_registrada");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error al registrar compra: " . $e->getMessage());
        }
    }