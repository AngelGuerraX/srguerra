<?php
// modules/transportadoras/logic.php
// MOTOR LÓGICO DE TRANSPORTADORAS

// 1. Verificar Sesión
verificar_sesion();
$empresa_id = $_SESSION['empresa_id'];

// =============================================================================
// PROCESAMIENTO DE FORMULARIOS (POST)
// =============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Si tienes la función validar_csrf() activada en tu sistema, descomenta esto:
    // validar_csrf(); 

    $action = $_POST['action'];

    // ---------------------------------------------------------
    // A) CREAR NUEVA TRANSPORTADORA
    // ---------------------------------------------------------
    // modules/transportadoras/logic.php

    if ($_POST['action'] == 'crear') {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono']; // Si ya agregaste esta columna
        $costo = $_POST['costo_envio_fijo'];
        
        // VALIDACIÓN DE SEGURIDAD PARA CAMPO PÚBLICO
        $es_publica = 0;
        if (isset($_POST['es_publica']) && $_SESSION['rol'] == 'SuperAdmin') {
            $es_publica = 1;
        }

        $empresa_id = $_SESSION['empresa_id'];

        $sql = "INSERT INTO transportadoras (empresa_id, nombre, telefono, costo_envio_fijo, es_publica, activo) 
                VALUES (?, ?, ?, ?, ?, 1)";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$empresa_id, $nombre, $telefono, $costo, $es_publica]);

        header("Location: index.php?ruta=transportadoras");
    }
 
  // ---------------------------------------------------------
    // B) EDITAR TRANSPORTADORA (CON CREDENCIALES)
    // ---------------------------------------------------------
    if ($action == 'editar_transportadora') {
        $id     = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $costo  = $_POST['costo_envio_fijo'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        // Nuevos campos
        $codigo = strtoupper(trim($_POST['codigo_acceso'])); // Forzamos mayúsculas
        $pin    = trim($_POST['pin_acceso']);

        try {
            // Preparamos la consulta base
            $sql = "UPDATE transportadoras SET nombre=?, costo_envio_fijo=?, codigo_acceso=?, activo=?";
            $params = [$nombre, $costo, $codigo, $activo];

            // Si escribió un PIN nuevo, lo encriptamos y agregamos a la consulta
            if (!empty($pin)) {
                $hash = password_hash($pin, PASSWORD_BCRYPT);
                $sql .= ", pin_acceso=?";
                $params[] = $hash;
            }

            // Cerramos la consulta
            $sql .= " WHERE id=? AND empresa_id=?";
            $params[] = $id;
            $params[] = $empresa_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header("Location: index.php?ruta=transportadoras&msg=actualizado");
            exit();

        } catch (Exception $e) {
            die("Error al actualizar: " . $e->getMessage());
        }
    }
}

// =============================================================================
// PROCESAMIENTO DE ACCIONES URL (GET)
// =============================================================================
if (isset($_GET['action'])) {
    
    // ---------------------------------------------------------
    // C) ELIMINAR / DESACTIVAR
    // ---------------------------------------------------------
    if ($_GET['action'] == 'eliminar' && isset($_GET['id'])) {
        $id = $_GET['id'];

        try {
            // Opción 1: Borrado Físico (Si no tiene pedidos)
            // $stmt = $pdo->prepare("DELETE FROM transportadoras WHERE id = ? AND empresa_id = ?");
            
            // Opción 2: Borrado Lógico (Recomendado para no romper historial de pedidos)
            // Simplemente la desactivamos para que no salga en nuevos pedidos
            $stmt = $pdo->prepare("UPDATE transportadoras SET activo = 0 WHERE id = ? AND empresa_id = ?");
            
            $stmt->execute([$id, $empresa_id]);

            header("Location: index.php?ruta=transportadoras&msg=eliminado");
            exit();

        } catch (Exception $e) {
            die("Error al eliminar: " . $e->getMessage());
        }
    }
}

// Si llega aquí sin entrar a ningún if, regresa al índice
header("Location: index.php?ruta=transportadoras");
exit();
?>