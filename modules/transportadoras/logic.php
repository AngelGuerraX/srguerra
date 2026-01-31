<?php
// modules/transportadoras/logic.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    validar_csrf(); // Seguridad activada

    $action = $_POST['action'];

    if ($action == 'crear_transportadora') {
        $nombre = trim($_POST['nombre']);
        $costo = $_POST['costo_envio_fijo'];

        // Guardar en BD
        $stmt = $pdo->prepare("INSERT INTO transportadoras (empresa_id, nombre, costo_envio_fijo, activo) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_SESSION['empresa_id'], $nombre, $costo]);

        header("Location: index.php?ruta=transportadoras&msg=creado");
        exit();
    }
}
?>