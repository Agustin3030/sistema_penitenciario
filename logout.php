<?php
session_start();

// Registrar logout en historial
if (isset($_SESSION['id_usuario'])) {
    require 'conexion.php';
    $conexion = conectarBaseDeDatos();
    $conexion->query("INSERT INTO historial_login (usuario_id, accion) VALUES ({$_SESSION['id_usuario']}, 'LOGOUT')");
}

// Destruir sesión completamente
$_SESSION = array();
session_destroy();

// Redirigir a login
header("Location: login.php");
exit();
?>