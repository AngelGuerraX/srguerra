anja
<?php
// reset_pass.php
// Ejecuta este archivo una vez en el navegador y luego bórralo.
require 'config/db.php';

$password = '123456';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Actualizamos al usuario ID 1 (Admin)
$sql = "UPDATE usuarios SET password_hash = ? WHERE id = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hash]);

echo "<h1>¡Contraseña restablecida!</h1>";
echo "<p>Usuario: <b>admin@srguerra.com</b> (o el email que tenga el ID 1)</p>";
echo "<p>Contraseña: <b>123456</b></p>";
echo "<a href='index.php'>Ir al Login</a>";
?>