<?php
// modules/almacenes/logic.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    validar_csrf();

    $action = $_POST['action'];

    if ($action == 'crear_almacen') {
        $nombre = trim($_POST['nombre']);
        $ubicacion = trim($_POST['ubicacion']);
        $costo = $_POST['costo_empaque'];

        $stmt = $pdo->prepare("INSERT INTO almacenes (empresa_id, nombre, ubicacion, costo_empaque, activo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$_SESSION['empresa_id'], $nombre, $ubicacion, $costo]);

        header("Location: index.php?ruta=almacenes&msg=creado");
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'actualizar_almacen') {
    
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'] ?? ''; // Usa '' si está vacío
    $costo = $_POST['costo_empaque'] ?? 0;
    $empresa_id = $_SESSION['empresa_id'];

    if (empty($nombre)) die("El nombre es obligatorio");

    try {
        $sql = "UPDATE almacenes 
                SET nombre = ?, ubicacion = ?, costo_empaque = ? 
                WHERE id = ? AND empresa_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $ubicacion, $costo, $id, $empresa_id]);

        header("Location: index.php?ruta=almacenes&msg=actualizado");
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
}
?>