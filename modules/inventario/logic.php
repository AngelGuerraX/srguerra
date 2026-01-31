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
