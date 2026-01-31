<?php
// modules/clientes/logic.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    validar_csrf();

    $action = $_POST['action'];

    if ($action == 'editar_cliente') {
        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $telefono = trim($_POST['telefono']);
        $provincia = $_POST['provincia'];
        $ciudad = $_POST['ciudad'];
        $direccion = $_POST['direccion'];

        $sql = "UPDATE clientes SET nombre=?, telefono=?, provincia=?, ciudad=?, direccion=? WHERE id=? AND empresa_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $telefono, $provincia, $ciudad, $direccion, $id, $_SESSION['empresa_id']]);

        header("Location: index.php?ruta=clientes/ver&id=" . $id . "&msg=actualizado");
        exit();
    }
}
?>