<?php
function conectarBaseDeDatos() {
    $conexion = new mysqli('localhost', 'root', '', 'sistema_penitenciario');
    if ($conexion->connect_error) {
        error_log("Error de conexión: ".$conexion->connect_error);
        die("Error al conectar con la base de datos");
    }
    $conexion->set_charset("utf8mb4");
    return $conexion;
}
?>