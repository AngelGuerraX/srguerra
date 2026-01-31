<?php
/* CONEXIÓN A BASE DE DATOS - EL CLAVITO ERP */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Tu usuario (por defecto en XAMPP es root)
define('DB_PASS', '');          // Tu contraseña (por defecto vacío)
define('DB_NAME', 'srguerrabdd'); // Asegúrate de haber creado esta base de datos en PHPMyAdmin

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Ajuste horario para RD
    $pdo->exec("SET time_zone = '-04:00'");
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
