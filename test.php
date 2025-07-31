<?php
require 'conexion.php';
$con = conectarBaseDeDatos();

// Verificar admin
$res = $con->query("SELECT usuario, password FROM usuarios WHERE usuario = 'admin'");
$admin = $res->fetch_assoc();

echo "<h2>Datos del admin:</h2>";
echo "<pre>";
print_r($admin);
echo "</pre>";

// Verificar si la contraseña coincide
echo "<h2>Verificación de contraseña:</h2>";
echo "Contraseña 'admin123' válida: ";
echo password_verify('admin123', $admin['password']) ? "SÍ" : "NO";

$con->close();
?>