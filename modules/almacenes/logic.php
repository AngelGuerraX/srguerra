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
?>