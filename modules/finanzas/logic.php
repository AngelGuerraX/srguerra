<?php
// modules/finanzas/logic.php

if (!isset($_SESSION['empresa_id'])) header("Location: index.php");
$empresa_id = $_SESSION['empresa_id'];
$action = $_REQUEST['action'] ?? '';

// GUARDAR GASTO OPERATIVO
if ($action == 'guardar_gasto' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $desc = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $cat = $_POST['categoria'];
    
    $stmt = $pdo->prepare("INSERT INTO gastos (empresa_id, descripcion, monto, fecha, categoria) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$empresa_id, $desc, $monto, $fecha, $cat]);
    
    echo "<script>window.location='index.php?ruta=finanzas/gastos';</script>";
    exit();
}

// BORRAR GASTO OPERATIVO
if ($action == 'borrar_gasto') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$id, $empresa_id]);
    
    echo "<script>window.location='index.php?ruta=finanzas/gastos';</script>";
    exit();
}
?>